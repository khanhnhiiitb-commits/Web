<?php
namespace App\Core;
class HomeController extends Controller {
    public function index() {
        $title = "Xin chào, hệ thống MVC đã hoạt động!";
        // Truyền biến title ra view home/index
        $this->view("home/index", ['title' => $title]);
    }
}