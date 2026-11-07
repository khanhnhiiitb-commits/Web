<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\UserModel;
use PDO;

class AdminController extends Controller
{
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
    }

    // Trang tổng quan Admin
    public function dashboard()
    {
        $this->checkAdmin();
        $db = Database::getConnection();

        // Đếm số liệu thống kê
        $totalUsers = (int) $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $totalPosts = (int) $db->query("SELECT COUNT(*) FROM posts")->fetchColumn();
        $totalRequests = (int) $db->query("SELECT COUNT(*) FROM item_requests")->fetchColumn();

        $stmt = $db->prepare("SELECT COUNT(*) FROM item_requests WHERE status = 'pending'");
        $stmt->execute();
        $pendingRequests = (int) $stmt->fetchColumn();

        $data = [
            'totalUsers' => $totalUsers,
            'totalPosts' => $totalPosts,
            'totalRequests' => $totalRequests,
            'pendingRequests' => $pendingRequests
        ];

        // ÉP BUỘC DÙNG GIAO DIỆN 'admin_layout'
        $this->view('admin/index', $data, 'admin_layout');
    }
}