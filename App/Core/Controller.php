<?php
namespace App\Core;
class Controller {
    // Hàm gọi Model từ thư mục App/Models
public function model($model)
{
    $file = __DIR__ . '/../Models/' . $model . '.php';

    if (file_exists($file)) {
        require_once $file;
    } else {
        die("Không tìm thấy Model: " . $file);
    }

    $class = 'App\\Models\\' . $model;

    return new $class();
}

    // Hàm gọi View và truyền dữ liệu
    public function view($view, $data = []) {
    extract($data);

    $viewPath = __DIR__ . '/../Views/' . $view . '.php';

    if (file_exists($viewPath)) {
        require $viewPath;
    } else {
        die("Lỗi: Không tìm thấy View: " . $viewPath);
    }
}

    // Hàm tiện ích: Chuyển hướng trang
    public function redirect($url) {
        header("Location: " . BASE_URL . $url);
        exit();
    }
}