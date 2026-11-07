<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\UserModel;
use PDOException;

class AuthController extends Controller
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    // Hiển thị trang đăng nhập
    public function login()
    {
        $success = $_SESSION['success'] ?? '';
        unset($_SESSION['success']);

        $this->view('auth/login', [
            'error' => '',
            'success' => $success
        ]);
    }

    // Hiển thị trang đăng ký
    public function register()
    {
        $this->view('auth/register', [
            'error' => '',
            'success' => ''
        ]);
    }

    // Xử lý đăng nhập
    public function postLogin()
    {
        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->view('auth/login', [
                'error' => 'Email không đúng định dạng.',
                'success' => ''
            ]);
            return;
        }

        if ($password === '') {
            $this->view('auth/login', [
                'error' => 'Vui lòng nhập mật khẩu.',
                'success' => ''
            ]);
            return;
        }

        try {
            $user = $this->userModel->findByEmail($email);

            if (!$user || !password_verify($password, $user['password_hash'])) {
                $this->view('auth/login', [
                    'error' => 'Email hoặc mật khẩu không chính xác.',
                    'success' => ''
                ]);
                return;
            }

            // Chỉ cho phép tài khoản đang hoạt động đăng nhập
            if ((int)$user['status'] !== 1) {
                $this->view('auth/login', [
                    'error' => 'Tài khoản của bạn hiện đã bị khóa.',
                    'success' => ''
                ]);
                return;
            }

            // Đăng nhập thành công
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['username'] = $user['full_name'];

            $redirect = $_SESSION['redirect_after_login'] ?? '';
            unset($_SESSION['redirect_after_login']);

            if ($redirect !== '') {
                header('Location: index.php?' . $redirect);
            } else {
                header('Location: index.php?controller=Home&action=index');
            }

            exit;

        } catch (PDOException $e) {
            error_log('LOGIN ERROR: ' . $e->getMessage());

            $this->view('auth/login', [
                'error' => 'Có lỗi xảy ra khi đăng nhập. Vui lòng thử lại.',
                'success' => ''
            ]);
            return;
        }
    }

    // Xử lý đăng ký
    public function postRegister()
    {
        $fullName = trim($_POST['full_name'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');

        $error = '';

        if ($fullName === '') {
            $error = 'Vui lòng nhập họ tên.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Email không đúng định dạng.';
        } elseif (strlen($password) < 8) {
            $error = 'Mật khẩu phải có ít nhất 8 ký tự.';
        } elseif ($password !== $confirmPassword) {
            $error = 'Mật khẩu xác nhận không khớp.';
        }

        if ($error !== '') {
            $this->view('auth/register', [
                'error' => $error,
                'success' => ''
            ]);
            return;
        }

        try {
            // Kiểm tra email đã tồn tại
            if ($this->userModel->findByEmail($email)) {
                $this->view('auth/register', [
                    'error' => 'Email này đã được đăng ký.',
                    'success' => ''
                ]);
                return;
            }

            $created = $this->userModel->createUser([
                'full_name' => $fullName,
                'email' => $email,
                'password' => $password,
                'phone' => $phone !== '' ? $phone : null,
                'address' => $address !== '' ? $address : null
            ]);

            if ($created) {
                $_SESSION['success'] = 'Đăng ký thành công! Bạn hãy đăng nhập.';
                header('Location: index.php?controller=Auth&action=login');
                exit;
            }

            $error = 'Đăng ký chưa thành công. Vui lòng thử lại.';

        } catch (PDOException $e) {
            error_log('REGISTER ERROR: ' . $e->getMessage());
            $error = 'Không thể tạo tài khoản. Vui lòng kiểm tra lại thông tin.';
        }

        $this->view('auth/register', [
            'error' => $error,
            'success' => ''
        ]);
    }

    // Đăng xuất
    public function logout()
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();

        header('Location: index.php?controller=Home&action=index');
        exit;
    }
}