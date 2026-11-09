<?php
namespace App\Core;
use App\Models\UserModel;
class Controller
{
    // Gọi Model
    public function model($model)
    {
        $file = __DIR__ . '/../Models/' . $model . '.php';
        if (!file_exists($file)) {
            die("Không tìm thấy Model: " . $file);
        }
        require_once $file;
        $class = 'App\\Models\\' . $model;
        return new $class();
    }

    // Gọi View thông qua layout chung (ĐÃ SỬA ĐỂ HỖ TRỢ ADMIN)
   public function view($view, $data = [], $layout = 'main')
    {
        if (is_array($data)) {
            extract($data);
        }

        $viewPath = __DIR__ . '/../Views/' . $view . '.php';
        $layoutPath = __DIR__ . '/../Views/layouts/' . $layout . '.php';

        if (!file_exists($viewPath)) {
            die("Không tìm thấy View: " . $viewPath);
        }

        // Lấy nội dung View (Nội dung HTML của trang tổng quan)
        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        if (file_exists($layoutPath)) {
            require $layoutPath;
        } else {
            echo $content;
        }
    }

    // Kiểm tra đăng nhập và trạng thái tài khoản
    protected function requireLogin()
    {
        if (empty($_SESSION['user_id'])) {
            $this->redirect('index.php?controller=Auth&action=login');
        }

        $userModel = new UserModel();
        $user = $userModel->findById((int) $_SESSION['user_id']);

        if (!$user || (int) $user['status'] !== 1) {
            $_SESSION = [];
            session_regenerate_id(true);

            $this->redirect('index.php?controller=Auth&action=login');
        }

        // Cập nhật quyền theo dữ liệu hiện tại trong CSDL
        $_SESSION['role'] = $user['role'];
        $_SESSION['username'] = $user['full_name'];

        return $user;
    }
    // Kiểm tra quyền Admin
    protected function requireAdmin()
    {
        $user = $this->requireLogin();

        if ($user['role'] !== 'admin') {
            $this->redirect('index.php?controller=Home&action=index');
        }

        return $user;
    }

    // Chuyển hướng
    public function redirect($url)
    {
        header("Location: " . $url);
        exit();
    }
}