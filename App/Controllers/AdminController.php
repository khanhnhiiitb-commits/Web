<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class AdminController extends Controller
{
    // Hiển thị trang quản trị
    public function dashboard()
    {
        // Kiểm tra người dùng đã đăng nhập và có quyền Admin
        if (
            !isset($_SESSION['user_id']) ||
            !isset($_SESSION['role']) ||
            $_SESSION['role'] !== 'admin'
        ) {
            header('Location: index.php?controller=auth&action=login');
            exit;
        }

        // Kết nối database
        $db = Database::getConnection();

        // Đếm tổng số người dùng
        $stmt = $db->query("SELECT COUNT(*) FROM users");
        $totalUsers = (int) $stmt->fetchColumn();

        // Đếm tổng số bài đăng
        $stmt = $db->query("SELECT COUNT(*) FROM posts");
        $totalPosts = (int) $stmt->fetchColumn();

        // Đếm tổng số yêu cầu
        $stmt = $db->query("SELECT COUNT(*) FROM item_requests");
        $totalRequests = (int) $stmt->fetchColumn();

        // Đếm số yêu cầu đang chờ xử lý
        $stmt = $db->prepare(
            "SELECT COUNT(*) FROM item_requests
             WHERE status = :status"
        );

        $stmt->execute([
            'status' => 'pending'
        ]);

        $pendingRequests = (int) $stmt->fetchColumn();

        // Lấy 5 người dùng gần đây
        $stmt = $db->query(
            "SELECT id, full_name, email, role, status
             FROM users
             ORDER BY id DESC
             LIMIT 5"
        );

        $recentUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Lấy 5 yêu cầu gần đây
        $stmt = $db->query(
            "SELECT item_requests.id,
            item_requests.status,
            users.full_name
            FROM item_requests
            LEFT JOIN users
            ON item_requests.requester_id = users.id
            ORDER BY item_requests.id DESC
            LIMIT 5"
       );

        $recentRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Hiển thị giao diện dashboard
        require __DIR__ . '/../../views/admin/dashboard.php';
    }
    
    // Hiển thị danh sách người dùng
    public function users()
    {
        if (
            !isset($_SESSION['user_id']) ||
            !isset($_SESSION['role']) ||
            $_SESSION['role'] !== 'admin'
        ) {
            header('Location: index.php');
            exit;
        }

        $userModel = new \App\Models\UserModel();
        $users = $userModel->getAllUsers();

        require __DIR__ . '/../../views/admin/manage_users.php';
    }

    // Khóa hoặc mở khóa tài khoản
    public function updateUserStatus()
    {
        if (
            !isset($_SESSION['user_id']) ||
            !isset($_SESSION['role']) ||
            $_SESSION['role'] !== 'admin'
        ) {
            header('Location: index.php');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=admin&action=users');
            exit;
        }

        $id = (int) ($_POST['id'] ?? 0);
        $status = (int) ($_POST['status'] ?? -1);

        // Chỉ chấp nhận trạng thái 0 hoặc 1
        if ($id <= 0 || !in_array($status, [0, 1], true)) {
            header('Location: index.php?controller=admin&action=users');
            exit;
        }

        // Không cho admin tự khóa tài khoản của mình
        if ($id === (int) $_SESSION['user_id']) {
            header('Location: index.php?controller=admin&action=users');
            exit;
        }

        $userModel = new \App\Models\UserModel();
        $userModel->updateStatus($id, $status);

        header('Location: index.php?controller=admin&action=users');
        exit;
    }

}
