<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\RequestModel;

class RequestController extends Controller
{
    private RequestModel $requestModel;

    public function __construct()
    {
        // Khởi tạo RequestModel thông qua cơ chế của Core Controller hoặc trực tiếp
        $this->requestModel = new RequestModel();
    }

    // 1. Danh sách các món đồ mình đi xin (Trang quản lý đồ nhận)
    public function myRequests()
    {
        $userId = $this->getUserId();

        if (!$userId) {
            header("Location: index.php?controller=auth&action=login");
            exit();
        }

        $requests = $this->requestModel->getMyRequests($userId);

        // Gọi View hiển thị danh sách đồ mình đăng ký xin
        $this->view('requests/my_items', [
            'requests' => $requests
        ]);
    }

    // 2. Danh sách người xin những món đồ do mình đăng
    public function manageMyItems()
    {
        $userId = $this->getUserId();

        if (!$userId) {
            header("Location: index.php?controller=auth&action=login");
            exit();
        }

        $requests = $this->requestModel->getRequestsForMyItems($userId);

        // Gọi View hiển thị danh sách người xin đồ
        $this->view('requests/my_requests', [
            'requests' => $requests
        ]);
    }

    // 3. Xử lý đồng ý cho đồ
    public function accept()
    {
        $this->checkPost();

        $userId = $this->getUserId();
        $requestId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if (!$userId || $requestId <= 0) {
            die("Yêu cầu không hợp lệ.");
        }

        $success = $this->requestModel->acceptRequest($requestId, (int)$userId);

        $_SESSION['request_message'] = $success
            ? "Đã chấp nhận yêu cầu tặng đồ thành công!"
            : "Không thể chấp nhận yêu cầu. Vui lòng kiểm tra lại.";

        header("Location: index.php?controller=request&action=manageMyItems");
        exit();
    }

    // 4. Xử lý từ chối yêu cầu xin đồ
    public function reject()
    {
        $this->checkPost();

        $userId = $this->getUserId();
        $requestId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if (!$userId || $requestId <= 0) {
            die("Yêu cầu không hợp lệ.");
        }

        $success = $this->requestModel->rejectRequest($requestId, (int)$userId);

        $_SESSION['request_message'] = $success
            ? "Đã từ chối yêu cầu nhận đồ."
            : "Không thể từ chối yêu cầu này.";

        header("Location: index.php?controller=request&action=manageMyItems");
        exit();
    }

    // Lấy ID người dùng từ Session
    private function getUserId()
    {
        // Hỗ trợ cả 2 dạng lưu session phổ biến trong dự án của bạn
        if (isset($_SESSION['user_id'])) {
            return $_SESSION['user_id'];
        }
        if (isset($_SESSION['user']['id'])) {
            return $_SESSION['user']['id'];
        }
        return null;
    }

    // Kiểm tra phương thức POST bảo mật
    private function checkPost()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit("Phương thức không được hỗ trợ.");
        }
    }
}