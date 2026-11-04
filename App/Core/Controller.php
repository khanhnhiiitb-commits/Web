<?php
namespace App\Core;
class Controller {
    // Hàm gọi Model từ thư mục models
    public function model($model) {
        require_once "./models/" . $model . ".php";
        return new $model();
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