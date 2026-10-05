<?php
namespace App\Core;

class Controller {
    protected function model(string $modelName) {
        $class = "App\\Models\\" . $modelName;
        return new $class();
    }

    protected function view(string $viewPath, array $data = []): void {
        extract($data); 
        $file = __DIR__ . "/../Views/" . $viewPath . ".php";
        if (file_exists($file)) {
            require_once $file;
        } else {
            die("Không tìm thấy file giao diện: {$viewPath}.php");
        }
    }

    protected function jsonResponse(array $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}