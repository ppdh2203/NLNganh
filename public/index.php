<?php

declare(strict_types=1);

// Khởi động hệ thống: nạp autoload và biến môi trường .env
require_once __DIR__ . '/../bootstrap.php';

use Bramus\Router\Router;

// Tạo Router
$router = new Router();

// Nạp danh sách route của hệ thống
require_once __DIR__ . '/../routes/web.php';

// Bắt đầu xử lý route
$router->run();
