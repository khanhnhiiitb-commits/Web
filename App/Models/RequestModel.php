<?php

namespace App\Models;

use App\Core\Database;
use PDO;
use Exception;

class RequestModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // Chấp nhận yêu cầu nhận đồ
    public function acceptRequest($requestId)
    {
        try {
            // Bắt đầu transaction
            $this->db->beginTransaction();

            // Lấy item_id của yêu cầu được chọn
            $sql = "SELECT item_id
                    FROM item_requests
                    WHERE id = :request_id
                    FOR UPDATE";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':request_id' => $requestId
            ]);

            $request = $stmt->fetch();

            // Nếu không tìm thấy yêu cầu
            if (!$request) {
                $this->db->rollBack();
                return false;
            }

            $itemId = $request['item_id'];

            // Bước 1: Chấp nhận yêu cầu được chọn
            $sql = "UPDATE item_requests
                    SET status = 'accepted'
                    WHERE id = :request_id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':request_id' => $requestId
            ]);

            // Từ chối các yêu cầu khác đang chờ của cùng món đồ
            $sql = "UPDATE item_requests
                    SET status = 'rejected'
                    WHERE item_id = :item_id
                    AND id != :request_id
                    AND status = 'pending'";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':item_id' => $itemId,
                ':request_id' => $requestId
            ]);

            // Bước 2: Cập nhật trạng thái món đồ thành gifted
            $sql = "UPDATE items
                    SET status = 'gifted'
                    WHERE id = :item_id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':item_id' => $itemId
            ]);

            // Nếu tất cả thành công, xác nhận thay đổi
            $this->db->commit();

            return true;

        } catch (\Throwable $e) {

            // Nếu có lỗi, hoàn tác các thay đổi
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            return false;
        }
    }
}
