<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class ItemModel extends Model
{
    // Thêm món đồ mới
    public function insertItem(array $data): bool
    {
        $sql = "INSERT INTO items
                (user_id, category_id, title, description,
                 condition_note, image_url, pickup_location, status)
                VALUES
                (:user_id, :category_id, :title, :description,
                 :condition_note, :image_url, :pickup_location, 'available')";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':user_id'        => $data['user_id'],
            ':category_id'    => $data['category_id'],
            ':title'          => $data['title'],
            ':description'    => $data['description'],
            ':condition_note' => $data['condition_note'],
            ':image_url'      => $data['image_url'],
            ':pickup_location'=> $data['pickup_location']
        ]);
    }

    // Lấy danh sách món đồ đang chia sẻ
    public function getAvailableItems(): array
    {
        $sql = "SELECT
                    i.*,
                    c.name AS category_name,
                    u.full_name AS owner_name
                FROM items i
                INNER JOIN categories c ON i.category_id = c.id
                INNER JOIN users u ON i.user_id = u.id
                WHERE i.status = 'available'
                ORDER BY i.created_at DESC";

        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // Tìm kiếm và lọc món đồ
    public function searchItems(
        string $keyword = '',
        ?int $categoryId = null,
        string $location = ''
    ): array {
        $sql = "SELECT
                    i.*,
                    c.name AS category_name,
                    u.full_name AS owner_name
                FROM items i
                INNER JOIN categories c ON i.category_id = c.id
                INNER JOIN users u ON i.user_id = u.id
                WHERE i.status = 'available'";

        $params = [];

        if ($keyword !== '') {
            $sql .= " AND (i.title LIKE :keyword
                       OR i.description LIKE :keyword
                       OR i.condition_note LIKE :keyword)";
            $params[':keyword'] = '%' . $keyword . '%';
        }

        if ($categoryId !== null && $categoryId > 0) {
            $sql .= " AND i.category_id = :category_id";
            $params[':category_id'] = $categoryId;
        }

        if ($location !== '') {
            $sql .= " AND i.pickup_location LIKE :location";
            $params[':location'] = '%' . $location . '%';
        }

        $sql .= " ORDER BY i.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lấy chi tiết một món đồ
    public function getItemById(int $id): ?array
    {
        $sql = "SELECT
                    i.*,
                    c.name AS category_name,
                    u.full_name AS owner_name
                FROM items i
                INNER JOIN categories c ON i.category_id = c.id
                INNER JOIN users u ON i.user_id = u.id
                WHERE i.id = :id AND i.status = 'available'
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);

        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        return $item ?: null;
    }

    // Lấy danh sách món đồ của người đang đăng nhập
    public function getMyItems(int $userId): array
    {
        $sql = "SELECT i.*, c.name AS category_name
                FROM items i
                INNER JOIN categories c ON i.category_id = c.id
                WHERE i.user_id = :user_id
                ORDER BY i.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}