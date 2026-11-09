<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\UserModel;
use PDOException;

class UserController extends Controller
{
    // Xem hồ sơ cá nhân
    public function profile()
    {
        $user = $this->requireLogin();

        $error = '';
        $success = $_SESSION['profile_success'] ?? '';
        unset($_SESSION['profile_success']);

        $this->view('user/profile', [
            'user' => $user,
            'error' => $error,
            'success' => $success
        ]);
    }

    // Cập nhật hồ sơ
    public function updateProfile()
    {
        $user = $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('index.php?controller=User&action=profile');
        }

        $fullName = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');

        $error = '';

        if ($fullName === '') {
            $error = 'Vui lòng nhập họ và tên.';
        } elseif ($phone !== '' &&
                  !preg_match('/^[0-9]{10,11}$/', $phone)) {
            $error = 'Số điện thoại phải có 10 hoặc 11 chữ số.';
        }

        if ($error !== '') {
            $user['full_name'] = $fullName;
            $user['phone'] = $phone;
            $user['address'] = $address;

            $this->view('user/profile', [
                'user' => $user,
                'error' => $error,
                'success' => ''
            ]);
            return;
        }

        try {
            $userModel = new UserModel();

            $updated = $userModel->updateProfile(
                (int) $_SESSION['user_id'],
                [
                    'full_name' => $fullName,
                    'phone' => $phone !== '' ? $phone : null,
                    'address' => $address !== '' ? $address : null
                ]
            );

            if ($updated) {
                $_SESSION['username'] = $fullName;
                $_SESSION['profile_success'] = 'Cập nhật hồ sơ thành công.';

                $this->redirect('index.php?controller=User&action=profile');
            }

            $error = 'Không thể cập nhật hồ sơ.';

        } catch (PDOException $e) {
            error_log($e->getMessage());
            $error = 'Có lỗi xảy ra khi cập nhật hồ sơ.';
        }

        $this->view('user/profile', [
            'user' => $user,
            'error' => $error,
            'success' => ''
        ]);
    }

    // Hiển thị trang đổi mật khẩu
    public function changePassword()
    {
        $this->requireLogin();

        $error = '';
        $success = '';

        $this->view('user/change_password', [
            'error' => $error,
            'success' => $success
        ]);
    }

    // Xử lý đổi mật khẩu
    public function postChangePassword()
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('index.php?controller=User&action=changePassword');
        }

        $oldPassword = $_POST['old_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        $error = '';
        $success = '';

        try {
            $userModel = new UserModel();
            $user = $userModel->findByEmail(
                $userModel->findById((int) $_SESSION['user_id'])['email']
            );

            if (!$user || !password_verify($oldPassword, $user['password_hash'])) {
                $error = 'Mật khẩu cũ không chính xác.';
            } elseif (strlen($newPassword) < 8) {
                $error = 'Mật khẩu mới phải có ít nhất 8 ký tự.';
            } elseif ($newPassword !== $confirmPassword) {
                $error = 'Mật khẩu xác nhận không khớp.';
            } elseif (password_verify($newPassword, $user['password_hash'])) {
                $error = 'Mật khẩu mới phải khác mật khẩu cũ.';
            } else {
                $updated = $userModel->updatePassword(
                    (int) $_SESSION['user_id'],
                    $newPassword
                );

                if ($updated) {
                    $success = 'Đổi mật khẩu thành công.';
                } else {
                    $error = 'Không thể đổi mật khẩu.';
                }
            }

        } catch (PDOException $e) {
            error_log($e->getMessage());
            $error = 'Có lỗi xảy ra khi đổi mật khẩu.';
        }

        $this->view('user/change_password', [
            'error' => $error,
            'success' => $success
        ]);
    }
}
