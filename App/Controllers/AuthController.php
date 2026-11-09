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
        if (!empty($_SESSION['user_id'])) {
            $this->goToHome();
        }

        $success = $_SESSION['success'] ?? '';
        unset($_SESSION['success']);

        $error = '';

        require __DIR__ . '/../Views/auth/login.php';
    }

    // Hiển thị trang đăng ký
    public function register()
    {
        $error = '';
        $success = '';

        require __DIR__ . '/../Views/auth/register.php';
    }

    // Xử lý đăng nhập
    public function postLogin()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('index.php?controller=Auth&action=login');
        }

        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';

        $error = '';
        $success = '';

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Email không đúng định dạng.';
        } elseif ($password === '') {
            $error = 'Vui lòng nhập mật khẩu.';
        }

        if ($error !== '') {
            require __DIR__ . '/../Views/auth/login.php';
            return;
        }

        try {
            $user = $this->userModel->findByEmail($email);

            if (!$user || !password_verify($password, $user['password_hash'])) {
                $error = 'Email hoặc mật khẩu không chính xác.';
                require __DIR__ . '/../Views/auth/login.php';
                return;
            }

            if ((int) $user['status'] !== 1) {
                $error = 'Tài khoản của bạn đã bị khóa.';
                require __DIR__ . '/../Views/auth/login.php';
                return;
            }

            // Tạo phiên đăng nhập mới
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'admin') {
    $this->redirect('index.php?controller=Admin&action=dashboard');
} else {
    $this->redirect('index.php?controller=Home&action=index');
}

            // Phân quyền chuyển trang
            $this->goToHome();

        } catch (PDOException $e) {
            error_log($e->getMessage());

            $error = 'Có lỗi xảy ra khi đăng nhập. Vui lòng thử lại.';
            require __DIR__ . '/../Views/auth/login.php';
        }
    }

    // Xử lý đăng ký
    public function postRegister()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('index.php?controller=Auth&action=register');
        }

        $fullName = trim($_POST['full_name'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        $error = '';
        $success = '';

        if ($fullName === '') {
            $error = 'Vui lòng nhập họ và tên.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Email không đúng định dạng.';
        } elseif (strlen($password) < 8) {
            $error = 'Mật khẩu phải có ít nhất 8 ký tự.';
        } elseif ($password !== $confirmPassword) {
            $error = 'Mật khẩu xác nhận không khớp.';
        }

        if ($error !== '') {
            require __DIR__ . '/../Views/auth/register.php';
            return;
        }

        try {
            if ($this->userModel->findByEmail($email)) {
                $error = 'Email này đã được đăng ký.';
                require __DIR__ . '/../Views/auth/register.php';
                return;
            }

            $created = $this->userModel->createUser([
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone !== '' ? $phone : null,
                'password' => $password,
                'address' => null
            ]);

            if ($created) {
                $_SESSION['success'] = 'Đăng ký thành công! Hãy đăng nhập.';
                $this->redirect('index.php?controller=Auth&action=login');
            }

            $error = 'Đăng ký thất bại. Vui lòng thử lại.';

        } catch (PDOException $e) {
            error_log($e->getMessage());
            $error = 'Không thể tạo tài khoản. Vui lòng thử lại.';
        }

        require __DIR__ . '/../Views/auth/register.php';
    }

    // Chuyển hướng theo quyền
    private function goToHome()
    {
        if (($_SESSION['role'] ?? '') === 'admin') {
            $this->redirect('index.php?controller=Admin&action=dashboard');
        }

        $this->redirect('index.php?controller=Home&action=index');
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

        $this->redirect('index.php?controller=Home&action=index');
    }
}
