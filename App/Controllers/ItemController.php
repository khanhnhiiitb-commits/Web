<?php
namespace App\Controllers;

use App\Core\Controller;

class ItemController extends Controller {
    
    // Yêu cầu đăng nhập trước khi thực hiện các thao tác
    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('auth/login');
        }
    }

    // Hiển thị form đăng đồ
    public function create() {
        $categoryModel = $this->model('CategoryModel');
        $categories = $categoryModel->getAllCategories();
        
        $this->view('items/create', [
            'categories' => $categories
        ]);
    }

    // Xử lý dữ liệu form gửi lên (POST)
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $title = trim($_POST['title']);
            $categoryId = $_POST['category_id'];
            $conditionNote = trim($_POST['condition_note']);
            $pickupLocation = trim($_POST['pickup_location']);
            $description = trim($_POST['description']);
            $userId = $_SESSION['user_id'];

            // Xử lý Upload file ảnh
            $imageUrl = 'default.png'; // Ảnh mặc định nếu lỗi
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
                $fileName = $_FILES['image']['name'];
                $fileSize = $_FILES['image']['size'];
                $fileTmp = $_FILES['image']['tmp_name'];
                
                // Lấy đuôi file
                $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                // Kiểm tra định dạng và dung lượng (< 2MB)
                if (in_array($fileExt, $allowedExts) && $fileSize < 2000000) {
                    // Đổi tên file bằng uniqid() để không bị đè file cũ
                    $newFileName = uniqid() . '.' . $fileExt;
                    $uploadPath = __DIR__ . '/../../public/uploads/items/' . $newFileName;
                    
                    if (move_uploaded_file($fileTmp, $uploadPath)) {
                        $imageUrl = $newFileName;
                    }
                }
            }

            // Gọi Model để lưu vào DB
            $itemModel = $this->model('ItemModel');
            $itemModel->insertItem($userId, $categoryId, $title, $description, $conditionNote, $pickupLocation, $imageUrl);

            // Lưu thành công, chuyển hướng về kho đồ của tôi
            $this->redirect('item/my_items');
        }
    }

    // Trang hiển thị danh sách đồ của cá nhân
    public function my_items() {
        $itemModel = $this->model('ItemModel');
        $items = $itemModel->getMyItems($_SESSION['user_id']);
        
        $this->view('items/my_items', [
            'items' => $items
        ]);
    }
}