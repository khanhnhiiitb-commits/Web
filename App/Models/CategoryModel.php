<?php

namespace App\Models;

use App\Core\Model;

class CategoryModel extends Model
{
    public function getAllCategories(): array
    {
        $sql = "SELECT id, name, slug, description,
                       tkn_count_items_by_category(id) AS available_count
                FROM categories
                ORDER BY name ASC";

        return $this->db->query($sql)->fetchAll();
    }
}