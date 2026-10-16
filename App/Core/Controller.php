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
        // Phân rã mảng $data thành các biến đơn lẻ (vd: $data['items'] trở thành biến $items)
        extract($data); 
        
        if (file_exists("./views/" . $view . ".php")) {
            require_once "./views/" . $view . ".php";
        } else {
            die("Lỗi: Giao diện (View) không tồn tại tại ./views/" . $view . ".php");
        }
    }

    // Hàm tiện ích: Chuyển hướng trang
    public function redirect($url) {
        header("Location: " . BASE_URL . $url);
        exit();
    }
}