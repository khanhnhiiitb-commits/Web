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

       $stmt = $db->prepare("SELECT COUNT(*) FROM posts WHERE status = 'pending'");
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
        $this->requireAdmin();
        $userModel = new UserModel();
        $users = $userModel->getAllUsers();
        $this->view('admin/manage_users', ['users' => $users], 'admin_layout');
    }

   public function updateUserStatus()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int) ($_POST['id'] ?? 0);
            $status = filter_var($_POST['status'] ?? null, FILTER_VALIDATE_INT);

            if ($id > 0 && in_array($status, [0, 1], true) && $id !== (int) $_SESSION['user_id']) {
                $userModel = new UserModel();
                $userModel->updateStatus($id, $status);
            }
        }
        
        // Tự động quay lại chính trang vừa thao tác
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }

   public function products()
    {
        $this->checkAdmin();
        $db = Database::getConnection();
        
        $products = $db->query(
            "SELECT posts.*, users.full_name as user_name 
             FROM posts 
             LEFT JOIN users ON posts.user_id = users.id 
             ORDER BY posts.id DESC"
        )->fetchAll(PDO::FETCH_ASSOC);
        
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
        
        header('Location: ' . $_SERVER['HTTP_REFERER']);
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
        
        // Tự động quay lại chính trang vừa thao tác
        header('Location: ' . $_SERVER['HTTP_REFERER']);
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

        // 1. Lấy dữ liệu thống kê
        $totalUsers = (int) $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $totalPosts = (int) $db->query("SELECT COUNT(*) FROM posts")->fetchColumn();
        $totalRequests = (int) $db->query("SELECT COUNT(*) FROM item_requests")->fetchColumn();

        // 2. Lấy dữ liệu 50 giao dịch
        $recentTransactions = $db->query(
            "SELECT item_requests.id, item_requests.status, item_requests.created_at, users.full_name as requester_name 
             FROM item_requests 
             LEFT JOIN users ON item_requests.requester_id = users.id 
             ORDER BY item_requests.id DESC LIMIT 50"
        )->fetchAll(PDO::FETCH_ASSOC);

        // 3. Đổi Header sang định dạng chuẩn XLS của Excel
        $filename = "BaoCao_SongXanh_" . date('Ymd_His') . ".xls";
        header("Content-Type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        
        // 4. In ra mã HTML được style bằng CSS
        echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
        echo '<head><meta charset="UTF-8"></head>';
        echo '<body>';
        
        // Tiêu đề báo cáo
        echo '<h2 style="color: #198754; text-align: center; font-size: 24px;">BÁO CÁO VẬN HÀNH SỐNG XANH SHARING</h2>';
        echo '<p><strong>Ngày xuất:</strong> ' . date('d/m/Y H:i:s') . '</p>';
        
        // Bảng 1: Thống kê tổng quan
        echo '<table border="1" cellpadding="8" style="border-collapse: collapse; width: 100%; font-family: Arial;">';
        echo '<tr style="background-color: #198754; color: white; font-weight: bold;"><td colspan="2" style="text-align: center;">TỔNG QUAN HỆ THỐNG</td></tr>';
        echo '<tr><td style="width: 200px;"><strong>Tổng số tài khoản:</strong></td><td style="text-align: right;">' . $totalUsers . '</td></tr>';
        echo '<tr><td><strong>Tổng số bài đăng:</strong></td><td style="text-align: right;">' . $totalPosts . '</td></tr>';
        echo '<tr><td><strong>Tổng giao dịch:</strong></td><td style="text-align: right;">' . $totalRequests . '</td></tr>';
        echo '</table><br><br>';
        
        // Bảng 2: Chi tiết giao dịch
        echo '<table border="1" cellpadding="8" style="border-collapse: collapse; width: 100%; font-family: Arial;">';
        echo '<tr style="background-color: #0dcaf0; color: white; font-weight: bold;"><td colspan="4" style="text-align: center;">CHI TIẾT 50 GIAO DỊCH GẦN NHẤT</td></tr>';
        echo '<tr style="background-color: #f8f9fa; font-weight: bold; text-align: center;">
                <td style="width: 80px;">Mã Đơn</td>
                <td style="width: 250px;">Người Nhận</td>
                <td style="width: 150px;">Trạng Thái</td>
                <td style="width: 200px;">Ngày Tạo</td>
              </tr>';

        if (!empty($recentTransactions)) {
            foreach ($recentTransactions as $row) {
                // Tô màu trạng thái
                $statusColor = 'black';
                if ($row['status'] === 'completed') $statusColor = '#198754'; // Xanh lá
                if ($row['status'] === 'pending') $statusColor = '#fd7e14'; // Cam

                echo '<tr>';
                echo '<td style="text-align: center;">#' . $row['id'] . '</td>';
                echo '<td>' . htmlspecialchars($row['requester_name'] ?? 'Ẩn danh') . '</td>';
                echo '<td style="color: ' . $statusColor . '; font-weight: bold; text-align: center;">' . strtoupper($row['status']) . '</td>';
                echo '<td style="text-align: center;">' . ($row['created_at'] ?? 'Chưa rõ') . '</td>';
                echo '</tr>';
            }
        } else {
            echo '<tr><td colspan="4" style="text-align: center; color: red;">Không có dữ liệu giao dịch nào.</td></tr>';
        }
        
        echo '</table>';
        echo '</body></html>';
        exit;
    }
}