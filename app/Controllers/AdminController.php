<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use PDO;

class AdminController extends Controller
{
    public function index(): void
    {
        // Kiểm tra quyền Admin
        $this->requireActiveUser('Admin');
        $db = Database::connect();

        // Thống kê tài khoản
        $stmt = $db->query("
            SELECT
                SUM(CASE WHEN role = 'Student' THEN 1 ELSE 0 END) AS students,
                SUM(CASE WHEN role = 'Provider' THEN 1 ELSE 0 END) AS providers,
                SUM(CASE WHEN role = 'Provider' AND status = 'Pending' THEN 1 ELSE 0 END) AS pending_providers
            FROM users
        ");
        $usersStats = $stmt->fetch(PDO::FETCH_ASSOC);

        // Thống kê học bổng
        $totalScholarships = (int) $db->query("
            SELECT COUNT(*) FROM scholarships
        ")->fetchColumn();

        // Tổng hợp số liệu Dashboard
        $stats = [
            'students' => (int) ($usersStats['students'] ?? 0),
            'providers' => (int) ($usersStats['providers'] ?? 0),
            'scholarships' => $totalScholarships,
            'pending' => (int) ($usersStats['pending_providers'] ?? 0)
        ];

        // Lấy danh sách tài khoản Student và Provider
        $stmt = $db->query("
            SELECT
                u.user_id,
                u.email,
                u.role,
                u.status,
                u.created_at,
                CASE
                    WHEN u.role = 'Student' THEN s.student_name
                    WHEN u.role = 'Provider' THEN p.provider_name
                END AS display_name
            FROM users u
            LEFT JOIN students s ON s.user_id = u.user_id
            LEFT JOIN providers p ON p.user_id = u.user_id
            WHERE u.role IN ('Student', 'Provider')
            ORDER BY u.created_at DESC, u.user_id DESC
        ");
        $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Hiển thị Dashboard Admin
        $this->adminView('admin/dashboard', [
            'stats' => $stats,
            'accounts' => $accounts
        ]);
    }

    public function toggleAccountStatus(): void
    {
        // Kiểm tra quyền Admin
        $this->requireActiveUser('Admin');
        $db = Database::connect();

        // Kiểm tra CSRF
        $token = $_POST['csrf_token'] ?? '';
        if (
            !is_string($token) ||
            !isset($_SESSION['csrf_token']) ||
            !is_string($_SESSION['csrf_token']) ||
            !hash_equals($_SESSION['csrf_token'], $token)
        ) {
            http_response_code(403);
            exit('Phiên làm việc không hợp lệ.');
        }

        // Kiểm tra ID tài khoản
        $userId = filter_var($_POST['user_id'] ?? null, FILTER_VALIDATE_INT);
        if (!$userId || $userId === (int) $_SESSION['user_id']) {
            http_response_code(400);
            exit('Tài khoản không hợp lệ.');
        }

        // Lấy thông tin tài khoản cần thay đổi
        $stmt = $db->prepare("
            SELECT role, status
            FROM users
            WHERE user_id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $userId]);
        $account = $stmt->fetch(PDO::FETCH_ASSOC);

        // Chỉ cho phép thao tác với Student và Provider
        if (!$account || !in_array($account['role'], ['Student', 'Provider'], true)) {
            http_response_code(400);
            exit('Không thể thay đổi tài khoản này.');
        }

        // Chỉ chuyển đổi Active và Locked
        $newStatus = match ($account['status']) {
            'Active' => 'Locked',
            'Locked' => 'Active',
            default => null
        };

        if ($newStatus === null) {
            http_response_code(400);
            exit('Trạng thái tài khoản không cho phép thao tác.');
        }

        // Cập nhật trạng thái tài khoản
        $stmt = $db->prepare("
            UPDATE users
            SET status = :new_status
            WHERE user_id = :id AND status = :old_status
        ");
        $stmt->execute([
            'new_status' => $newStatus,
            'id' => $userId,
            'old_status' => $account['status']
        ]);

        // Quay lại tab Quản lý tài khoản
        header('Location: /admin?tab=users');
        exit;
    }
}
