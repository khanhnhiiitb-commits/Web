<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class MessageModel
{
    private PDO $db;
    private const OPEN_STATUSES = ['pending', 'accepted', 'arranged', 'in_progress', 'awaiting_confirmation'];

    public function __construct() { $this->db = Database::getConnection(); }

    public function getConversation(int $requestId, int $userId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT ir.id AS request_id, ir.status, ir.requester_id, i.user_id AS giver_id,
                    i.title AS item_title, giver.full_name AS giver_name,
                    receiver.full_name AS receiver_name
             FROM item_requests ir
             JOIN items i ON i.id = ir.item_id
             JOIN users giver ON giver.id = i.user_id
             JOIN users receiver ON receiver.id = ir.requester_id
             WHERE ir.id = :request_id AND (ir.requester_id = :requester_id OR i.user_id = :giver_id)"
        );
        $stmt->execute(['request_id' => $requestId, 'requester_id' => $userId, 'giver_id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function isWritable(string $status): bool
    {
        return in_array($status, self::OPEN_STATUSES, true);
    }

    public function getMessages(int $requestId, int $afterId = 0): array
    {
        $stmt = $this->db->prepare(
            "SELECT m.id, m.sender_id, u.full_name AS sender_name, m.content, m.created_at
             FROM messages m JOIN users u ON u.id = m.sender_id
             WHERE m.request_id = :request_id AND m.id > :after_id
             ORDER BY m.id ASC LIMIT 200"
        );
        $stmt->execute(['request_id' => $requestId, 'after_id' => $afterId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function markRead(int $requestId, int $userId): void
    {
        $stmt = $this->db->prepare(
            "UPDATE messages SET is_read = 1 WHERE request_id = :request_id AND sender_id <> :user_id AND is_read = 0"
        );
        $stmt->execute(['request_id' => $requestId, 'user_id' => $userId]);
    }

    public function sendMessage(int $requestId, int $userId, string $content): bool
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                "SELECT ir.status, ir.requester_id, i.user_id AS giver_id
                 FROM item_requests ir JOIN items i ON i.id = ir.item_id
                 WHERE ir.id = :request_id FOR UPDATE"
            );
            $stmt->execute(['request_id' => $requestId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row || !in_array($userId, [(int)$row['requester_id'], (int)$row['giver_id']], true)
                || !$this->isWritable($row['status'])) {
                $this->db->rollBack();
                return false;
            }
            $stmt = $this->db->prepare(
                "INSERT INTO messages (request_id, sender_id, content) VALUES (:request_id, :sender_id, :content)"
            );
            $stmt->execute(['request_id' => $requestId, 'sender_id' => $userId, 'content' => $content]);
            $this->db->commit();
            return true;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            error_log('[MessageModel] ' . $e->getMessage());
            throw $e;
        }
    }
}
