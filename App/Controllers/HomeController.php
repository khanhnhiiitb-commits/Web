<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\ItemModel;
use App\Models\CategoryModel;

class HomeController extends Controller {
    // Đổ dữ liệu ra trang chủ lúc tải trang
    public function index() {
        $itemModel = new ItemModel();
        $categoryModel = new CategoryModel();
        
        // Lấy danh sách đồ đang available
        $items = $itemModel->getAvailableItems();
        $categories = $categoryModel->getAllCategories();
        
        $this->view('home/index', [
            'items' => $items,
            'categories' => $categories
        ]);
    }

    // Xử lý bộ lọc AJAX, trả về JSON
    public function searchAjax() {
        $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
        $categoryId = isset($_GET['category_id']) ? $_GET['category_id'] : '';
        
        $itemModel = new ItemModel();
        $items = $itemModel->searchItems($keyword, $categoryId);
        
        // Trả về dữ liệu JSON
        header('Content-Type: application/json');
        echo json_encode($items);
        exit;
    }
}