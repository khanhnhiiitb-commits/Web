
<?php
session_start();

require_once __DIR__ . '/App/Config/config.php';

// Tự động nạp class thuộc namespace App
spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/App/';

    $len = strlen($prefix);

    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);

    $file = $baseDir
        . str_replace('\\', '/', $relativeClass)
        . '.php';

    if (is_file($file)) {
        require_once $file;
    }
});

// Đọc controller và action từ URL
$controllerParam = $_GET['controller'] ?? 'Home';
$actionParam = $_GET['action'] ?? 'index';

$controllerName = ucfirst((string)$controllerParam) . 'Controller';
$actionName = (string)$actionParam;

// Chỉ cho phép tên controller/action hợp lệ
if (
    !preg_match('/^[A-Za-z][A-Za-z0-9_]*$/', $controllerParam)
    || !preg_match('/^[A-Za-z][A-Za-z0-9_]*$/', $actionName)
) {
    http_response_code(400);
    exit('Controller hoặc action không hợp lệ.');
}

$controllerClass = 'App\\Controllers\\' . $controllerName;

// Kiểm tra controller
if (!class_exists($controllerClass)) {
    http_response_code(404);

    exit(
        'Lỗi 404: Không tìm thấy Controller '
        . htmlspecialchars($controllerName, ENT_QUOTES, 'UTF-8')
    );
}

// Khởi tạo controller
$controller = new $controllerClass();

// Kiểm tra action có thể gọi
if (
    !method_exists($controller, $actionName)
    || !is_callable([$controller, $actionName])
    || str_starts_with($actionName, '__')
) {
    http_response_code(404);

    exit(
        'Lỗi 404: Không tìm thấy phương thức '
        . htmlspecialchars($actionName, ENT_QUOTES, 'UTF-8')
        . ' trong '
        . htmlspecialchars($controllerName, ENT_QUOTES, 'UTF-8')
    );
}

// Gọi action
$controller->$actionName();
