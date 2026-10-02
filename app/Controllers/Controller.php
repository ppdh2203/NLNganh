<?php

declare(strict_types=1);

namespace App\Controllers;

class Controller
{
    protected function view(string $view, array $data = []): void
    {
        // Chuyển dữ liệu trong mảng thành biến để View sử dụng
        extract($data);

        // Bắt đầu lưu nội dung của View thay vì gửi ngay ra trình duyệt
        ob_start();

        // Nạp View được Controller yêu cầu
        require __DIR__ . '/../Views/' . $view . '.php';

        // Lấy nội dung View vừa tạo và lưu vào biến $content
        $content = ob_get_clean();

        // Đưa $content vào khung giao diện chung
        require __DIR__ . '/../Views/layouts/main.php';
    }
}