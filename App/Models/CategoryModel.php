<?php
namespace App\Models;

use App\Core\Model;

class CategoryModel extends Model {
    public function getAllCategories() {
        $sql = "SELECT * FROM categories ORDER BY name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}