<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use PDO;

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

    public function sendNotFound(): void
    {
        // Trả đúng HTTP status 404
        http_response_code(404);

        // Hiển thị giao diện trang 404
        $this->view('errors/404');
    }

    protected function adminView(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        ob_start();
        require __DIR__ . '/../Views/' . $view . '.php';
        $content = ob_get_clean();
        require __DIR__ . '/../Views/layouts/admin.php';
    }

    protected function providerView(string $view, array $data = []): void
    {
        // Chuyển dữ liệu thành các biến để View sử dụng
        extract($data, EXTR_SKIP);

        // Lấy nội dung trang Provider
        ob_start();
        require __DIR__ . '/../Views/' . $view . '.php';
        $content = ob_get_clean();

        // Hiển thị trong layout riêng của Provider
        require __DIR__ . '/../Views/layouts/provider.php';
    }

    protected function requireActiveUser(?string $requiredRole = null): array
    {
        // Kiểm tra đăng nhập
        if (empty($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        // Lấy thông tin tài khoản mới nhất từ database
        $db = Database::connect();
        $stmt = $db->prepare("
        SELECT user_id, role, status
        FROM users
        WHERE user_id = :user_id
        LIMIT 1
    ");
        $stmt->execute(['user_id' => (int) $_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Tài khoản không tồn tại hoặc không còn hoạt động
        if (!$user || $user['status'] !== 'Active') {
            unset($_SESSION['user_id'], $_SESSION['user_role']);
            session_regenerate_id(true);
            $_SESSION['login_error'] = [
                'message' => 'Tài khoản của bạn đã bị khóa hoặc không còn hoạt động.',
                'type' => 'danger'
            ];
            header('Location: /login');
            exit;
        }

        // Kiểm tra vai trò nếu chức năng yêu cầu
        if ($requiredRole !== null && $user['role'] !== $requiredRole) {
            http_response_code(403);
            exit('Bạn không có quyền truy cập chức năng này.');
        }

        return $user;
    }
}
