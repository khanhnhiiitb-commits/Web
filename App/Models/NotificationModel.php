<?php
namespace App\Models;
use App\Core\Model;

class NotificationModel extends Model
{
    protected string $table = 'notifications';

    public function getForUser(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT id, title, message, is_read, created_at FROM notifications WHERE user_id=? ORDER BY created_at DESC, id DESC LIMIT 100');
        $stmt->execute([$userId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function countUnread(int $userId): int
    {
        $stmt = $this->db->prepare('SELECT nttd_fn_count_unread_notifications(?)');
        try {
            $stmt->execute([$userId]);
            return (int)$stmt->fetchColumn();
        } finally {
            $stmt->closeCursor();
        }
    }
}
