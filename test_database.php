<?php

// Nạp autoload để PHP tìm được class Database
require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Database;

try {
    // Gọi hàm connect() để thử kết nối MySQL
    $db = Database::connect();

    echo "Kết nối database thành công!";
} catch (Exception $e) {
    echo "Kết nối database thất bại!";
}