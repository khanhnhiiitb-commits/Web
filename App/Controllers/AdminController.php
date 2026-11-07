<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\UserModel;
use PDO;

class AdminController extends Controller
{
    // Kiểm tra quyền Admin
    private function checkAdmin()
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: index.php?controller=Auth&action=login');
            exit;
        }

        if (($_SESSION['role'] ?? '') !== 'admin') {
            header('Location: index.php?controller=Home&action=index');
            exit;
        }

        // Kiểm tra tài khoản có còn hoạt động không
        $userModel = new UserModel();
        $user = $userModel->findById($_SESSION['user_id']);

        if (!$user || (int) $user['status'] !== 1) {
            $_SESSION = [];
            session_destroy();

            header('Location: index.php?controller=Auth&action=login');
            exit;
        }
    }

    // Trang quản trị
    public function dashboard()
    {
        $this->checkAdmin();

        $db = Database::getConnection();

        $totalUsers = (int) $db
            ->query("SELECT COUNT(*) FROM users")
            ->fetchColumn();

        $totalPosts = (int) $db
            ->query("SELECT COUNT(*) FROM posts")
            ->fetchColumn();

        $totalRequests = (int) $db
            ->query("SELECT COUNT(*) FROM item_requests")
            ->fetchColumn();

        $stmt = $db->prepare(
            "SELECT COUNT(*) FROM item_requests
             WHERE status = :status"
        );
        $stmt->execute([':status' => 'pending']);

        $pendingRequests = (int) $stmt->fetchColumn();

        $stmt = $db->query(
            "SELECT id, full_name, email, role, status
             FROM users ORDER BY id DESC LIMIT 5"
        );
        $recentUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

        $this->view('admin/dashboard', [
            'totalUsers' => $totalUsers,
            'totalPosts' => $totalPosts,
            'totalRequests' => $totalRequests,
            'pendingRequests' => $pendingRequests,
            'recentUsers' => $recentUsers,
            'recentRequests' => $recentRequests
        ]);
    }

    // Danh sách người dùng
    public function users()
    {
        $this->checkAdmin();

        $userModel = new UserModel();
        $users = $userModel->getAllUsers();

        $this->view('admin/manage_users', [
            'users' => $users
        ]);
    }

    // Khóa hoặc mở khóa tài khoản
    public function updateUserStatus()
    {
        $this->checkAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('index.php?controller=Admin&action=users');
        }

        $id = (int) ($_POST['id'] ?? 0);
        $status = filter_var(
            $_POST['status'] ?? null,
            FILTER_VALIDATE_INT
        );

        if ($id <= 0 || !in_array($status, [0, 1], true)) {
            $this->redirect('index.php?controller=Admin&action=users');
        }

        // Không cho Admin tự khóa chính mình
        if ($id === (int) $_SESSION['user_id']) {
            $this->redirect('index.php?controller=Admin&action=users');
        }

        $userModel = new UserModel();
        $userModel->updateStatus($id, $status);

        $this->redirect('index.php?controller=Admin&action=users');
    }
}
