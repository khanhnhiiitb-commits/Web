<?php
namespace App\Core; // Đổi lại thành Core vì Database là nền tảng hệ thống

use PDO;
use PDOException;

class Database {

    private static ?PDO $instance = null;
    
    private function __construct() {}

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            // Lấy trực tiếp HẰNG SỐ (DB_HOST, DB_PORT...) để nối chuỗi
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            try {
                // Truyền DB_USER và DB_PASS vào PDO
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Báo lỗi nếu SQL sai
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Luôn trả dữ liệu về dạng mảng
                    PDO::ATTR_EMULATE_PREPARES => false // Bảo mật: Chống SQL Injection
                ]);
            } catch (PDOException $e) {
                die("<h1>🚨 Lỗi kết nối Cơ sở dữ liệu:</h1> <p>" . $e->getMessage() . "</p> <p>Vui lòng kiểm tra lại thông tin Host, Username, Password trong file App/Config/config.php.</p>");
            }
        }
        return self::$instance;
    }
}