<?php

// Cấu hình thông số Database
define('DB_HOST', '26.225.165.255');
define('DB_PORT', '3306');
define('DB_NAME', 'reuse_sharing_db'); 
define('DB_USER', 'root');
define('DB_PASS', ''); // Mặc định XAMPP không có mật khẩu

// Cấu hình URL gốc của website
define('BASE_URL', 'http://localhost/doan/');

try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    
    // Cấu hình báo lỗi và kiểu dữ liệu trả về
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Kết nối CSDL thất bại: " . $e->getMessage());
}