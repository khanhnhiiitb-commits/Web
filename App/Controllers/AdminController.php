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

        // Lọc riêng cho bảng users (có cột created_at)
        $userCondition = $dateCondition;
        // Lọc riêng cho bảng items (có cột created_at)
        $itemCondition = $dateCondition;
        // Lọc riêng cho bảng item_requests (có cột created_at)
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

        // 4. Đếm bài chờ duyệt theo khoảng thời gian
        $stmt = $db->prepare("SELECT COUNT(*) FROM items" . $pendingCondition);
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

   public function deleteProduct(): void
    {
        $id = $this->verifyProductAction();
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM items WHERE id = :id AND status IN ('pending_review','rejected')");
        $stmt->execute([':id' => $id]);
        header('Location: index.php?controller=Admin&action=products');
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

        // 1. Đếm tổng số tài khoản
        $stmt = $db->query("SELECT COUNT(*) FROM users");
        $totalUsers = (int) $stmt->fetchColumn();

        // 2. Đếm tổng số bài đăng (dùng $totalPosts để khớp với đoạn code echo bên dưới)
        $stmt = $db->query("SELECT COUNT(*) FROM items");
        $totalPosts = (int) $stmt->fetchColumn();

        // 3. Đếm tổng giao dịch thành công (dùng $totalRequests hoặc $totalTransactions khớp với echo)
        $stmt = $db->query("SELECT COUNT(*) FROM item_requests WHERE status = 'accepted'");
        $totalRequests = (int) $stmt->fetchColumn();

        // Lấy danh sách chi tiết giao dịch để đổ vào bảng dưới
        $transactions = $db->query(
            "SELECT 
                item_requests.id,
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