<?php
namespace App\Models;

use App\Core\Model;
use PDO;

class PostModel extends Model
{
    protected string $table = 'posts';

    public function getPublishedPosts(): array
    {
        $stmt = $this->db->query(
            "SELECT p.*, u.full_name AS author_name,
                    gttl_count_post_comments(p.id) AS comment_count
             FROM posts p JOIN users u ON u.id = p.user_id
             WHERE p.status = 'published' ORDER BY p.created_at DESC, p.id DESC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllPosts(): array
    {
        return $this->getPublishedPosts();
    }

    public function createPost(int $userId, string $title, string $content, ?string $thumbnail = null): bool
    {
        $stmt = $this->db->prepare('CALL gttl_create_post(?, ?, ?, ?)');
        try {
            return $stmt->execute([$userId, $title, $content, $thumbnail]);
        } finally {
            $stmt->closeCursor();
        }
    }

    public function getComments(int $postId): array
    {
        $stmt = $this->db->prepare(
            "SELECT pc.*, u.full_name AS commenter_name
             FROM post_comments pc JOIN users u ON u.id=pc.user_id
             WHERE pc.post_id=? ORDER BY pc.created_at ASC, pc.id ASC"
        );
        $stmt->execute([$postId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addComment(int $postId, int $userId, string $comment): bool
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("SELECT id FROM posts WHERE id=? AND status='published' FOR UPDATE");
            $stmt->execute([$postId]);
            $postExists = (bool)$stmt->fetchColumn();
            $stmt->closeCursor();
            if (!$postExists) {
                $this->db->rollBack();
                return false;
            }
            $stmt = $this->db->prepare('CALL gttl_add_post_comment(?, ?, ?)');
            try {
                $ok = $stmt->execute([$postId, $userId, $comment]);
            } finally {
                $stmt->closeCursor();
            }
            if (!$ok) {
                $this->db->rollBack();
                return false;
            }
            $this->db->commit();
            return true;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }
}
