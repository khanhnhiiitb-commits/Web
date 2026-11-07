<?php

namespace App\Models;

use App\Core\Model;

class CategoryModel extends Model
{
    public function getAllCategories(): array
    {
        $sql = "SELECT id, name, slug, description
                FROM categories
                ORDER BY name ASC";

        return $this->db->query($sql)->fetchAll();
    }
}