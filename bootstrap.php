<?php

declare(strict_types=1);

// Nạp Composer Autoload để sử dụng các thư viện và class trong project
require_once __DIR__ . '/vendor/autoload.php';

// Đọc các biến môi trường từ file .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();