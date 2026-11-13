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
        // Địa chỉ gửi GHN của món đồ
        $ghnProvinceId = (int) ($_POST['ghn_province_id'] ?? 0);
        $ghnDistrictId = (int) ($_POST['ghn_district_id'] ?? 0);
        $ghnWardCode = trim((string) ($_POST['ghn_ward_code'] ?? ''));
        $senderAddress = trim((string) ($_POST['sender_address_detail'] ?? ''));
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
        // Kiểm tra địa chỉ gửi GHN
        if (
            $ghnProvinceId <= 0 ||
            $ghnDistrictId <= 0 ||
            $ghnWardCode === '' ||
            $senderAddress === '' ||
            mb_strlen($ghnWardCode) > 20 ||
            mb_strlen($senderAddress) > 255
        ) {
    $_SESSION['error'] = 'Vui lòng nhập đầy đủ địa chỉ gửi GHN.';

    header('Location: index.php?controller=Item&action=create');
    exit;
}

        // Kiểm tra ảnh
        if ( !isset($_FILES['image']) ||  $_FILES['image']['error'] !== UPLOAD_ERR_OK ) {
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
            'image/webp' => 'webp'  ];
        $mimeType = $imageInfo['mime'];
        if (!isset($allowedTypes[$mimeType])) {
            $_SESSION['error'] = 'Chỉ chấp nhận ảnh JPG, PNG hoặc WEBP.';
            header('Location: index.php?controller=Item&action=create');
            exit;
        }

        $extension = $allowedTypes[$mimeType];
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
            'user_id'               => (int) $_SESSION['user_id'],
            'category_id'           => $categoryId,
            'title'                 => $title,
            'description'           => $description,
            'condition_note'        => $condition,
            'image_url'             => $imageUrl,
            'pickup_location'       => $location,

            // Thông tin địa chỉ gửi GHN
            'ghn_province_id'       => $ghnProvinceId,
            'ghn_district_id'       => $ghnDistrictId,
            'ghn_ward_code'         => $ghnWardCode,
            'sender_address_detail' => $senderAddress
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
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if (!$id || $id <= 0) {
        http_response_code(404);
        echo 'Món đồ không hợp lệ.';
        return;
    }

    $item = $this->itemModel->getItemById($id);

    if (!$item) {
        http_response_code(404);
        echo 'Không tìm thấy món đồ.';
        return;
    }

    $userId = (int)($_SESSION['user_id'] ?? 0);
    $isOwner = $userId > 0
        && $userId === (int)$item['user_id'];

    $isAdmin = ($_SESSION['role'] ?? '') === 'admin';
    $status = $item['status'] ?? '';

    if (
        !in_array($status, ['available', 'gifted'], true)
        && !$isOwner
        && !$isAdmin
    ) {
        http_response_code(404);
        echo 'Không tìm thấy món đồ.';
        return;
    }

    $canReceive = $status === 'available'
        && $userId > 0
        && !$isOwner
        && !$isAdmin;

    $this->view('items/item_detail', [
        'item' => $item,
        'isOwner' => $isOwner,
        'canReceive' => $canReceive
    ]);
}
// Xử lý đăng ký nhận đồ từ trang chi tiết
    public function requestItem(): void
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php');
            exit;
        }

        $itemId = (int)($_POST['item_id'] ?? 0);
        $message = trim($_POST['message'] ?? '');
        $userId = (int)($_SESSION['user_id'] ?? $_SESSION['user']['id'] ?? 0);

        if ($itemId <= 0 || $userId <= 0) {
            $_SESSION['error'] = 'Yêu cầu không hợp lệ.';
            header('Location: index.php');
            exit;
        }

        // Kiểm tra món đồ có tồn tại và đang available không
        $item = $this->itemModel->getItemById($itemId);
        if (!$item || $item['status'] !== 'available') {
            $_SESSION['error'] = 'Món đồ này hiện không còn khả dụng để nhận.';
            header('Location: index.php?controller=Item&action=detail&id=' . $itemId);
            exit;
        }

        // Không cho phép chủ nhân tự đăng ký nhận đồ của mình
        if ((int)$item['user_id'] === $userId) {
            $_SESSION['error'] = 'Bạn không thể đăng ký nhận món đồ do chính mình đăng tải.';
            header('Location: index.php?controller=Item&action=detail&id=' . $itemId);
            exit;
        }

        // Gọi RequestModel để tạo bản ghi yêu cầu và cập nhật trạng thái món đồ
        try {
            require_once __DIR__ . '/../Models/RequestModel.php';
            $requestModel = new \App\Models\RequestModel();

            // Tạo yêu cầu với trạng thái chờ xử lý (pending)
            $success = $requestModel->createRequest($itemId, $userId, $message);

            if ($success) {
             $_SESSION['success'] =
                 'Đăng ký nhận đồ thành công! Vui lòng chờ chủ đồ phản hồi.';

                 header('Location: index.php?controller=Request&action=myRequests');
                exit;
            } else {
                $_SESSION['error'] = 'Không thể gửi yêu cầu. Vui lòng thử lại sau.';
                header('Location: index.php?controller=Item&action=detail&id=' . $itemId);
                exit;
            }
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'Có lỗi xảy ra: ' . $e->getMessage();
            header('Location: index.php?controller=Item&action=detail&id=' . $itemId);
            exit;
        }
    }
}