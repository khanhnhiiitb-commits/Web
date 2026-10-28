<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\UserModel;

class UserController extends Controller
{
    // Hiển thị trang Profile
    public function profile()
    {
        // Kiểm tra người dùng đã đăng nhập chưa
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('login');
            return;
        }

        $userModel = new UserModel();

        // Lấy thông tin người dùng đang đăng nhập
        $user = $userModel->findById($_SESSION['user_id']);

        // Nếu không tìm thấy tài khoản
        if (!$user) {
            session_unset();
            session_destroy();

            header('Location: index.php?controller=auth&action=login');
            exit;
        }

        $error = '';
        $success = '';

        // Xử lý khi người dùng gửi form cập nhật
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fullName = trim($_POST['full_name'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $address = trim($_POST['address'] ?? '');

            // Kiểm tra họ tên
            if ($fullName === '') {
                $error = 'Vui lòng nhập họ và tên.';
            } else {
                $data = [
                    'full_name' => $fullName,
                    'phone' => $phone,
                    'address' => $address
                ];

                if ($userModel->updateProfile(
                    (int) $_SESSION['user_id'],
                    $data
                )) {
                    // Lấy lại dữ liệu mới nhất từ database
                    $user = $userModel->findById(
                        $_SESSION['user_id']
                    );

                    $success = 'Cập nhật thông tin thành công.';
                } else {
                    $error = 'Cập nhật thông tin thất bại.';
                }
            }
        }

        // Hiển thị giao diện Profile
        require __DIR__ . '/../../views/user/profile.php';
    }
}
