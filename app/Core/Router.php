<?php

namespace App\Core;

class Router
{
    // Danh sách các route GET
    private array $getRoutes = [];

    // Đăng ký một route GET
    public function get(string $path, callable $callback): void
    {
        $this->getRoutes[$path] = $callback;
    }

    // Tìm và chạy route phù hợp với URL hiện tại
    public function dispatch(string $uri): void
    {
        // Nếu route tồn tại thì chạy hàm tương ứng
        if (isset($this->getRoutes[$uri])) {
            $this->getRoutes[$uri]();
            return;
        }

        // Không tìm thấy route
        http_response_code(404);
        echo "404 - Không tìm thấy trang";
    }
}