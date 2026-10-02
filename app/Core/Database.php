<?php

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $connection = null;

    public static function connect(): PDO
    {
        // Nếu đã kết nối rồi thì dùng lại kết nối cũ
        if (self::$connection !== null) {
            return self::$connection;
        }

        // Lấy thông tin cấu hình database
        $config = require __DIR__ . '/../../config/database.php';

        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset={$config['charset']}";

        try {
            // Tạo kết nối PDO tới MySQL
            self::$connection = new PDO(
                $dsn,
                $config['username'],
                $config['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );

            return self::$connection;

        } catch (PDOException $e) {
            // Không hiển thị chi tiết lỗi DB ra ngoài website
            throw new PDOException('Không thể kết nối đến cơ sở dữ liệu.');
        }
    }
}