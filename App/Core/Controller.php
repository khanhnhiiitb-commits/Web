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

    // Gọi View
    public function view($view, $data = [])
    {
        extract($data);

        $viewPath = __DIR__ . '/../Views/' . $view . '.php';

        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            die("Không tìm thấy View: " . $viewPath);
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