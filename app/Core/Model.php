<?php
namespace App\Core;

use PDO;
use PDOException;

/**
 * Base Model Class (MySQL Version)
 */
class Model
{
    protected static $db = null;

    public function __construct()
    {
        if (self::$db === null) {
            self::$db = $this->connect();
        }
    }

    private function connect()
    {
        // 1. Nạp cấu hình từ file database.php
        $config = require __DIR__ . '/../../config/database.php';

        try {
            // 2. Tạo chuỗi kết nối (DSN) cho MySQL
            // Thay đổi quan trọng: dùng mysql:host=... và thêm charset=utf8mb4
            $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4";
            
            // Nếu bạn dùng port khác 3306 (ví dụ MAMP dùng 8889), hãy thêm ;port=... vào $dsn
            // Ví dụ: ...;dbname={$config['dbname']};port=3306;charset...

            $pdo = new PDO($dsn, $config['username'], $config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false, // Tắt giả lập để bảo mật tốt hơn
            ]);

            return $pdo;

        } catch (PDOException $e) {
            // Ghi log lỗi (không show lỗi chi tiết ra màn hình người dùng production)
            error_log("Database connection failed: " . $e->getMessage());
            die("Lỗi kết nối Database. Vui lòng kiểm tra file config/database.php");
        }
    }

    protected function getDb()
    {
        return self::$db;
    }
}