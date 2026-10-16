<?php
class App {
    // Thiết lập Controller và Action mặc định nếu người dùng chỉ gõ tên miền
    protected $controller = 'HomeController';
    protected $action = 'index';
    protected $params = [];

    public function __construct() {
        // Lấy url từ thanh địa chỉ và cắt thành mảng
        $arr = $this->UrlProcess();

        // 1. XỬ LÝ CONTROLLER
        if (isset($arr[0])) {
            // Chuyển chữ cái đầu thành viết hoa và thêm chữ 'Controller' (vd: item -> ItemController)
            $controllerName = ucfirst($arr[0]) . 'Controller'; 
            if (file_exists("./controllers/" . $controllerName . ".php")) {
                $this->controller = $controllerName;
                unset($arr[0]);
            }
        }
        // Gọi file Controller ra và khởi tạo đối tượng
        require_once "./controllers/" . $this->controller . ".php";
        $this->controller = new $this->controller;

        // 2. XỬ LÝ ACTION (Hàm bên trong Controller)
        if (isset($arr[1])) {
            if (method_exists($this->controller, $arr[1])) {
                $this->action = $arr[1];
            }
            unset($arr[1]);
        }

        // 3. XỬ LÝ PARAMS (Các tham số truyền vào như id)
        $this->params = $arr ? array_values($arr) : [];

        // Chạy hàm trong Controller và truyền tham số vào
        call_user_func_array([$this->controller, $this->action], $this->params);
    }

    // Hàm bóc tách URL thành mảng
    public function UrlProcess() {
        if (isset($_GET["url"])) {
            // Lọc bỏ ký tự lạ, loại bỏ dấu / ở cuối, và cắt thành mảng
            return explode("/", filter_var(trim($_GET["url"], "/"), FILTER_SANITIZE_URL));
        }
        return [];
    }
}