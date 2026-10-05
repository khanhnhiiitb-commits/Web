<?php
namespace App\Config;

use PDO;
use PDOException;

class Database {

    
    private string $host = "localhost"; 
    private string $port = "3306";
    private string $db_name = "reuse_sharing_db"; // Tên CSDL nhóm thống nhất
    private string $username = "root";            // Mặc định XAMPP là root
    private string $password = "";                // Mặc định XAMPP không có mật khẩu

    private static ?PDO $instance = null;

    private function __construct() {}

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $db = new self();
            $dsn = "mysql:host={$db->host};port={$db->port};dbname={$db->db_name};charset=utf8mb4";
            try {
                self::$instance = new PDO($dsn, $db->username, $db->password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Báo lỗi nếu SQL sai
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Luôn trả dữ liệu về dạng mảng
                    PDO::ATTR_EMULATE_PREPARES => false // Bảo mật: Chống SQL Injection
                ]);
            } catch (PDOException $e) {
                die("<h1>🚨 Lỗi kết nối Cơ sở dữ liệu:</h1> <p>" . $e->getMessage() . "</p> <p>Vui lòng kiểm tra lại thông tin Host, Username, Password trong file app/Config/Database.php.</p>");
            }
        }
        return self::$instance;
    }
}