<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\UserModel;
use PDO;

class AdminController extends Controller
{
    // Kiểm tra quyền Admin (Middleware)
    private function checkAdmin()
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }

        if (($_SESSION['role'] ?? '') !== 'admin') {
            header('Location: ' . BASE_URL . 'home');
            exit;
        }

        // Kiểm tra tài khoản có còn hoạt động không
        $userModel = new UserModel();
        $user = $userModel->findById($_SESSION['user_id']);

        if (!$user || (int) $user['status'] !== 1) {
            $_SESSION = [];
            session_destroy();

            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }
    }

    //Trang tổng quan (Dashboard)
    public function dashboard()
    {
        $this->checkAdmin();
        $db = Database::getConnection();

        // 1. Thống kê số lượng
        $totalUsers = (int) $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $totalPosts = (int) $db->query("SELECT COUNT(*) FROM posts")->fetchColumn();
        $totalRequests = (int) $db->query("SELECT COUNT(*) FROM item_requests")->fetchColumn();

        $stmt = $db->prepare("SELECT COUNT(*) FROM item_requests WHERE status = :status");
        $stmt->execute([':status' => 'pending']);
        $pendingRequests = (int) $stmt->fetchColumn();

        // 2. Danh sách rút gọn
        $recentUsers = $db->query("SELECT id, full_name, email, role, status FROM users ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

        $recentRequests = $db->query(
            "SELECT item_requests.id, item_requests.status, users.full_name 
             FROM item_requests 
             LEFT JOIN users ON item_requests.requester_id = users.id 
             ORDER BY item_requests.id DESC LIMIT 5"
        )->fetchAll(PDO::FETCH_ASSOC);

        // 3. Đóng gói Data
        $data = [
            'totalUsers' => $totalUsers,
            'totalPosts' => $totalPosts,
            'totalRequests' => $totalRequests,
            'pendingRequests' => $pendingRequests,
            'recentUsers' => $recentUsers,
            'recentRequests' => $recentRequests
        ];

        // 4. Render qua admin_layout
        $this->view('layouts/admin_layout', [
            'view' => 'admin/dashboard', 
            'data' => $data
        ]);
    }

    // Quản lý người dùng
    public function users()
    {
        $this->checkAdmin();
        $userModel = new UserModel();
        $users = $userModel->getAllUsers();

        // Render qua admin_layout
        $this->view('layouts/admin_layout', [
            'view' => 'admin/manage_users', 
            'data' => ['users' => $users]
        ]);
    }

    // Xử lý action khóa/mở khóa
    public function updateUserStatus()
    {
        $this->checkAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'admin/users');
            exit;
        }

        $id = (int) ($_POST['id'] ?? 0);
        $status = filter_var($_POST['status'] ?? null, FILTER_VALIDATE_INT);

        if ($id <= 0 || !in_array($status, [0, 1], true)) {
            header('Location: ' . BASE_URL . 'admin/users');
            exit;
        }

        // Không cho Admin tự khóa chính mình
        if ($id === (int) $_SESSION['user_id']) {
            header('Location: ' . BASE_URL . 'admin/users');
            exit;
        }

        $userModel = new UserModel();
        $userModel->updateStatus($id, $status);

        header('Location: ' . BASE_URL . 'admin/users');
        exit;
    }

    // Task 3: Quản lý bài đăng (Nếu bạn đã thiết lập)
    public function items()
    {
        $this->checkAdmin();
        $db = Database::getConnection();

        // Lấy danh sách bài đăng (tùy thuộc vào tên bảng của bạn, giả sử là 'posts')
        $items = $db->query("SELECT * FROM posts ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

        $this->view('layouts/admin_layout', [
            'view' => 'admin/manage_items', 
            'data' => ['items' => $items]
        ]);
    }

    // Quản lý giao dịch / requests (Nhiệm vụ bổ sung)
    public function transactions()
    {
        $this->checkAdmin();
        $db = Database::getConnection();

        $transactions = $db->query(
            "SELECT item_requests.*, users.full_name as requester_name 
             FROM item_requests 
             LEFT JOIN users ON item_requests.requester_id = users.id 
             ORDER BY item_requests.id DESC"
        )->fetchAll(PDO::FETCH_ASSOC);

        $this->view('layouts/admin_layout', [
            'view' => 'admin/manage_transactions', 
            'data' => ['transactions' => $transactions]
        ]);
    }
}