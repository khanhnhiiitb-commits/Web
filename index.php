<?php
session_start();

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    
    $base_dir = __DIR__ . '/App/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

$controllerName = ucfirst($_GET['controller'] ?? 'Home') . 'Controller';
$actionName     = $_GET['action'] ?? 'index';

$controllerClass = "App\\Controllers\\" . $controllerName;

if (class_exists($controllerClass)) {
    $controller = new $controllerClass();
    if (method_exists($controller, $actionName)) {
        $controller->$actionName();
    } else {
        echo "<h1>Lỗi 404: Không tìm thấy phương thức '{$actionName}' trong '{$controllerName}'.</h1>";
    }
} else {
    echo "<h1>Lỗi 404: Không tìm thấy Controller '{$controllerName}'.</h1>";
}