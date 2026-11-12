<?php
namespace App\Models;

use App\Core\Database;
use DateTimeImmutable;
use DateTimeZone;
use PDO;
use RuntimeException;
use Throwable;

final class GhnEventModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function history(int $requestId, int $userId): array
    {
        $sql = 'SELECT e.event_type, e.ghn_status, e.event_time, e.source, e.applied
                FROM ghn_order_events e
                JOIN item_requests ir ON ir.id = e.request_id
                JOIN items i ON i.id = ir.item_id
                WHERE e.request_id = :request_id
                  AND (i.user_id = :giver OR ir.requester_id = :receiver)
                ORDER BY e.event_time DESC, e.id DESC LIMIT 40';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':request_id' => $requestId, ':giver' => $userId, ':receiver' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** @return string applied|recorded|duplicate|retry|ignored */
    public function acceptWebhook(array $data, int $shopId): string
    {
        $code = trim((string) ($data['OrderCode'] ?? ''));
        $type = strtolower(trim((string) ($data['Type'] ?? '')));
        $status = strtolower(trim((string) ($data['Status'] ?? '')));
        if ($status === '' && !in_array($type, ['create', 'switch_status'], true)) {
            $status = 'unknown';
        }
        $rawTime = trim((string) ($data['Time'] ?? ''));
        $clientCode = trim((string) ($data['ClientOrderCode'] ?? ''));
        $actualShopId = filter_var($data['ShopID'] ?? null, FILTER_VALIDATE_INT);
        $types = ['create', 'switch_status', 'update_weight', 'update_cod',
            'update_fee', 'update_payment_type', 'cod', 'update_partial_return'];

        if (!preg_match('/^[a-zA-Z0-9_-]{3,80}$/D', $code) ||
            !in_array($type, $types, true) ||
            !preg_match('/^[a-z0-9_-]{1,100}$/D', $status) ||
            strlen($rawTime) > 60 || $rawTime === '' ||
            $actualShopId === false || (int) $actualShopId !== $shopId) {
            throw new RuntimeException('Invalid GHN event content or ShopID.');
        }

        try {
            $dt = new DateTimeImmutable($rawTime);
        } catch (Throwable $e) {
            throw new RuntimeException('Invalid GHN event time.', 0, $e);
        }
        $eventTime = $dt->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s.u');
        // GHN mô tả key duy nhất: OrderCode + Type + Time.
        $eventKey = hash('sha256', $code . '|' . $type . '|' . $rawTime);
        return $this->record($code, $type, $status, $eventTime, $eventKey,
            'webhook', $clientCode, in_array($type, ['create', 'switch_status'], true));
    }

    
public function acceptPolling(
    int $requestId,
    string $code,
    string $status
): bool {
    $code = trim($code);
    $status = strtolower(trim($status));

    if (
        $requestId <= 0 ||
        !preg_match('/^[a-zA-Z0-9_-]{3,80}$/D', $code) ||
        !preg_match('/^[a-z0-9_-]{1,100}$/D', $status)
    ) {
        throw new RuntimeException('Invalid GHN polling data.');
    }

    // Đây là thời điểm tra cứu, không phải thời điểm GHN
    // thực sự chuyển trạng thái vận đơn.
    $observedAt = (new DateTimeImmutable(
        'now',
        new DateTimeZone('UTC')
    ))->format('Y-m-d H:i:s.u');

    $eventKey = hash(
        'sha256',
        $code . '|poll|' . $observedAt . '|' .
        bin2hex(random_bytes(8))
    );

    return $this->record(
        $code,
        'poll',
        $status,
        $observedAt,
        $eventKey,
        'poll',
        'GNT-' . $requestId,
        true,
        $requestId
    ) === 'applied';
}


    
private function record(
    string $code,
    string $type,
    string $status,
    string $eventTime,
    string $eventKey,
    string $source,
    string $clientCode,
    bool $mayUpdateStatus,
    ?int $expectedRequestId = null
): string {
    $this->db->beginTransaction();

    try {
        // Khóa vận đơn để tránh hai yêu cầu đồng thời
        // cập nhật cùng một trạng thái.
        $stmt = $this->db->prepare(
            'SELECT d.request_id, d.ghn_status
             FROM delivery_details d
             JOIN item_requests ir ON ir.id = d.request_id
             WHERE d.tracking_number = :code
               AND ir.delivery_method = \'shipping\'
             FOR UPDATE'
        );

        $stmt->execute([':code' => $code]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            $retry = false;

            if (
                $source === 'webhook' &&
                preg_match(
                    '/^GNT-([1-9][0-9]*)$/D',
                    $clientCode,
                    $matches
                )
            ) {
                $pending = $this->db->prepare(
                    'SELECT 1
                     FROM item_requests ir
                     LEFT JOIN delivery_details d
                       ON d.request_id = ir.id
                     WHERE ir.id = :id
                       AND ir.delivery_method = \'shipping\'
                       AND d.tracking_number IS NULL
                     LIMIT 1'
                );

                $pending->execute([
                    ':id' => (int) $matches[1]
                ]);

                $retry = (bool) $pending->fetchColumn();
            }

            $this->db->rollBack();

            return $retry ? 'retry' : 'ignored';
        }

        $requestId = (int) $row['request_id'];
        $currentStatus = strtolower(
            trim((string) ($row['ghn_status'] ?? ''))
        );

        if (
            ($expectedRequestId !== null &&
                $requestId !== $expectedRequestId) ||
            ($clientCode !== '' &&
                $clientCode !== 'GNT-' . $requestId)
        ) {
            $this->db->rollBack();
            return 'ignored';
        }

        // Không sinh thêm sự kiện polling nếu trạng thái
        // chưa thay đổi so với trạng thái đã áp dụng.
        if ($source === 'poll' && $currentStatus === $status) {
            $this->db->commit();
            return 'duplicate';
        }

        // Tránh ghi lặp cùng trạng thái polling
        // khi webhook đang là nguồn trạng thái chính.
        if ($source === 'poll') {
            $lastPoll = $this->db->prepare(
                'SELECT ghn_status
                 FROM ghn_order_events
                 WHERE request_id = :id
                   AND source = \'poll\'
                 ORDER BY id DESC
                 LIMIT 1'
            );

            $lastPoll->execute([':id' => $requestId]);

            if ($lastPoll->fetchColumn() === $status) {
                $this->db->commit();
                return 'duplicate';
            }
        }

        $insert = $this->db->prepare(
            'INSERT IGNORE INTO ghn_order_events
             (request_id, order_code, event_key,
              event_type, ghn_status, event_time,
              source, applied)
             VALUES
             (:request_id, :order_code, :event_key,
              :event_type, :ghn_status, :event_time,
              :source, 0)'
        );

        $insert->execute([
            ':request_id' => $requestId,
            ':order_code' => $code,
            ':event_key' => $eventKey,
            ':event_type' => $type,
            ':ghn_status' => $status,
            ':event_time' => $eventTime,
            ':source' => $source
        ]);

        if ($insert->rowCount() === 0) {
            $this->db->commit();
            return 'duplicate';
        }

        $shouldApply = $mayUpdateStatus;

        if ($shouldApply) {
            // Chỉ xét các sự kiện đã thực sự cập nhật
            // trạng thái đơn hàng.
            $latest = $this->db->prepare(
                'SELECT event_time, source
                 FROM ghn_order_events
                 WHERE request_id = :id
                   AND applied = 1
                   AND event_key <> :event_key
                 ORDER BY event_time DESC, id DESC
                 LIMIT 1'
            );

            $latest->execute([
                ':id' => $requestId,
                ':event_key' => $eventKey
            ]);

            $lastApplied = $latest->fetch(PDO::FETCH_ASSOC);

            if ($source === 'poll') {
                // Khi đã có webhook được áp dụng,
                // không lấy thời điểm polling hiện tại
                // làm bằng chứng trạng thái mới hơn.
                $webhookCheck = $this->db->prepare(
                    'SELECT 1
                     FROM ghn_order_events
                     WHERE request_id = :id
                       AND source = \'webhook\'
                       AND applied = 1
                     LIMIT 1'
                );

                $webhookCheck->execute([
                    ':id' => $requestId
                ]);

                if ($webhookCheck->fetchColumn()) {
                    $shouldApply = false;
                }
            }

            // Một sự kiện cũ hoặc cùng thời điểm không
            // được ghi đè sự kiện đã áp dụng.
            if (
                $shouldApply &&
                $lastApplied &&
                strcmp(
                    $eventTime,
                    (string) $lastApplied['event_time']
                ) <= 0
            ) {
                $shouldApply = false;
            }
        }

        if ($shouldApply) {
            $update = $this->db->prepare(
                'UPDATE delivery_details
                 SET ghn_status = :status
                 WHERE request_id = :id
                   AND tracking_number = :code'
            );

            $update->execute([
                ':status' => $status,
                ':id' => $requestId,
                ':code' => $code
            ]);

            $mark = $this->db->prepare(
                'UPDATE ghn_order_events
                 SET applied = 1
                 WHERE event_key = :key'
            );

            $mark->execute([
                ':key' => $eventKey
            ]);
        }

        $this->db->commit();

        return $shouldApply ? 'applied' : 'recorded';

    } catch (Throwable $e) {
        if ($this->db->inTransaction()) {
            $this->db->rollBack();
        }

        throw $e;
    }
}

}
