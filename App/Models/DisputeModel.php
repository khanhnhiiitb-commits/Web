<?php
namespace App\Models;

use App\Core\Database;
use PDO;
use RuntimeException;
use Throwable;

class DisputeModel
{
    private PDO $db;
    public function __construct() { $this->db = Database::getConnection(); }

    public function getRequest(int $id, int $userId): ?array
    {
        $stmt = $this->db->prepare("SELECT ir.id, ir.status, ir.requester_id, i.user_id AS giver_id, i.title AS item_title
            FROM item_requests ir JOIN items i ON i.id = ir.item_id
            WHERE ir.id = :id AND (ir.requester_id = :receiver OR i.user_id = :giver) LIMIT 1");
        $stmt->execute([':id'=>$id, ':receiver'=>$userId, ':giver'=>$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function open(int $requestId, int $userId, string $reason, string $description): int
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("SELECT ir.status, ir.requester_id, i.user_id AS giver_id
                FROM item_requests ir JOIN items i ON i.id = ir.item_id WHERE ir.id = ? FOR UPDATE");
            $stmt->execute([$requestId]);
            $r = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$r || !in_array($userId, [(int)$r['requester_id'], (int)$r['giver_id']], true))
                throw new RuntimeException('Bạn không có quyền khiếu nại giao dịch này.');
            if (!in_array($r['status'], ['accepted','arranged','in_progress','awaiting_confirmation'], true))
                throw new RuntimeException('Giao dịch hiện không cho phép mở khiếu nại.');
            $check = $this->db->prepare("SELECT id FROM disputes WHERE request_id=? AND status IN ('open','reviewing') LIMIT 1");
            $check->execute([$requestId]);
            if ($check->fetchColumn()) throw new RuntimeException('Giao dịch đang có khiếu nại chưa giải quyết.');
            $insert = $this->db->prepare("INSERT INTO disputes (request_id,reporter_id,reason,description,status,previous_request_status)
                VALUES (?,?,?,?,'open',?)");
            $insert->execute([$requestId,$userId,$reason,$description,$r['status']]);
            $id = (int)$this->db->lastInsertId();
            $this->db->prepare("UPDATE item_requests SET status='disputed' WHERE id=?")->execute([$requestId]);
            $this->db->prepare("INSERT INTO request_logs (request_id,actor_id,action,old_status,new_status,note)
                VALUES (?,?, 'dispute_opened',?,'disputed',?)")
                ->execute([$requestId,$userId,$r['status'],'Khiếu nại #'.$id]);
            $this->db->commit();
            return $id;
        } catch (Throwable $e) { $this->db->rollBack(); throw $e; }
    }

    public function findAccessible(int $id, int $userId, bool $admin): ?array
    {
        $sql = "SELECT d.*, i.title AS item_title, ir.requester_id, i.user_id AS giver_id,
                rp.full_name AS reporter_name, rv.full_name AS resolver_name
                FROM disputes d JOIN item_requests ir ON ir.id=d.request_id
                JOIN items i ON i.id=ir.item_id
                JOIN users rp ON rp.id=d.reporter_id
                LEFT JOIN users rv ON rv.id=d.resolved_by
                WHERE d.id=:id";
        if (!$admin) $sql .= ' AND (ir.requester_id=:user_a OR i.user_id=:user_b)';
        $stmt = $this->db->prepare($sql);
        $params=[':id'=>$id];
        if (!$admin) { $params[':user_a']=$userId; $params[':user_b']=$userId; }
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function messages(int $id): array
    {
        $s=$this->db->prepare('SELECT dm.*, u.full_name AS sender_name, u.role AS sender_role FROM dispute_messages dm JOIN users u ON dm.sender_id=u.id WHERE dm.dispute_id=? ORDER BY dm.created_at,dm.id');
        $s->execute([$id]); return $s->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addMessage(int $id, int $userId, string $body): void
    {
        $s=$this->db->prepare('INSERT INTO dispute_messages(dispute_id,sender_id,content) VALUES(?,?,?)');
        $s->execute([$id,$userId,$body]);
    }

    public function myDisputes(int $userId): array
    {
        $s=$this->db->prepare("SELECT d.id,d.request_id,d.reason,d.status,d.created_at,i.title AS item_title
            FROM disputes d JOIN item_requests ir ON d.request_id=ir.id JOIN items i ON ir.item_id=i.id
            WHERE ir.requester_id=? OR i.user_id=? ORDER BY d.created_at DESC,d.id DESC");
        $s->execute([$userId,$userId]); return $s->fetchAll(PDO::FETCH_ASSOC);
    }

    public function allDisputes(): array
    {
        return $this->db->query("SELECT d.id,d.request_id,d.reason,d.status,d.created_at,i.title AS item_title,
            u.full_name AS reporter_name FROM disputes d JOIN item_requests ir ON ir.id=d.request_id
            JOIN items i ON i.id=ir.item_id JOIN users u ON u.id=d.reporter_id ORDER BY FIELD(d.status,'open','reviewing','resolved','closed'),d.created_at DESC")
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateByAdmin(int $id, int $adminId, string $status, string $note): void
    {
        $this->db->beginTransaction();
        try {
            $s=$this->db->prepare("SELECT d.*,ir.status AS request_status FROM disputes d JOIN item_requests ir ON ir.id=d.request_id WHERE d.id=? FOR UPDATE");
            $s->execute([$id]); $d=$s->fetch(PDO::FETCH_ASSOC);
            if (!$d) throw new RuntimeException('Không tìm thấy khiếu nại.');
            $allowed=['open'=>['reviewing','resolved'], 'reviewing'=>['resolved'], 'resolved'=>['closed']];
            if (!in_array($status,$allowed[$d['status']]??[],true)) throw new RuntimeException('Chuyển trạng thái không hợp lệ.');
            if ($status==='resolved' && trim($note)==='') throw new RuntimeException('Cần ghi kết quả giải quyết.');
            if ($status==='resolved') {
                $restore=$d['previous_request_status'];
                if ($d['request_status'] !== 'disputed' || !in_array($restore,['accepted','arranged','in_progress','awaiting_confirmation'],true))
                    throw new RuntimeException('Không thể khôi phục trạng thái giao dịch.');
                $this->db->prepare('UPDATE item_requests SET status=? WHERE id=?')->execute([$restore,$d['request_id']]);
                $this->db->prepare("INSERT INTO request_logs(request_id,actor_id,action,old_status,new_status,note) VALUES(?,?,'dispute_resolved','disputed',?,?)")
                    ->execute([$d['request_id'],$adminId,$restore,'Khiếu nại #'.$id.': '.$note]);
            }
            $s=$this->db->prepare("UPDATE disputes SET status=?,resolved_by=CASE WHEN ?='resolved' THEN ? ELSE resolved_by END,
                resolved_at=CASE WHEN ?='resolved' THEN NOW() ELSE resolved_at END,
                resolution_note=CASE WHEN ?='resolved' THEN ? ELSE resolution_note END WHERE id=?");
            $s->execute([$status,$status,$adminId,$status,$status,$note,$id]);
            $this->db->commit();
        } catch (Throwable $e) { $this->db->rollBack(); throw $e; }
    }
}
