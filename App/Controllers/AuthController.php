<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\UserModel;
use PDOException;

class AuthController extends Controller
{
    // Hiển thị form đăng nhập
    public function login()
    {
        $error = '';
        $success = $_SESSION['success'] ?? '';

        unset($_SESSION['success']);

        require __DIR__ . '/../../views/auth/login.php';
    }

    // Hiển thị form đăng ký
    public function register()
    {
        $error = '';
        $success = '';

        require __DIR__ . '/../../views/auth/register.php';
    }

    // Xử lý dữ liệu đăng nhập từ form
    public function postLogin()
    {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // Kiểm tra dữ liệu đầu vào
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Email không đúng định dạng.';
            require __DIR__ . '/../../views/auth/login.php';
            return;
        }

        if ($password === '') {
            $error = 'Vui lòng nhập mật khẩu.';
            require __DIR__ . '/../../views/auth/login.php';
            return;
        }

        $userModel = new UserModel();
        $user = $userModel->findByEmail($email);

        // Kiểm tra tài khoản và mật khẩu
        if (!$user || !password_verify($password, $user['password_hash'])) {
            $error = 'Email hoặc mật khẩu không chính xác.';
            require __DIR__ . '/../../views/auth/login.php';
            return;
        }

        // Không cho đăng nhập tài khoản đã bị khóa
        if (isset($user['status']) && (int) $user['status'] !== 1) {
            $error = 'Tài khoản của bạn hiện đã bị khóa.';
            require __DIR__ . '/../../views/auth/login.php';
            return;
        }

        // Đăng nhập thành công: lưu thông tin vào Session
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];

        // Chuyển về trang chủ
        $this->redirect('home');
    }

    // Xử lý dữ liệu đăng ký từ form
    public function postRegister()
    {
        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');

        // Kiểm tra họ tên
        if ($fullName === '') {
            $error = 'Vui lòng nhập họ tên.';
            require __DIR__ . '/../../views/auth/register.php';
            return;
        }

        // Kiểm tra định dạng email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Email không đúng định dạng.';
            require __DIR__ . '/../../views/auth/register.php';
            return;
        }

        // Kiểm tra độ dài mật khẩu
        if (strlen($password) < 8) {
            $error = 'Mật khẩu phải có ít nhất 8 ký tự.';
            require __DIR__ . '/../../views/auth/register.php';
            return;
        }

        // Kiểm tra mật khẩu xác nhận
        if ($password !== $confirmPassword) {
            $error = 'Mật khẩu xác nhận không khớp.';
            require __DIR__ . '/../../views/auth/register.php';
            return;
        }

        $userModel = new UserModel();

        // Kiểm tra email đã được sử dụng chưa
        if ($userModel->findByEmail($email)) {
            $error = 'Email này đã được đăng ký.';
            require __DIR__ . '/../../views/auth/register.php';
            return;
        }

        // Tạo tài khoản mới
        try {
            $created = $userModel->createUser([
                'full_name' => $fullName,
                'email' => $email,
                'password' => $password,
                'phone' => $phone !== '' ? $phone : null,
                'address' => $address !== '' ? $address : null
            ]);

            if ($created) {
                $success = 'Đăng ký thành công! Bạn có thể đăng nhập ngay.';
                $error = '';
            } else {
                $error = 'Đăng ký chưa thành công. Vui lòng thử lại.';
                $success = '';
            }
        } catch (PDOException $e) {
            $error = 'Không thể tạo tài khoản. Vui lòng kiểm tra lại thông tin.';
            $success = '';
        }

        require __DIR__ . '/../../views/auth/register.php';
    }

    // Đăng xuất
    public function logout()
    {
        session_unset();
        session_destroy();

        $this->redirect('home');
    }
}
