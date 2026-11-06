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

    // Trang chủ
    public function index()
    {
        $keyword = trim($_GET['keyword'] ?? '');
        $categoryId = $_GET['category_id'] ?? '';

        // Lấy sản phẩm
        if ($keyword !== '' || $categoryId !== '') {
            $items = $this->itemModel->searchItems(
                $keyword,
                $categoryId
            );
        } else {
            $items = $this->itemModel->getAvailableItems();
        }

        // Lấy danh mục
        $categories = $this->itemModel->getCategories();

        // Truyền dữ liệu sang View
        $this->view('home/index', [
            'items' => $items,
            'categories' => $categories,
            'keyword' => $keyword,
            'categoryId' => $categoryId
        ]);
    }

    // Tìm kiếm AJAX
    public function searchAjax()
    {
        $keyword = trim($_GET['keyword'] ?? '');
        $categoryId = $_GET['category_id'] ?? '';

        $data = $this->itemModel->searchItems(
            $keyword,
            $categoryId
        );

        header('Content-Type: application/json; charset=utf-8');

        echo json_encode(
            $data,
            JSON_UNESCAPED_UNICODE
        );

        exit;
    }
}