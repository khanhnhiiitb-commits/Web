<?php
namespace App\Controllers;
use App\Core\Controller;

class HomeController extends Controller 
{
    private $itemModel;

    public function __construct() 
    {
        $this->itemModel = $this->model('ItemModel'); 
    }

    public function index() 
    {
        // Lấy danh sách đồ available từ ItemModel truyền ra view
        $data = $this->itemModel->getAvailableItems();
        $categories = $this->itemModel->getCategories(); // Giả sử có hàm lấy danh mục
        $this->view('home/index', [
            'items' => $data,
            'categories' => $categories
        ]);
    }

    public function searchAjax() 
    {
        $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
        $categoryId = isset($_GET['category_id']) ? trim($_GET['category_id']) : '';

        // Truy vấn Model
        $data = $this->itemModel->searchItems($keyword, $categoryId);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }
}