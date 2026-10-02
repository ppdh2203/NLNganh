<?php

// Khởi động project: autoload + nạp biến từ .env
require_once __DIR__ . '/bootstrap.php';
use App\Core\Database;

try {
    // Gọi hàm connect() để thử kết nối MySQL
    $db = Database::connect();

    echo "Kết nối database thành công!";
} catch (Exception $e) {
    echo "Kết nối database thất bại!";
}