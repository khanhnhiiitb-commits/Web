<?php

namespace App\Models;

use App\Core\Database;
use PDO;
use Throwable;

class RequestModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // 1. Danh sách các món đồ mình đi xin
    public function getMyRequests($userId)
    {
        $sql = "SELECT ir.id AS request_id,
                       ir.message,
                       ir.status AS request_status,
                       ir.created_at,
                       i.title AS item_title
                FROM item_requests ir
                JOIN items i ON ir.item_id = i.id
                WHERE ir.requester_id = :user_id
                ORDER BY ir.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll();
    }

    // 2. Danh sách người xin những món đồ mình đăng
    public function getRequestsForMyItems($userId)
    {
        $sql = "SELECT ir.id AS request_id,
                       ir.item_id,
                       ir.message,
                       ir.status AS request_status,
                       ir.created_at,
                       i.title AS item_title,
                       u.full_name AS requester_name
                FROM item_requests ir
                JOIN items i ON ir.item_id = i.id
                JOIN users u ON ir.requester_id = u.id
                WHERE i.user_id = :user_id
                  AND ir.status = 'pending'
                ORDER BY ir.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll();
    }

    // 3. Chủ đồ đồng ý cho người xin
    public function acceptRequest($requestId, $userId)
    {
        try {
            $this->db->beginTransaction();

            // Chỉ cho phép chủ đồ duyệt yêu cầu
            $sql = "SELECT ir.item_id
                    FROM item_requests ir
                    JOIN items i ON ir.item_id = i.id
                    WHERE ir.id = :request_id
                      AND i.user_id = :user_id
                      AND ir.status = 'pending'
                    FOR UPDATE";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'request_id' => $requestId,
                'user_id' => $userId
            ]);

            $request = $stmt->fetch();

            if (!$request) {
                $this->db->rollBack();
                return false;
            }

            $itemId = $request['item_id'];

            // Chấp nhận yêu cầu được chọn
            $sql = "UPDATE item_requests
                    SET status = 'accepted'
                    WHERE id = :request_id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['request_id' => $requestId]);

            // Từ chối những yêu cầu đang chờ khác của cùng món đồ
            $sql = "UPDATE item_requests
                    SET status = 'rejected'
                    WHERE item_id = :item_id
                      AND id != :request_id
                      AND status = 'pending'";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'item_id' => $itemId,
                'request_id' => $requestId
            ]);

            // Đánh dấu món đồ đã được tặng
            $sql = "UPDATE items
                    SET status = 'gifted'
                    WHERE id = :item_id
                      AND user_id = :user_id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'item_id' => $itemId,
                'user_id' => $userId
            ]);

            $this->db->commit();
            return true;

        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            error_log($e->getMessage());
            return false;
        }
    }

    // 4. Chủ đồ từ chối một yêu cầu
    public function rejectRequest($requestId, $userId)
    {
        $sql = "UPDATE item_requests ir
                JOIN items i ON ir.item_id = i.id
                SET ir.status = 'rejected'
                WHERE ir.id = :request_id
                  AND i.user_id = :user_id
                  AND ir.status = 'pending'";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'request_id' => $requestId,
            'user_id' => $userId
        ]);

        return $stmt->rowCount() > 0;
    }
}