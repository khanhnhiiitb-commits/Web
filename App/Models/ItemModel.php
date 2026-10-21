<?php
namespace App\Models;

use App\Core\Model;

class ItemModel extends Model {
    public function insertItem($userId, $categoryId, $title, $description, $conditionNote, $pickupLocation, $imageUrl) {
        $sql = "INSERT INTO items (user_id, category_id, title, description, condition_note, pickup_location, image_url, status) 
                VALUES (:user_id, :category_id, :title, :description, :condition_note, :pickup_location, :image_url, 'available')";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'user_id' => $userId,
            'category_id' => $categoryId,
            'title' => $title,
            'description' => $description,
            'condition_note' => $conditionNote,
            'pickup_location' => $pickupLocation,
            'image_url' => $imageUrl
        ]);
    }

    // Hàm lấy danh sách đồ do chính mình đăng
    public function getMyItems($userId) {
        $sql = "SELECT items.*, categories.name as category_name 
                FROM items 
                JOIN categories ON items.category_id = categories.id 
                WHERE items.user_id = :user_id 
                ORDER BY items.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }
}