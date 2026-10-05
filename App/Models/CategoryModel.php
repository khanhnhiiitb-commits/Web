<?php
namespace App\Models;

use App\Core\Model;
use PDO;

class CategoryModel extends Model {
    protected string $table = 'categories';

    public function getAllCategories(): array {
        $sql = "SELECT * FROM {$this->table} ORDER BY id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Trả về mảng dữ liệu
    }
}