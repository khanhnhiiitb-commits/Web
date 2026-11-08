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
            header('Location: ' . BASE_URL . 'index.php?controller=Auth&action=login');
            exit;
        }
        if (($_SESSION['role'] ?? '') !== 'admin') {
            header('Location: ' . BASE_URL . 'index.php?controller=Home&action=index');
            exit;
        }
        $userModel = new UserModel();
        $user = $userModel->findById($_SESSION['user_id']);
        if (!$user || (int) $user['status'] !== 1) {
            $_SESSION = [];
            session_destroy();
            header('Location: ' . BASE_URL . 'index.php?controller=Auth&action=login');
            exit;
        }
    }

    public function dashboard()
    {
        $this->checkAdmin();
        $db = Database::getConnection();

        $totalUsers = (int) $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $totalPosts = (int) $db->query("SELECT COUNT(*) FROM posts")->fetchColumn();
        $totalRequests = (int) $db->query("SELECT COUNT(*) FROM item_requests")->fetchColumn();

        $stmt = $db->prepare("SELECT COUNT(*) FROM item_requests WHERE status = 'pending'");
        $stmt->execute();
        $pendingRequests = (int) $stmt->fetchColumn();

        $recentRequests = $db->query(
            "SELECT item_requests.id, item_requests.status, users.full_name 
             FROM item_requests 
             LEFT JOIN users ON item_requests.requester_id = users.id 
             ORDER BY item_requests.id DESC LIMIT 5"
        )->fetchAll(PDO::FETCH_ASSOC);

        $recentPendingPosts = $db->query(
            "SELECT posts.id, posts.title, users.full_name as user_name 
             FROM posts 
             LEFT JOIN users ON posts.user_id = users.id 
             WHERE posts.status = 'pending' 
             ORDER BY posts.id DESC LIMIT 3"
        )->fetchAll(PDO::FETCH_ASSOC);

        $data = [
            'totalUsers' => $totalUsers,
            'totalPosts' => $totalPosts,
            'totalRequests' => $totalRequests,
            'pendingRequests' => $pendingRequests,
            'recentRequests' => $recentRequests,
            'recentPendingPosts' => $recentPendingPosts
        ];

        // ÉP BUỘC DÙNG GIAO DIỆN 'admin_layout'. DÒNG NÀY QUYẾT ĐỊNH VIỆC CÓ GIAO DIỆN HAY KHÔNG
        $this->view('admin/index', $data, 'admin_layout');
    }

    public function users()
    {
        $this->checkAdmin();
        $userModel = new UserModel();
        $users = $userModel->getAllUsers();
        $this->view('admin/manage_users', ['users' => $users], 'admin_layout');
    }

    public function updateUserStatus()
    {
        $this->checkAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int) ($_POST['id'] ?? 0);
            $status = filter_var($_POST['status'] ?? null, FILTER_VALIDATE_INT);

            if ($id > 0 && in_array($status, [0, 1], true) && $id !== (int) $_SESSION['user_id']) {
                $userModel = new UserModel();
                $userModel->updateStatus($id, $status);
            }
        }
        header('Location: ' . BASE_URL . 'index.php?controller=Admin&action=users');
        exit;
    }

    public function products()
    {
        $this->checkAdmin();
        $db = Database::getConnection();
        $products = $db->query("SELECT * FROM posts ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
        $this->view('admin/manage_products', ['products' => $products], 'admin_layout');
    }

    public function approveProduct()
    {
        $this->checkAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0) {
                $db = Database::getConnection();
                $stmt = $db->prepare("UPDATE posts SET status = 'available' WHERE id = :id");
                $stmt->execute(['id' => $id]);
            }
        }
        if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'dashboard') !== false) {
            header('Location: ' . BASE_URL . 'index.php?controller=Admin&action=dashboard');
        } else {
            header('Location: ' . BASE_URL . 'index.php?controller=Admin&action=products');
        }
        exit;
    }

    public function deleteProduct()
    {
        $this->checkAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0) {
                $db = Database::getConnection();
                $stmt = $db->prepare("DELETE FROM posts WHERE id = :id");
                $stmt->execute(['id' => $id]);
            }
        }
        header('Location: ' . BASE_URL . 'index.php?controller=Admin&action=products');
        exit;
    }

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

        $this->view('admin/manage_transactions', ['transactions' => $transactions], 'admin_layout');
    }
    // ==========================================
    // TÍNH NĂNG: XUẤT BÁO CÁO EXCEL (DASHBOARD)
    // ==========================================
    public function exportExcel()
    {
        $this->checkAdmin();
        $db = Database::getConnection();

        // 1. Lấy dữ liệu thống kê tổng quát
        $totalUsers = (int) $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $totalPosts = (int) $db->query("SELECT COUNT(*) FROM posts")->fetchColumn();
        $totalRequests = (int) $db->query("SELECT COUNT(*) FROM item_requests")->fetchColumn();

        // 2. Lấy dữ liệu 50 giao dịch gần nhất để xuất ra chi tiết
        $recentTransactions = $db->query(
            "SELECT item_requests.id, item_requests.status, item_requests.created_at, users.full_name as requester_name 
             FROM item_requests 
             LEFT JOIN users ON item_requests.requester_id = users.id 
             ORDER BY item_requests.id DESC LIMIT 50"
        )->fetchAll(PDO::FETCH_ASSOC);

        // 3. Thiết lập Header để trình duyệt hiểu đây là file tải về dạng CSV (Excel đọc được)
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="BaoCao_SongXanh_' . date('Ymd_His') . '.csv"');
        
        // Fix lỗi font tiếng Việt khi mở bằng Excel (Thêm BOM)
        echo "\xEF\xBB\xBF"; 

        $output = fopen('php://output', 'w');

        // Ghi phần thống kê tổng quan
        fputcsv($output, ['--- BÁO CÁO VẬN HÀNH SỐNG XANH SHARING ---']);
        fputcsv($output, ['Ngày xuất:', date('d/m/Y H:i:s')]);
        fputcsv($output, []);
        fputcsv($output, ['TỔNG QUAN HỆ THỐNG']);
        fputcsv($output, ['Tổng số tài khoản:', $totalUsers]);
        fputcsv($output, ['Tổng số bài đăng:', $totalPosts]);
        fputcsv($output, ['Tổng giao dịch:', $totalRequests]);
        fputcsv($output, []);
        
        // Ghi tiêu đề các cột cho bảng giao dịch
        fputcsv($output, ['CHI TIẾT 50 GIAO DỊCH GẦN NHẤT']);
        fputcsv($output, ['Mã Đơn', 'Người Nhận', 'Trạng Thái', 'Ngày Tạo']);

        // Ghi dữ liệu từng dòng
        if (!empty($recentTransactions)) {
            foreach ($recentTransactions as $row) {
                fputcsv($output, [
                    $row['id'],
                    $row['requester_name'] ?? 'Ẩn danh',
                    $row['status'],
                    $row['created_at'] ?? 'Không rõ'
                ]);
            }
        } else {
            fputcsv($output, ['Không có dữ liệu giao dịch nào.']);
        }

        fclose($output);
        exit;
    }
}