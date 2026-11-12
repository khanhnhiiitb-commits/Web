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
    $keyword = trim($_GET['keyword'] ?? '');

    $categoryId = (
        isset($_GET['category_id']) &&
        $_GET['category_id'] !== ''
    ) ? (int) $_GET['category_id'] : null;

    $location = trim($_GET['location'] ?? '');

    $page = max(1, (int) ($_GET['page'] ?? 1));
    $perPage = 6;

    $result = $this->itemModel->getItemsPaginated(
        $keyword,
        $categoryId,
        $location,
        $page,
        $perPage
    );
    $totalItems = $result['total'];
    $totalPages = (int) ceil($totalItems / $perPage);
    if ($totalPages > 0 && $page > $totalPages) {
        $page = $totalPages;
        $result = $this->itemModel->getItemsPaginated(
            $keyword,
            $categoryId,
            $location,
            $page,
            $perPage
        );
    }

    $categoryModel = $this->model('CategoryModel');
    $categories = $categoryModel->getAllCategories();

    $this->view('home/index', [
        'items' => $result['items'],
        'categories' => $categories,
        'keyword' => $keyword,
        'categoryId' => $categoryId,
        'location' => $location,
        'page' => $page,
        'totalPages' => $totalPages,
        'totalItems' => $totalItems
    ]);
}

public function searchAjax()
{
    header('Content-Type: application/json; charset=utf-8');

    try {
        $keyword = trim($_GET['keyword'] ?? '');

        $categoryId = (
            isset($_GET['category_id']) &&
            $_GET['category_id'] !== ''
        ) ? (int) $_GET['category_id'] : null;

        $location = trim($_GET['location'] ?? '');

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 6;

        $result = $this->itemModel->getItemsPaginated(
            $keyword,
            $categoryId,
            $location,
            $page,
            $perPage
        );

        $totalItems = $result['total'];
        $totalPages = (int) ceil($totalItems / $perPage);

        if ($totalPages > 0 && $page > $totalPages) {
            $page = $totalPages;

            $result = $this->itemModel->getItemsPaginated(
                $keyword,
                $categoryId,
                $location,
                $page,
                $perPage
            );
        }

        $items = $result['items'];

        ob_start();
        require __DIR__ . '/../Views/home/_item_list.php';
        $html = ob_get_clean();

        ob_start();
        require __DIR__ . '/../Views/home/_pagination.php';
        $paginationHtml = ob_get_clean();

        echo json_encode([
            'success' => true,
            'html' => $html,
            'pagination' => $paginationHtml,
            'count' => $totalItems,
            'currentPage' => $page,
            'totalPages' => $totalPages
        ], JSON_UNESCAPED_UNICODE);

    } catch (\Throwable $e) {
        error_log('AJAX PAGINATION ERROR: ' . $e->getMessage());

        http_response_code(500);

        echo json_encode([
            'success' => false,
            'message' => 'Không thể tải danh sách sản phẩm.'
        ], JSON_UNESCAPED_UNICODE);
    }

    exit;
}
}