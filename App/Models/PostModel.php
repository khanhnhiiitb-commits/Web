<?php
namespace App\Models;
use App\Core\Model;

class PostModel extends Model {
    public function getPublishedPosts() {
        $sql = "SELECT posts.*, users.full_name 
                FROM posts 
                JOIN users ON posts.user_id = users.id 
                WHERE posts.status = 'published' 
                ORDER BY posts.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}