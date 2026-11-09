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
                       ir.item_id,
                       ir.message,
                       ir.status AS request_status,
                       ir.created_at,
                       ir.giver_confirmed_at,
                       ir.receiver_confirmed_at,
                       i.title AS item_title,
                       i.image_url,
                       i.pickup_location,
                       i.status AS item_status,
                       c.name AS category_name,
                       u.full_name AS owner_name
                FROM item_requests ir
                JOIN items i ON ir.item_id = i.id
                JOIN categories c ON i.category_id = c.id
                JOIN users u ON i.user_id = u.id
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
                      AND i.status = 'available'
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
    // Tạo yêu cầu nhận đồ mới
        private ?string $error = null;

    // Lý do thất bại của lần gọi createRequest() gần nhất
    public function getError(): ?string
    {
        return $this->error;
    }

    // Tạo yêu cầu nhận đồ mới, có chống đăng ký trùng
    public function createRequest(int $itemId, int $requesterId, string $message = ''): bool
    {
        $this->error = null;

        try {
            $this->db->beginTransaction();

            // Khóa dòng món đồ: hai người bấm cùng lúc sẽ được xử lý lần lượt
            $stmt = $this->db->prepare("SELECT user_id, status FROM items WHERE id = :id FOR UPDATE");
            $stmt->execute([':id' => $itemId]);
            $item = $stmt->fetch();

            if (!$item) {
                $this->error = 'Món đồ không tồn tại.';
            } elseif ((int)$item['user_id'] === $requesterId) {
                $this->error = 'Bạn không thể xin món đồ do chính mình đăng.';
            } elseif ($item['status'] !== 'available') {
                $this->error = 'Món đồ này đã có người nhận.';
            } else {
                $stmt = $this->db->prepare(
                    "SELECT COUNT(*) FROM item_requests
                     WHERE item_id = :item_id AND requester_id = :requester_id AND status = 'pending'"
                );
                $stmt->execute([':item_id' => $itemId, ':requester_id' => $requesterId]);

                if ((int)$stmt->fetchColumn() > 0) {
                    $this->error = 'Bạn đã gửi yêu cầu cho món đồ này rồi, hãy chờ chủ đồ phản hồi.';
                }
            }

            if ($this->error !== null) {
                $this->db->rollBack();
                return false;
            }

            $stmt = $this->db->prepare(
                "INSERT INTO item_requests (item_id, requester_id, message, status, created_at)
                 VALUES (:item_id, :requester_id, :message, 'pending', NOW())"
            );
            $stmt->execute([
                ':item_id'      => $itemId,
                ':requester_id' => $requesterId,
                ':message'      => $message
            ]);

            $this->db->commit();
            return true;

        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log($e->getMessage());
            $this->error = 'Có lỗi xảy ra, vui lòng thử lại.';
            return false;
        }
    }

    // Người nhận hủy yêu cầu (chỉ khi chủ đồ chưa xử lý)
    public function cancelRequest(int $requestId, int $userId): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE item_requests
             SET status = 'cancelled'
             WHERE id = :id AND requester_id = :user_id AND status = 'pending'"
        );
        $stmt->execute([':id' => $requestId, ':user_id' => $userId]);

        return $stmt->rowCount() > 0;
    }

    // Một bên xác nhận đã trao/nhận; khi cả hai xác nhận thì giao dịch hoàn tất
    public function confirmHandover(int $requestId, int $userId): bool
    {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare(
                "SELECT ir.requester_id, ir.giver_confirmed_at, ir.receiver_confirmed_at,
                        i.user_id AS owner_id
                 FROM item_requests ir
                 JOIN items i ON ir.item_id = i.id
                 WHERE ir.id = :id AND ir.status = 'accepted'
                 FOR UPDATE"
            );
            $stmt->execute([':id' => $requestId]);
            $row = $stmt->fetch();

            if (!$row) {
                $this->db->rollBack();
                return false;
            }

            $isGiver    = (int)$row['owner_id'] === $userId;
            $isReceiver = (int)$row['requester_id'] === $userId;

            if (!$isGiver && !$isReceiver) {
                $this->db->rollBack();
                return false;
            }

            // Tên cột lấy từ 2 giá trị cố định, không lấy từ dữ liệu người dùng
            $column = $isGiver ? 'giver_confirmed_at' : 'receiver_confirmed_at';

            $this->db->prepare(
                "UPDATE item_requests SET $column = COALESCE($column, NOW()) WHERE id = :id"
            )->execute([':id' => $requestId]);

            $giverDone    = $isGiver    || $row['giver_confirmed_at'] !== null;
            $receiverDone = $isReceiver || $row['receiver_confirmed_at'] !== null;

            if ($giverDone && $receiverDone) {
                $this->db->prepare(
                    "UPDATE item_requests SET status = 'completed' WHERE id = :id"
                )->execute([':id' => $requestId]);
            }

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

    // Lịch sử phía người tặng: mọi yêu cầu (mọi trạng thái) trên đồ mình đăng
    public function getGivenHistory($userId)
    {
        $sql = "SELECT ir.id AS request_id,
                       ir.message,
                       ir.status AS request_status,
                       ir.created_at,
                       ir.giver_confirmed_at,
                       ir.receiver_confirmed_at,
                       i.title AS item_title,
                       u.full_name AS requester_name
                FROM item_requests ir
                JOIN items i ON ir.item_id = i.id
                JOIN users u ON ir.requester_id = u.id
                WHERE i.user_id = :user_id
                ORDER BY ir.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll();
    }
}