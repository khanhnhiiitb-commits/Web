<?php
namespace App\Core;

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

    protected function requireLogin(): void
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: index.php?controller=Auth&action=login');
            exit;
        }
    }

    // Chuyển hướng
    public function redirect($url)
    {
        header("Location: " . $url);
        exit();
    }
}