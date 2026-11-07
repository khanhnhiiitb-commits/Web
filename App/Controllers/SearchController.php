<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\ItemModel;
use App\Models\CategoryModel;

class SearchController extends Controller
{
    public function index(): void
    {
        $itemModel = $this->model('ItemModel');
        $categoryModel = $this->model('CategoryModel');

        $keyword = trim($_GET['keyword'] ?? '');
        $categoryId = (int)($_GET['category_id'] ?? 0);
        $location = trim($_GET['location'] ?? '');

        $items = $itemModel->searchItems(
            $keyword,
            $categoryId > 0 ? $categoryId : null,
            $location
        );

        $categories = $categoryModel->getAllCategories();

        $this->view('search/index', [
            'items' => $items,
            'categories' => $categories,
            'keyword' => $keyword,
            'selectedCategory' => $categoryId,
            'location' => $location
        ]);
    }
}