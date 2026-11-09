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

    // Tìm kiếm từ khóa
    if ($keyword !== '') {
        $sql .= " AND (
            i.title LIKE :keyword_title
            OR i.description LIKE :keyword_description
            OR i.condition_note LIKE :keyword_condition
        )";

        $searchKeyword = '%' . $keyword . '%';

        $params[':keyword_title'] = $searchKeyword;
        $params[':keyword_description'] = $searchKeyword;
        $params[':keyword_condition'] = $searchKeyword;
    }

    // Lọc danh mục
    if ($categoryId !== null && $categoryId > 0) {
        $sql .= " AND i.category_id = :category_id";
        $params[':category_id'] = $categoryId;
    }

    // Lọc địa điểm
    if ($location !== '') {
        $sql .= " AND i.pickup_location LIKE :location";
        $params[':location'] = '%' . $location . '%';
    }

    $sql .= " ORDER BY i.created_at DESC";

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
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
            WHERE i.id = :id
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
    /**
 * Tạo điều kiện tìm kiếm dùng chung cho truy vấn dữ liệu và đếm số dòng.
 */
private function buildItemConditions(
    string $keyword = '',
    ?int $categoryId = null,
    string $location = ''
): array {
    $where = " WHERE i.status = 'available'";
    $params = [];

    if ($keyword !== '') {
        $where .= " AND (
            i.title LIKE :keyword_title
            OR i.description LIKE :keyword_description
            OR i.condition_note LIKE :keyword_condition
        )";

        $searchKeyword = '%' . $keyword . '%';

        $params[':keyword_title'] = $searchKeyword;
        $params[':keyword_description'] = $searchKeyword;
        $params[':keyword_condition'] = $searchKeyword;
    }

    if ($categoryId !== null && $categoryId > 0) {
        $where .= " AND i.category_id = :category_id";
        $params[':category_id'] = $categoryId;
    }

    if ($location !== '') {
        $where .= " AND i.pickup_location LIKE :location";
        $params[':location'] = '%' . $location . '%';
    }

    return [$where, $params];
}

/**
 * Lấy danh sách sản phẩm có phân trang.
 */
public function getItemsPaginated(
    string $keyword = '',
    ?int $categoryId = null,
    string $location = '',
    int $page = 1,
    int $perPage = 8
): array {
    $page = max(1, $page);
    $perPage = max(1, $perPage);
    $offset = ($page - 1) * $perPage;

    [$where, $params] = $this->buildItemConditions(
        $keyword,
        $categoryId,
        $location
    );

    // Đếm tổng số sản phẩm phù hợp.
    $countSql = "SELECT COUNT(*)
                 FROM items i
                 $where";

    $countStmt = $this->db->prepare($countSql);
    $countStmt->execute($params);
    $totalItems = (int) $countStmt->fetchColumn();

    // Lấy sản phẩm của trang hiện tại.
    $sql = "SELECT
                i.*,
                c.name AS category_name,
                u.full_name AS owner_name
            FROM items i
            INNER JOIN categories c ON i.category_id = c.id
            INNER JOIN users u ON i.user_id = u.id
            $where
            ORDER BY i.created_at DESC
            LIMIT :limit OFFSET :offset";

    $stmt = $this->db->prepare($sql);

    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }

    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

    $stmt->execute();

    return [
        'items' => $stmt->fetchAll(PDO::FETCH_ASSOC),
        'total' => $totalItems
    ];
}
public function updateStatus(int $itemId, string $status): bool
    {
        $sql = "UPDATE items SET status = :status WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':status' => $status,
            ':id' => $itemId
        ]);
    }
}