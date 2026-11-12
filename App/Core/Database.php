<?php

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

require_once __DIR__ . '/../Config/config.php';

class Database
{
    private static ?PDO $instance = null;

    private function __construct()
    {
    }
    public static function getConnection(): PDO
    {
        if (self::$instance !== null) {
            return self::$instance;
        }
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            DB_HOST,
            DB_PORT,
            DB_NAME
        );
        try {
            self::$instance = new PDO(
                $dsn,
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_TIMEOUT => 10,
                ]
            );
            return self::$instance;
        } catch (PDOException $e) {
            error_log('[GiveTake Database] ' . $e->getMessage());
            throw new RuntimeException(
                'Không thể kết nối cơ sở dữ liệu. Vui lòng thử lại sau.',
                0,
                $e
            );
        }
    }
}
