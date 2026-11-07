<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\ItemModel;
use App\Models\CategoryModel;

class ItemController extends Controller
{
    private ItemModel $itemModel;
    private CategoryModel $categoryModel;

    public function __construct()
    {
        $this->itemModel = $this->model('ItemModel');
        $this->categoryModel = $this->model('CategoryModel');
    }

    // Form đăng món đồ
    public function create(): void
    {
        $this->requireLogin();

        $categories = $this->categoryModel->getAllCategories();

        $this->view('items/post_item', [
            'categories' => $categories
        ]);
    }

    // Xử lý đăng món đồ
    public function store(): void
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=Item&action=create');
            exit;
        }

        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $condition = trim($_POST['condition_note'] ?? '');
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $location = trim($_POST['pickup_location'] ?? '');

        if (
            $title === '' ||
            $description === '' ||
            $condition === '' ||
            $categoryId <= 0 ||
            $location === ''
        ) {
            $_SESSION['error'] = 'Vui lòng nhập đầy đủ thông tin món đồ.';
            header('Location: index.php?controller=Item&action=create');
            exit;
        }

        // Kiểm tra ảnh
        if (
            !isset($_FILES['image']) ||
            $_FILES['image']['error'] !== UPLOAD_ERR_OK
        ) {
            $_SESSION['error'] = 'Vui lòng chọn ảnh hợp lệ.';
            header('Location: index.php?controller=Item&action=create');
            exit;
        }

        $file = $_FILES['image'];
        $maxSize = 5 * 1024 * 1024;

        if ($file['size'] > $maxSize) {
            $_SESSION['error'] = 'Ảnh không được vượt quá 5MB.';
            header('Location: index.php?controller=Item&action=create');
            exit;
        }

        $imageInfo = getimagesize($file['tmp_name']);

        if ($imageInfo === false) {
            $_SESSION['error'] = 'File tải lên không phải ảnh hợp lệ.';
            header('Location: index.php?controller=Item&action=create');
            exit;
        }

        $allowedTypes = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp'
        ];

        $mimeType = $imageInfo['mime'];

        if (!isset($allowedTypes[$mimeType])) {
            $_SESSION['error'] = 'Chỉ chấp nhận ảnh JPG, PNG hoặc WEBP.';
            header('Location: index.php?controller=Item&action=create');
            exit;
        }

        $extension = $allowedTypes[$mimeType];

        // Thư mục lưu ảnh: Web/public/uploads/items
        $uploadDir = dirname(__DIR__, 2)
                   . '/public/uploads/items/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileName = bin2hex(random_bytes(16)) . '.' . $extension;
        $destination = $uploadDir . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            $_SESSION['error'] = 'Không thể lưu ảnh lên máy chủ.';
            header('Location: index.php?controller=Item&action=create');
            exit;
        }

        $imageUrl = 'public/uploads/items/' . $fileName;

        try {
            $this->itemModel->insertItem([
                'user_id'         => (int)$_SESSION['user_id'],
                'category_id'     => $categoryId,
                'title'           => $title,
                'description'     => $description,
                'condition_note'  => $condition,
                'image_url'       => $imageUrl,
                'pickup_location' => $location
            ]);

            $_SESSION['success'] = 'Đăng món đồ thành công!';
            header('Location: index.php?controller=Item&action=my_items');
            exit;

        } catch (\Throwable $e) {
            // Nếu lưu database thất bại, xóa ảnh vừa tải lên
            if (file_exists($destination)) {
                unlink($destination);
            }

            $_SESSION['error'] = 'Có lỗi xảy ra khi đăng món đồ.';
            header('Location: index.php?controller=Item&action=create');
            exit;
        }
    }

    // Danh sách món đồ của tôi
    public function my_items(): void
    {
        $this->requireLogin();

        $items = $this->itemModel->getMyItems(
            (int)$_SESSION['user_id']
        );

        $this->view('items/my_items', [
            'items' => $items
        ]);
    }

    // Chi tiết món đồ
    public function detail(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $item = $this->itemModel->getItemById($id);

        if (!$item) {
            http_response_code(404);
            echo 'Không tìm thấy món đồ.';
            return;
        }

        $this->view('items/item_detail', [
            'item' => $item
        ]);
    }
}