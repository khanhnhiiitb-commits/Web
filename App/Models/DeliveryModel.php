<?php
namespace App\Models;

use App\Core\Database;
use PDO;
use Throwable;

//Quản lý thỏa thuận giao nhận, chưa xử lý thanh toán.
class DeliveryModel
{
    private PDO $db;
    public function __construct() { $this->db = Database::getConnection(); }

    public function getForParticipant(int $requestId, int $userId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT ir.id AS request_id, ir.status, ir.delivery_method,
                    ir.requester_id, i.user_id AS giver_id, i.title AS item_title,
                    d.meeting_time, d.meeting_location, d.shipping_carrier,
                    d.shipping_address, d.delivery_note, d.shipping_fee_vnd,
                    d.agreed_at, d.proposed_at
             FROM item_requests ir
             JOIN items i ON i.id = ir.item_id
             LEFT JOIN delivery_details d ON d.request_id = ir.id
             WHERE ir.id = :request_id
               AND (ir.requester_id = :receiver_id OR i.user_id = :giver_id)"
        );
        $stmt->execute([':request_id'=>$requestId, ':receiver_id'=>$userId, ':giver_id'=>$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    //Chỉ người tặng đề xuất, chỉ khi accepted/arranged.
    // Nếu sửa thỏa thuận, reset đồng ý; không cho sửa sau khi đã thanh toán/gửi.
    public function propose(int $requestId, int $giverId, array $data): bool
    {
        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare(
                "SELECT ir.status, ir.requester_id, i.user_id AS giver_id, i.status AS item_status
                 FROM item_requests ir JOIN items i ON i.id=ir.item_id
                 WHERE ir.id=:id FOR UPDATE"
            );
            $stmt->execute([':id'=>$requestId]); $row=$stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row || (int)$row['giver_id']!==$giverId || $row['item_status']!=='reserved'
                || !in_array($row['status'], ['accepted','arranged'],true)) {
                $this->db->rollBack(); return false;
            }
            $sql = "INSERT INTO delivery_details
                        (request_id, meeting_time, meeting_location, shipping_carrier,
                         shipping_address, delivery_note, shipping_fee_vnd, proposed_at, agreed_at)
                    VALUES (:id, :meeting_time, :meeting_location, :carrier,
                            :address, :note, :fee, NOW(), NULL)
                    ON DUPLICATE KEY UPDATE
                        meeting_time=VALUES(meeting_time), meeting_location=VALUES(meeting_location),
                        shipping_carrier=VALUES(shipping_carrier), shipping_address=VALUES(shipping_address),
                        delivery_note=VALUES(delivery_note), shipping_fee_vnd=VALUES(shipping_fee_vnd),
                        proposed_at=NOW(), agreed_at=NULL";
            $stmt=$this->db->prepare($sql);
            $stmt->execute([
                ':id'=>$requestId, ':meeting_time'=>$data['meeting_time'],
                ':meeting_location'=>$data['meeting_location'], ':carrier'=>$data['carrier'],
                ':address'=>$data['address'], ':note'=>$data['note'], ':fee'=>$data['fee']
            ]);
            $this->db->prepare("UPDATE item_requests SET delivery_method=:method, status='accepted' WHERE id=:id")
                ->execute([':method'=>$data['method'], ':id'=>$requestId]);
            $this->db->prepare("INSERT INTO request_logs (request_id, actor_id, action, old_status, new_status, note)
                                VALUES (:id,:actor,'delivery_proposed',:old,'accepted',:note)")
                ->execute([':id'=>$requestId,':actor'=>$giverId,':old'=>$row['status'],
                           ':note'=>'Phương thức: '.$data['method'].'; phí ship đề xuất: '.$data['fee'].' VND']);
            $this->db->commit(); return true;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            error_log('Delivery propose: '.$e->getMessage()); return false;
        }
    }

    public function agree(int $requestId, int $receiverId): bool
    {
        try {
            $this->db->beginTransaction();
            $stmt=$this->db->prepare(
                "SELECT ir.status, ir.delivery_method, ir.requester_id, i.status AS item_status,
                        d.proposed_at, d.agreed_at
                 FROM item_requests ir JOIN items i ON i.id=ir.item_id
                 LEFT JOIN delivery_details d ON d.request_id=ir.id
                 WHERE ir.id=:id FOR UPDATE"
            );
            $stmt->execute([':id'=>$requestId]); $row=$stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row || (int)$row['requester_id']!==$receiverId || $row['status']!=='accepted'
                || $row['item_status']!=='reserved' || !$row['delivery_method']
                || !$row['proposed_at'] || $row['agreed_at']) {
                $this->db->rollBack(); return false;
            }
            $this->db->prepare("UPDATE delivery_details SET agreed_at=NOW() WHERE request_id=:id")
                ->execute([':id'=>$requestId]);
            $this->db->prepare("UPDATE item_requests SET status='arranged' WHERE id=:id AND status='accepted'")
                ->execute([':id'=>$requestId]);
            $this->db->prepare("INSERT INTO request_logs (request_id,actor_id,action,old_status,new_status,note)
                                VALUES (:id,:actor,'delivery_agreed','accepted','arranged',:note)")
                ->execute([':id'=>$requestId,':actor'=>$receiverId,
                           ':note'=>'Đồng ý phương thức giao nhận; chưa thanh toán phí vận chuyển']);
            $this->db->commit(); return true;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            error_log('Delivery agree: '.$e->getMessage()); return false;
        }
    }
}
