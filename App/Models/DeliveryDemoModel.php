<?php
namespace App\Models;

use App\Core\Database;
use PDO;
use Throwable;

class DeliveryDemoModel
{
    private PDO $db;
    private const STAGES = ['ready_to_pick', 'picking', 'delivering', 'delivered'];

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getState(int $requestId): ?array
    {
        $stmt = $this->db->prepare('SELECT demo_status, giver_confirmed_at, receiver_confirmed_at, demo_completed_at FROM delivery_demo_progress WHERE request_id = :id');
        $stmt->execute([':id' => $requestId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function eligibleRequest(int $requestId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT ir.id, ir.status, ir.delivery_method, ir.requester_id, i.user_id AS giver_id,
                    d.agreed_at, d.tracking_number
             FROM item_requests ir
             JOIN items i ON i.id = ir.item_id
             JOIN delivery_details d ON d.request_id = ir.id
             WHERE ir.id = :id FOR UPDATE"
        );
        $stmt->execute([':id' => $requestId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row || !in_array($row['status'], ['arranged', 'in_progress', 'awaiting_confirmation'], true)
            || $row['delivery_method'] !== 'shipping' || !$row['agreed_at'] || !$row['tracking_number']) {
            return null;
        }
        return $row;
    }

    /** Chỉ người tặng mới điều khiển các bước vận chuyển mô phỏng. */
    public function advance(int $requestId, int $actorId): string
    {
        try {
            $this->db->beginTransaction();
            $request = $this->eligibleRequest($requestId);
            if (!$request || (int)$request['giver_id'] !== $actorId) {
                $this->db->rollBack(); return 'forbidden';
            }
            $state = $this->getState($requestId);
            if (!$state) {
                $stmt = $this->db->prepare("INSERT INTO delivery_demo_progress (request_id, demo_status) VALUES (:id, 'ready_to_pick')");
                $stmt->execute([':id' => $requestId]);
                $result = 'ready_to_pick';
            } else {
                $position = array_search($state['demo_status'], self::STAGES, true);
                if ($position === false || $position === count(self::STAGES) - 1) {
                    $this->db->rollBack(); return 'finished';
                }
                if (!empty($state['giver_confirmed_at']) || !empty($state['receiver_confirmed_at'])) {
                    $this->db->rollBack(); return 'finished';
                }
                $result = self::STAGES[$position + 1];
                $stmt = $this->db->prepare('UPDATE delivery_demo_progress SET demo_status = :status WHERE request_id = :id');
                $stmt->execute([':status' => $result, ':id' => $requestId]);
            }
            $this->db->commit();
            return $result;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            error_log('GHN DEMO advance: ' . $e->getMessage());
            return 'error';
        }
    }

    /** Hai bên xác nhận ĐỘC LẬP trong bản mô phỏng, không hoàn tất giao dịch thật. */
    public function confirm(int $requestId, int $actorId): string
    {
        try {
            $this->db->beginTransaction();
            $request = $this->eligibleRequest($requestId);
            if (!$request || !in_array($actorId, [(int)$request['giver_id'], (int)$request['requester_id']], true)) {
                $this->db->rollBack(); return 'forbidden';
            }
            $state = $this->getState($requestId);
            if (!$state || $state['demo_status'] !== 'delivered') {
                $this->db->rollBack(); return 'not_delivered';
            }
            if ($state['demo_completed_at']) {
                $this->db->rollBack(); return 'completed';
            }
            $isGiver = $actorId === (int)$request['giver_id'];
            $field = $isGiver ? 'giver_confirmed_at' : 'receiver_confirmed_at';
            if ($state[$field]) {
                $this->db->rollBack(); return 'already_confirmed';
            }
            // Cột định danh chỉ được chọn từ hai giá trị cố định trên.
            $stmt = $this->db->prepare("UPDATE delivery_demo_progress SET {$field} = NOW() WHERE request_id = :id");
            $stmt->execute([':id' => $requestId]);
            $other = $isGiver ? 'receiver_confirmed_at' : 'giver_confirmed_at';
            $result = 'awaiting_confirmation';
            if ($state[$other]) {
                $stmt = $this->db->prepare('UPDATE delivery_demo_progress SET demo_completed_at = NOW() WHERE request_id = :id');
                $stmt->execute([':id' => $requestId]);
                $result = 'completed';
            }
            $this->db->commit();
            return $result;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            error_log('GHN DEMO confirm: ' . $e->getMessage());
            return 'error';
        }
    }
}
