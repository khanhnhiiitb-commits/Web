
<?php
namespace App\Controllers;

use App\Core\Controller;

class ItemController extends Controller
{
    // Hiển thị chi tiết món đồ - KHÔNG cần đăng nhập
    public function detail()
    {
        $id = (int)($_GET['id'] ?? 0);

        if ($id <= 0) {
            echo "Không tìm thấy sản phẩm.";
            return;
        }

        $itemModel = $this->model('ItemModel');
        $item = $itemModel->getItemById($id);

        if (!$item) {
            echo "Sản phẩm không tồn tại hoặc không còn khả dụng.";
            return;
        }

        $this->view('items/item_detail', [
            'item' => $item
        ]);
    }

    // Hiển thị form đăng đồ - CẦN đăng nhập
    public function create()
    {
        $this->requireLogin();

        $categoryModel = $this->model('CategoryModel');
        $categories = $categoryModel->getAllCategories();

        $this->view('items/post_item', [
            'categories' => $categories
        ]);
    }

    // Xử lý đăng đồ - CẦN đăng nhập
    public function store()
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=Item&action=create');
            exit;
        }

        $title = trim($_POST['title'] ?? '');
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $conditionNote = trim($_POST['condition_note'] ?? '');
        $pickupLocation = trim($_POST['pickup_location'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $userId = $_SESSION['user_id'];

        if ($title === '' || $categoryId <= 0 ||
            $pickupLocation === '') {
            die('Vui lòng nhập đầy đủ thông tin bắt buộc.');
        }

        // Ảnh mặc định
        $imageUrl = 'default.png';

        // Xử lý upload ảnh
        if (isset($_FILES['image']) &&
            $_FILES['image']['error'] === UPLOAD_ERR_OK) {

            $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
            $fileTmp = $_FILES['image']['tmp_name'];
            $fileSize = $_FILES['image']['size'];

            // Kiểm tra file có thực sự là ảnh
            $imageInfo = getimagesize($fileTmp);

            $fileExt = strtolower(
                pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION)
            );

            if (
                $imageInfo !== false &&
                in_array($fileExt, $allowedExts, true) &&
                $fileSize < 2000000
            ) {
                $newFileName = uniqid('item_', true) . '.' . $fileExt;

                $uploadDir = __DIR__ . '/../../public/uploads/items/';

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                if (move_uploaded_file(
                    $fileTmp,
                    $uploadDir . $newFileName
                )) {
                    $imageUrl = $newFileName;
                }
            }
        }

        $itemModel = $this->model('ItemModel');

        $itemModel->insertItem(
            $userId,
            $categoryId,
            $title,
            $description,
            $conditionNote,
            $pickupLocation,
            $imageUrl
        );

        header('Location: index.php?controller=Item&action=my_items');
        exit;
    }

    // Danh sách đồ của tôi - CẦN đăng nhập
    public function my_items()
    {
        $this->requireLogin();

        $itemModel = $this->model('ItemModel');

        $items = $itemModel->getMyItems($_SESSION['user_id']);

        $this->view('items/my_items', [
            'items' => $items
        ]);
    }
}
