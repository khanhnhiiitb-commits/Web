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
        // Kiểm tra xem đã đăng nhập chưa
        if (empty($_SESSION['user_id'])) {
            header('Location: ?controller=Auth&action=login');
            exit;
        }

        if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            // Nếu là user thường, đá về trang chủ và báo lỗi (hoặc chuyển đến trang 403)
            http_response_code(403);
            exit('Bạn không có quyền truy cập khu vực quản trị.');
        }
    }
   public function dashboard()
    {
        
        $this->checkAdmin();
        $db = Database::getConnection();

        // Lấy khoảng thời gian từ URL (mặc định là 'month' - tháng này)
        $range = $_GET['range'] ?? 'month';
        
        // Xác định điều kiện thời gian cho câu lệnh SQL
        $dateCondition = "";
        if ($range === 'today') {
            $dateCondition = " WHERE DATE(created_at) = CURDATE()";
        } elseif ($range === 'week') {
            $dateCondition = " WHERE created_at >= NOW() - INTERVAL 7 DAY";
        } elseif ($range === 'month') {
            $dateCondition = " WHERE created_at >= NOW() - INTERVAL 30 DAY";
        }

        $userCondition = $dateCondition;
        $itemCondition = $dateCondition;
        $requestCondition = $dateCondition ? $dateCondition . " AND status = 'accepted'" : " WHERE status = 'accepted'";
        $pendingCondition = $dateCondition ? $dateCondition . " AND status = 'pending_review'" : " WHERE status = 'pending_review'";

        // 1. Đếm tổng số tài khoản theo khoảng thời gian
        $stmt = $db->prepare("SELECT COUNT(*) FROM users" . $userCondition);
        $stmt->execute();
        $totalUsers = (int) $stmt->fetchColumn();

        // 2. Đếm tổng số bài đăng theo khoảng thời gian
        $stmt = $db->prepare("SELECT COUNT(*) FROM items" . $itemCondition);
        $stmt->execute();
        $totalProducts = (int) $stmt->fetchColumn();

        // 3. Đếm tổng giao dịch thành công theo khoảng thời gian
        $stmt = $db->prepare("SELECT COUNT(*) FROM item_requests" . $requestCondition);
        $stmt->execute();
        $totalTransactions = (int) $stmt->fetchColumn();

      // Đếm tất cả các bài chưa được duyệt 
        $stmt = $db->prepare("SELECT COUNT(*) FROM items WHERE status != 'available' OR status IS NULL");
        $stmt->execute();
        $pendingRequests = (int) $stmt->fetchColumn();

        // 5. Lấy danh sách giao dịch gần đây (giữ nguyên hoặc lọc theo ý muốn)
        $recentTransactions = $db->query(
            "SELECT 
                item_requests.id,
                item_requests.status,
                items.title AS item_title,
                giver.full_name AS giver_name,
                requester.full_name AS requester_name
             FROM item_requests
             INNER JOIN items ON item_requests.item_id = items.id
             INNER JOIN users AS requester ON item_requests.requester_id = requester.id
             INNER JOIN users AS giver ON items.user_id = giver.id
             ORDER BY item_requests.created_at DESC
             LIMIT 5"
        )->fetchAll(PDO::FETCH_ASSOC);

        // Truyền biến $range ra View để làm nổi bật (active) nút đang chọn
        $data = [
            'totalUsers' => $totalUsers,
            'totalProducts' => $totalProducts,
            'totalTransactions' => $totalTransactions,
            'pendingRequests' => $pendingRequests,
            'recentTransactions' => $recentTransactions,
            'currentRange' => $range
        ];

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
        
        // Tự động quay lại chính trang vừa thao tác
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }

  public function products()
    {
        $this->checkAdmin();
        $db = Database::getConnection();
        
        $products = $db->query(
            "SELECT items.*, 
                    users.full_name AS user_name, 
                    categories.name AS category 
             FROM items 
             LEFT JOIN users ON items.user_id = users.id 
             LEFT JOIN categories ON items.category_id = categories.id 
             ORDER BY items.id DESC"
        )->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($_SESSION['admin_product_csrf'])) {
            $_SESSION['admin_product_csrf'] = bin2hex(random_bytes(32));
        }
        $this->view('admin/manage_products', [
            'products' => $products,
            'csrfToken' => $_SESSION['admin_product_csrf']
        ], 'admin_layout');
    }
   private function verifyProductAction(): int
    {
        $this->checkAdmin();
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            http_response_code(405);
            exit('Chỉ chấp nhận POST.');
        }
        $token = (string)($_POST['csrf_token'] ?? '');
        if (empty($_SESSION['admin_product_csrf'])
            || !hash_equals((string)$_SESSION['admin_product_csrf'], $token)) {
            http_response_code(403);
            exit('CSRF không hợp lệ.');
        }
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        if (!$id || $id <= 0) {
            http_response_code(422);
            exit('Mã món đồ không hợp lệ.');
        }
        return (int)$id;
    }

    public function approveProduct(): void
    {
        $id = $this->verifyProductAction();
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE items SET status='available'
                              WHERE id=:id AND status='pending_review'");
        $stmt->execute([':id' => $id]);
        header('Location: index.php?controller=Admin&action=products');
        exit;
    }

    public function rejectProduct(): void
    {
        $id = $this->verifyProductAction();
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE items SET status='rejected'
                              WHERE id=:id AND status='pending_review'");
        $stmt->execute([':id' => $id]);
        header('Location: index.php?controller=Admin&action=products');
        exit;
    }

   public function deleteProduct()
    {
        $this->checkAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0) {
                $db = Database::getConnection();
                
                // Xóa bài đăng trong bảng items theo ID
                $stmt = $db->prepare("DELETE FROM items WHERE id = :id");
                $stmt->execute(['id' => $id]);
            }
        }
        // Quay lại trang quản lý bài đăng
        header('Location: ?controller=Admin&action=products');
        exit;
    }

   public function transactions()
    {
        $this->checkAdmin();
        $db = Database::getConnection();
        
        // Lấy danh sách yêu cầu nhận đồ (Lịch sử giao dịch)
        $transactions = $db->query(
            "SELECT 
                item_requests.id,
                item_requests.status,
                item_requests.created_at,
                items.title AS item_title,
                users.full_name AS requester_name
             FROM item_requests
             INNER JOIN items ON item_requests.item_id = items.id
             INNER JOIN users ON item_requests.requester_id = users.id
             ORDER BY item_requests.created_at DESC"
        )->fetchAll(PDO::FETCH_ASSOC);
        
        $this->view('admin/manage_transactions', ['transactions' => $transactions], 'admin_layout');
    }

    // Tiện thể thêm luôn hàm Xóa giao dịch để tránh lỗi khi bấm nút Xóa ở View
    public function deleteTransaction()
    {
        $this->checkAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0) {
                $db = Database::getConnection();
                $stmt = $db->prepare("DELETE FROM item_requests WHERE id = :id");
                $stmt->execute(['id' => $id]);
            }
        }
        $redirectUrl = $_SERVER['HTTP_REFERER'] ?? '?controller=Admin&action=transactions';
        header('Location: ' . $redirectUrl);
        exit;
    }
    // TÍNH NĂNG: XUẤT BÁO CÁO EXCEL (DASHBOARD)
   public function exportExcel()
    {
        $this->checkAdmin();
        $db = Database::getConnection();

        // 1. Cài đặt múi giờ Việt Nam để ngày xuất báo cáo chuẩn xác
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $exportTime = date('d/m/Y H:i:s');

        // 2. Đếm số liệu tổng quan
        $stmt = $db->query("SELECT COUNT(*) FROM users");
        $totalUsers = (int) $stmt->fetchColumn();

        $stmt = $db->query("SELECT COUNT(*) FROM items");
        $totalPosts = (int) $stmt->fetchColumn();

        $stmt = $db->query("SELECT COUNT(*) FROM item_requests WHERE status = 'accepted'");
        $totalRequests = (int) $stmt->fetchColumn();

        // 3. Lấy chi tiết giao dịch (Đã sửa lại câu truy vấn để lấy đúng dữ liệu)
        $transactions = $db->query(
            "SELECT 
                item_requests.id AS request_id,
                item_requests.status,
                item_requests.created_at,
                items.title AS item_title,
                users.full_name AS requester_name
             FROM item_requests
             LEFT JOIN items ON item_requests.item_id = items.id
             LEFT JOIN users ON item_requests.requester_id = users.id
             ORDER BY item_requests.created_at DESC
             LIMIT 50"
        )->fetchAll(PDO::FETCH_ASSOC);

        // Bắt đầu xuất file
        header("Content-Type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=Bao_Cao_Van_Hanh_" . date('Ymd_His') . ".xls");
        header("Pragma: no-cache");
        header("Expires: 0");

        // Xuất thẻ meta để hỗ trợ tiếng Việt có dấu
        echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';

        // 4. Định dạng lại giao diện Excel bằng HTML/CSS
        echo '<div style="font-family: Arial, sans-serif; font-size: 14px;">';
        
        // Tiêu đề chính
        echo '<h2 style="color: #198754; text-align: center; text-transform: uppercase; margin-bottom: 5px;">BÁO CÁO VẬN HÀNH SỐNG XANH SHARING</h2>';
        echo '<p style="text-align: center; font-style: italic; color: #555; margin-bottom: 20px;">Ngày xuất: ' . $exportTime . '</p>';

        // Bảng 1: Thống kê tổng quan
        echo '<table border="1" cellpadding="8" style="border-collapse: collapse; width: 50%; margin: 0 auto 30px auto; font-family: Arial;">';
        echo '<tr><th colspan="2" style="background-color: #198754; color: white; text-align: center; font-size: 16px;">TỔNG QUAN HỆ THỐNG</th></tr>';
        echo '<tr><td style="width: 60%;"><strong>Tổng số tài khoản:</strong></td><td style="text-align: right;">' . $totalUsers . '</td></tr>';
        echo '<tr><td><strong>Tổng số bài đăng:</strong></td><td style="text-align: right;">' . $totalPosts . '</td></tr>';
        echo '<tr><td><strong>Tổng giao dịch thành công:</strong></td><td style="text-align: right;">' . $totalRequests . '</td></tr>';
        echo '</table>';

        // Bảng 2: Chi tiết giao dịch
        echo '<table border="1" cellpadding="8" style="border-collapse: collapse; width: 100%; font-family: Arial;">';
        echo '<tr><th colspan="5" style="background-color: #0dcaf0; color: white; text-align: center; font-size: 16px;">CHI TIẾT 50 GIAO DỊCH GẦN NHẤT</th></tr>';
        echo '<tr style="background-color: #f8f9fa;">
                <th style="width: 10%; text-align: center;">Mã Đơn</th>
                <th style="width: 35%; text-align: left;">Tên Món Đồ</th>
                <th style="width: 25%; text-align: left;">Người Nhận</th>
                <th style="width: 15%; text-align: center;">Trạng Thái</th>
                <th style="width: 15%; text-align: center;">Ngày Tạo</th>
              </tr>';

        if (empty($transactions)) {
            echo '<tr><td colspan="5" style="text-align: center; color: red;">Không có dữ liệu giao dịch nào.</td></tr>';
        } else {
            foreach ($transactions as $txn) {
                // Chuyển đổi trạng thái sang tiếng Việt
                $statusMap = [
                    'pending' => '<span style="color: #ffc107; font-weight: bold;">Chờ duyệt</span>',
                    'accepted' => '<span style="color: #198754; font-weight: bold;">Thành công</span>',
                    'rejected' => '<span style="color: #dc3545; font-weight: bold;">Từ chối</span>'
                ];
                $displayStatus = $statusMap[$txn['status']] ?? $txn['status'];

                // Định dạng lại ngày giờ
                $formattedDate = date('d/m/Y H:i', strtotime($txn['created_at']));

                echo '<tr>';
                echo '<td style="text-align: center;">#' . htmlspecialchars($txn['request_id']) . '</td>';
                echo '<td>' . htmlspecialchars($txn['item_title'] ?? 'N/A') . '</td>';
                echo '<td>' . htmlspecialchars($txn['requester_name'] ?? 'N/A') . '</td>';
                echo '<td style="text-align: center;">' . $displayStatus . '</td>';
                echo '<td style="text-align: center;">' . $formattedDate . '</td>';
                echo '</tr>';
            }
        }
        
        echo '</table>';
        echo '</div>';
        exit;
    }
}