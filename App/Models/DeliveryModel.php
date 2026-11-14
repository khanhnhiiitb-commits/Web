<?php

namespace App\Models;

use App\Core\Database;
use PDO;
use Throwable;

// Quản lý thỏa thuận giao nhận, chưa xử lý thanh toán hoặc tạo vận đơn GHN.
class DeliveryModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getForParticipant(int $requestId, int $userId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT ir.id AS request_id, ir.status, ir.delivery_method,
                    ir.requester_id, ir.giver_confirmed_at, ir.receiver_confirmed_at,
                    ir.completed_at, i.user_id AS giver_id, i.title AS item_title,
                    d.meeting_time, d.meeting_location, d.shipping_carrier,
                    d.shipping_address, d.delivery_note, d.shipping_fee_vnd,
                    d.agreed_at, d.proposed_at, d.tracking_number,
                    d.to_province_id, d.to_district_id, d.to_ward_code,
                    d.package_weight, d.package_length,
                    d.package_width, d.package_height,
                    d.service_type_id, d.payment_type_id, d.ghn_status
             FROM item_requests ir
             JOIN items i ON i.id = ir.item_id
             LEFT JOIN delivery_details d ON d.request_id = ir.id
             WHERE ir.id = :request_id
               AND (ir.requester_id = :receiver_id OR i.user_id = :giver_id)"
        );
        $stmt->execute([
            ':request_id' => $requestId,
            ':receiver_id' => $userId,
            ':giver_id' => $userId,
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    // Chỉ người tặng đề xuất khi yêu cầu accepted/arranged và món đồ reserved.
    public function propose(int $requestId, int $giverId, array $data): bool
    {
        try {
            $this->db->beginTransaction();

            // [CẬP NHẬT 2] Kiểm tra báo giá có thuộc đúng món đồ, và địa chỉ gửi chưa thay đổi.
            $stmt = $this->db->prepare(
                "SELECT ir.status, ir.requester_id, ir.item_id,
                        i.user_id AS giver_id, i.status AS item_status,
                        i.ghn_district_id, i.ghn_ward_code,
                        d.tracking_number, d.ghn_status
                 FROM item_requests ir
                 JOIN items i ON i.id = ir.item_id
                 LEFT JOIN delivery_details d ON d.request_id = ir.id
                 WHERE ir.id = :id FOR UPDATE"
            );
            $stmt->execute([':id' => $requestId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (
                !$row || (int) $row['giver_id'] !== $giverId ||
                $row['item_status'] !== 'reserved' ||
                !in_array($row['status'], ['accepted', 'arranged'], true) ||
                (!empty($row['tracking_number']) || !empty($row['ghn_status']))
            ) {
                $this->db->rollBack();
                return false;
            }

            // Do not allow changing delivery method/fee/payer after a payment is pending or paid.
            $paymentLock = $this->db->prepare(
                "SELECT 1 FROM vnpay_payments WHERE request_id = :id AND status IN ('pending','paid') LIMIT 1"
            );
            $paymentLock->execute([':id' => $requestId]);
            if ($paymentLock->fetchColumn()) {
                $this->db->rollBack();
                return false;
            }

            if ($data['method'] === 'shipping') {
                if (
                    (int) ($data['item_id'] ?? 0) !== (int) $row['item_id'] ||
                    (int) ($data['from_district_id'] ?? 0) !== (int) ($row['ghn_district_id'] ?? 0) ||
                    (string) ($data['from_ward_code'] ?? '') !== (string) ($row['ghn_ward_code'] ?? '')
                ) {
                    $this->db->rollBack();
                    return false;
                }
            }

            // [CẬP NHẬT 3] Lưu thông tin GHN để không mất khi session bị xóa.
            // Cần UNIQUE KEY trên delivery_details(request_id).
            $sql = "INSERT INTO delivery_details
                        (request_id, meeting_time, meeting_location, shipping_carrier,
                         shipping_address, delivery_note, shipping_fee_vnd,
                         to_province_id, to_district_id, to_ward_code,
                         package_weight, package_length, package_width, package_height,
                         service_type_id, payment_type_id, ghn_status,
                         proposed_at, agreed_at)
                    VALUES
                        (:id, :meeting_time, :meeting_location, :carrier,
                         :address, :note, :fee,
                         :to_province_id, :to_district_id, :to_ward_code,
                         :package_weight, :package_length, :package_width, :package_height,
                         :service_type_id, :payment_type_id, NULL,
                         NOW(), NULL)
                    ON DUPLICATE KEY UPDATE
                        meeting_time = VALUES(meeting_time),
                        meeting_location = VALUES(meeting_location),
                        shipping_carrier = VALUES(shipping_carrier),
                        shipping_address = VALUES(shipping_address),
                        delivery_note = VALUES(delivery_note),
                        shipping_fee_vnd = VALUES(shipping_fee_vnd),
                        to_province_id = VALUES(to_province_id),
                        to_district_id = VALUES(to_district_id),
                        to_ward_code = VALUES(to_ward_code),
                        package_weight = VALUES(package_weight),
                        package_length = VALUES(package_length),
                        package_width = VALUES(package_width),
                        package_height = VALUES(package_height),
                        service_type_id = VALUES(service_type_id),
                        payment_type_id = VALUES(payment_type_id),
                        ghn_status = NULL,
                        proposed_at = NOW(),
                        agreed_at = NULL";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':id' => $requestId,
                ':meeting_time' => $data['meeting_time'],
                ':meeting_location' => $data['meeting_location'],
                ':carrier' => $data['carrier'],
                ':address' => $data['address'],
                ':note' => $data['note'],
                ':fee' => $data['fee'],
                ':to_province_id' => $data['to_province_id'],
                ':to_district_id' => $data['to_district_id'],
                ':to_ward_code' => $data['to_ward_code'],
                ':package_weight' => $data['package_weight'],
                ':package_length' => $data['package_length'],
                ':package_width' => $data['package_width'],
                ':package_height' => $data['package_height'],
                ':service_type_id' => $data['service_type_id'],
                ':payment_type_id' => $data['payment_type_id'],
            ]);

            $this->db->prepare(
                "UPDATE item_requests
                 SET delivery_method = :method, status = 'accepted'
                 WHERE id = :id"
            )->execute([':method' => $data['method'], ':id' => $requestId]);

            $this->db->prepare(
                "INSERT INTO request_logs
                    (request_id, actor_id, action, old_status, new_status, note)
                 VALUES (:id, :actor, 'delivery_proposed', :old, 'accepted', :note)"
            )->execute([
                ':id' => $requestId,
                ':actor' => $giverId,
                ':old' => $row['status'],
                ':note' => 'Phương thức: ' . $data['method']
                    . '; phí ship đề xuất: ' . $data['fee'] . ' VND',
            ]);

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('Delivery propose: ' . $e->getMessage());
            return false;
        }
    }

    public function agree(int $requestId, int $receiverId): bool
    {
        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare(
                "SELECT ir.status, ir.delivery_method, ir.requester_id,
                        i.status AS item_status,
                        d.proposed_at, d.agreed_at, d.tracking_number, d.ghn_status
                 FROM item_requests ir
                 JOIN items i ON i.id = ir.item_id
                 LEFT JOIN delivery_details d ON d.request_id = ir.id
                 WHERE ir.id = :id FOR UPDATE"
            );
            $stmt->execute([':id' => $requestId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (
                !$row || (int) $row['requester_id'] !== $receiverId ||
                $row['status'] !== 'accepted' ||
                $row['item_status'] !== 'reserved' ||
                !$row['delivery_method'] || !$row['proposed_at'] ||
                $row['agreed_at'] || (!empty($row['tracking_number']) || !empty($row['ghn_status']))
            ) {
                $this->db->rollBack();
                return false;
            }

            $this->db->prepare(
                'UPDATE delivery_details SET agreed_at = NOW() WHERE request_id = :id'
            )->execute([':id' => $requestId]);
            $this->db->prepare(
                "UPDATE item_requests SET status = 'arranged'
                 WHERE id = :id AND status = 'accepted'"
            )->execute([':id' => $requestId]);
            $this->db->prepare(
                "INSERT INTO request_logs
                    (request_id, actor_id, action, old_status, new_status, note)
                 VALUES (:id, :actor, 'delivery_agreed', 'accepted', 'arranged', :note)"
            )->execute([
                ':id' => $requestId,
                ':actor' => $receiverId,
                ':note' => 'Đồng ý phương thức giao nhận; chưa thanh toán phí vận chuyển',
            ]);
            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('Delivery agree: ' . $e->getMessage());
            return false;
        }
    }
    /** [GHN] Claim tạo đơn, serialize theo row item_requests. */
    public function claimShipment(int $requestId, int $giverId, bool $retry = false): ?array
    {
        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare(
                "SELECT ir.id, ir.status, ir.delivery_method, i.id AS item_id, i.title AS item_title,
                        i.status AS item_status, i.user_id AS giver_id,
                        i.ghn_province_id, i.ghn_district_id, i.ghn_ward_code,
                        i.sender_address_detail,
                        giver.full_name AS from_name, giver.phone AS from_phone,
                        receiver.full_name AS to_name, receiver.phone AS to_phone,
                        d.shipping_address, d.agreed_at, d.tracking_number, d.ghn_status,
                        d.to_province_id, d.to_district_id, d.to_ward_code,
                        d.package_weight, d.package_length, d.package_width, d.package_height,
                        d.service_type_id, d.payment_type_id
                 FROM item_requests ir
                 JOIN items i ON i.id = ir.item_id
                 JOIN users giver ON giver.id = i.user_id
                 JOIN users receiver ON receiver.id = ir.requester_id
                 JOIN delivery_details d ON d.request_id = ir.id
                 WHERE ir.id = :id FOR UPDATE"
            );
            $stmt->execute([':id' => $requestId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row || (int)$row['giver_id'] !== $giverId ||
                $row['status'] !== 'arranged' || $row['delivery_method'] !== 'shipping' ||
                $row['item_status'] !== 'reserved' || empty($row['agreed_at']) ||
                !empty($row['tracking_number']) ||
                ($retry ? ($row['ghn_status'] !== 'needs_review') : !empty($row['ghn_status']))) {
                $this->db->rollBack(); return null;
            }
            foreach (['ghn_province_id','ghn_district_id','to_province_id','to_district_id',
                      'package_weight','package_length','package_width','package_height',
                      'service_type_id','payment_type_id'] as $key) {
                if ((int)($row[$key] ?? 0) <= 0) { $this->db->rollBack(); return null; }
            }
            if (!in_array((int)$row['payment_type_id'], [1, 2], true) ||
                !in_array((int)$row['service_type_id'], [2, 5], true) ||
                empty($row['ghn_ward_code']) || empty($row['to_ward_code']) ||
                trim((string)($row['sender_address_detail'] ?? '')) === '' ||
                trim((string)($row['shipping_address'] ?? '')) === '' ||
                trim((string)($row['from_name'] ?? '')) === '' ||
                trim((string)($row['to_name'] ?? '')) === '' ||
                !preg_match('/^0[0-9]{9,10}$/D', preg_replace('/\s+/', '', (string)($row['from_phone'] ?? ''))) ||
                !preg_match('/^0[0-9]{9,10}$/D', preg_replace('/\s+/', '', (string)($row['to_phone'] ?? '')))) {
                $this->db->rollBack(); return null;
            }
            $expectedStatus = $retry ? "ghn_status = 'needs_review'" : 'ghn_status IS NULL';
            $stmt = $this->db->prepare(
                "UPDATE delivery_details SET ghn_status = 'creating'
                 WHERE request_id = :id AND tracking_number IS NULL AND {$expectedStatus}"
            );
            $stmt->execute([':id' => $requestId]);
            if ($stmt->rowCount() !== 1) { $this->db->rollBack(); return null; }
            $this->db->commit(); return $row;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            error_log('GHN claimShipment: ' . $e->getMessage());
            return null;
        }
    }

    public function finishShipment(int $requestId, int $giverId, string $code): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE delivery_details d
             JOIN item_requests ir ON ir.id = d.request_id
             JOIN items i ON i.id = ir.item_id
             SET d.tracking_number = :code, d.ghn_status = 'created'
             WHERE d.request_id = :id AND i.user_id = :giver
               AND d.ghn_status = 'creating' AND d.tracking_number IS NULL"
        );
        $stmt->execute([':code' => $code, ':id' => $requestId, ':giver' => $giverId]);
        return $stmt->rowCount() === 1;
    }

    public function shipmentNeedsReview(int $requestId, int $giverId): void
    {
        $stmt = $this->db->prepare(
            "UPDATE delivery_details d
             JOIN item_requests ir ON ir.id = d.request_id
             JOIN items i ON i.id = ir.item_id
             SET d.ghn_status = 'needs_review'
             WHERE d.request_id = :id AND i.user_id = :giver
               AND d.ghn_status = 'creating' AND d.tracking_number IS NULL"
        );
        $stmt->execute([':id' => $requestId, ':giver' => $giverId]);
    }

    public function canReconcileShipment(int $requestId, int $giverId): bool
    {
        $stmt = $this->db->prepare(
            "SELECT 1 FROM item_requests ir
             JOIN items i ON i.id = ir.item_id
             JOIN delivery_details d ON d.request_id = ir.id
             WHERE ir.id = :id AND i.user_id = :giver
               AND ir.delivery_method = 'shipping'
               AND d.ghn_status IN ('creating','needs_review')
               AND d.tracking_number IS NULL LIMIT 1"
        );
        $stmt->execute([':id' => $requestId, ':giver' => $giverId]);
        return (bool)$stmt->fetchColumn();
    }

    public function finishReviewedShipment(int $requestId, int $giverId, string $code): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE delivery_details d
             JOIN item_requests ir ON ir.id = d.request_id
             JOIN items i ON i.id = ir.item_id
             SET d.tracking_number = :code, d.ghn_status = 'created'
             WHERE d.request_id = :id AND i.user_id = :giver
               AND d.ghn_status IN ('creating','needs_review')
               AND d.tracking_number IS NULL"
        );
        $stmt->execute([':code'=>$code, ':id'=>$requestId, ':giver'=>$giverId]);
        return $stmt->rowCount() === 1;
    }


    public function updateGhnStatus(
        int $requestId,
        int $userId,
        string $trackingNumber,
        string $ghnStatus
    ): bool {
        $stmt = $this->db->prepare(
            "UPDATE delivery_details d
             JOIN item_requests ir ON ir.id = d.request_id
             JOIN items i ON i.id = ir.item_id
             SET d.ghn_status = :ghn_status
             WHERE d.request_id = :request_id
               AND ir.delivery_method = 'shipping'
               AND d.tracking_number = :tracking_number
               AND (ir.requester_id = :receiver_id OR i.user_id = :giver_id)"
        );
        $stmt->execute([
            ':ghn_status' => $ghnStatus,
            ':request_id' => $requestId,
            ':tracking_number' => $trackingNumber,
            ':receiver_id' => $userId,
            ':giver_id' => $userId,
        ]);
        // PDO rowCount có thể bằng 0 khi GHN không thay đổi trạng thái.
        return $stmt->rowCount() > 0 || $this->hasShipmentStatus(
            $requestId, $userId, $trackingNumber, $ghnStatus
        );
    }

    private function hasShipmentStatus(
        int $requestId,
        int $userId,
        string $trackingNumber,
        string $ghnStatus
    ): bool {
        $stmt = $this->db->prepare(
            "SELECT 1 FROM delivery_details d
             JOIN item_requests ir ON ir.id = d.request_id
             JOIN items i ON i.id = ir.item_id
             WHERE d.request_id = :request_id
               AND ir.delivery_method = 'shipping'
               AND d.tracking_number = :tracking_number
               AND d.ghn_status = :ghn_status
               AND (ir.requester_id = :receiver_id OR i.user_id = :giver_id)
             LIMIT 1"
        );
        $stmt->execute([
            ':request_id' => $requestId,
            ':tracking_number' => $trackingNumber,
            ':ghn_status' => $ghnStatus,
            ':receiver_id' => $userId,
            ':giver_id' => $userId,
        ]);
        return (bool) $stmt->fetchColumn();
    }


    
    public function confirmHandover(int $requestId, int $actorId): string
    {
        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare(
                "SELECT ir.id, ir.item_id, ir.status, ir.delivery_method,
                        ir.requester_id, ir.giver_confirmed_at, ir.receiver_confirmed_at,
                        i.user_id AS giver_id, i.status AS item_status,
                        d.agreed_at, d.tracking_number, d.ghn_status
                 FROM item_requests ir
                 JOIN items i ON i.id = ir.item_id
                 LEFT JOIN delivery_details d ON d.request_id = ir.id
                 WHERE ir.id = :id FOR UPDATE"
            );
            $stmt->execute([':id' => $requestId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row || !in_array($actorId, [(int)$row['giver_id'], (int)$row['requester_id']], true)) {
                $this->db->rollBack(); return 'forbidden';
            }
            if ($row['status'] === 'completed') {
                $this->db->rollBack(); return 'completed';
            }
            if (!in_array($row['status'], ['arranged', 'in_progress', 'awaiting_confirmation'], true)
                || $row['item_status'] !== 'reserved' || empty($row['agreed_at'])) {
                $this->db->rollBack(); return 'invalid_state';
            }
            if ($row['delivery_method'] === 'shipping') {
                if (empty($row['tracking_number'])) {
                    $this->db->rollBack(); return 'not_delivered';
                }
            } elseif ($row['delivery_method'] !== 'pickup') {
                $this->db->rollBack(); return 'invalid_state';
            }
            $isGiver = $actorId === (int)$row['giver_id'];
            $field = $isGiver ? 'giver_confirmed_at' : 'receiver_confirmed_at';
            if (!empty($row[$field])) {
                $this->db->rollBack(); return 'already_confirmed';
            }
            // Tên cột lấy từ danh sách cố định, không nhận từ input của trình duyệt.
            $this->db->prepare("UPDATE item_requests SET {$field} = NOW() WHERE id = :id")
                ->execute([':id' => $requestId]);
            $bothConfirmed = !empty($row[$isGiver ? 'receiver_confirmed_at' : 'giver_confirmed_at']);
            if ($bothConfirmed) {
                $itemStmt = $this->db->prepare(
                    "UPDATE items SET status = 'gifted' WHERE id = :id AND status = 'reserved'"
                );
                $itemStmt->execute([':id' => (int)$row['item_id']]);
                if ($itemStmt->rowCount() !== 1) {
                    throw new \RuntimeException('Không thể cập nhật trạng thái món đồ.');
                }
                $this->db->prepare(
                    "UPDATE item_requests SET status = 'completed', completed_at = NOW()
                     WHERE id = :id"
                )->execute([':id' => $requestId]);
                $newStatus = 'completed';
            } else {
                $this->db->prepare(
                    "UPDATE item_requests SET status = 'awaiting_confirmation' WHERE id = :id"
                )->execute([':id' => $requestId]);
                $newStatus = 'awaiting_confirmation';
            }
            $this->db->prepare(
                "INSERT INTO request_logs
                    (request_id, actor_id, action, old_status, new_status, note)
                 VALUES (:id, :actor, :action, :old_status, :new_status, :note)"
            )->execute([
                ':id' => $requestId,
                ':actor' => $actorId,
                ':action' => $bothConfirmed ? 'delivery_completed' : 'handover_confirmed',
                ':old_status' => $row['status'],
                ':new_status' => $newStatus,
                ':note' => $isGiver ? 'Người tặng xác nhận đã bàn giao đồ' : 'Người nhận xác nhận đã nhận đồ',
            ]);
            $this->db->commit();
            return $newStatus;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            error_log('Delivery confirmHandover request=' . $requestId . ': ' . $e->getMessage());
            return 'error';
        }
    }

}
