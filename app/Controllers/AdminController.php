<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use PDO;
use App\Models\Scholarship;

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
            AND u.status IN ('Active', 'Locked')
            ORDER BY u.created_at DESC, u.user_id DESC
        ");
        $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Danh sách nhà cung cấp đang chờ duyệt

        // Lấy hồ sơ nhà cung cấp theo trạng thái xét duyệt
        $stmt = $db->query("
            SELECT
                u.user_id,
                u.email,
                u.status,
                u.created_at,
                p.provider_name,
                p.provider_logo,
                p.organization,
                p.contact_email,
                p.provider_phone,
                p.provider_address,
                p.verification_document
            FROM users u
            INNER JOIN providers p ON p.user_id = u.user_id
            WHERE u.role = 'Provider'
              AND u.status IN ('Pending', 'Active', 'Rejected')
            ORDER BY u.created_at DESC, u.user_id DESC
        ");

        $allProviders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $pendingProviders = [];
        $approvedProviders = [];
        $rejectedProviders = [];

        foreach ($allProviders as $provider) {
            switch ($provider['status']) {
                case 'Pending':
                    $pendingProviders[] = $provider;
                    break;
                case 'Active':
                    $approvedProviders[] = $provider;
                    break;
                case 'Rejected':
                    $rejectedProviders[] = $provider;
                    break;
            }
        }

        // Danh sách học bổng theo trạng thái xét duyệt
        $pendingScholarships = Scholarship::getByStatus('Pending');
        $publishedScholarships = Scholarship::getByStatus('Published');
        $rejectedScholarships = Scholarship::getByStatus('Rejected');

        // Hiển thị Dashboard Admin
        $this->adminView('admin/dashboard', [
            'stats' => $stats,
            'accounts' => $accounts,
            'pendingProviders' => $pendingProviders,
            'approvedProviders' => $approvedProviders,
            'rejectedProviders' => $rejectedProviders,
            'pendingScholarships' => $pendingScholarships,
            'publishedScholarships' => $publishedScholarships,
            'rejectedScholarships' => $rejectedScholarships
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


    public function viewProviderDocument(int $id): void
    {
        // Chỉ Admin đang hoạt động mới được xem
        $this->requireActiveUser('Admin');

        $db = Database::connect();

        // Lấy đường dẫn tài liệu từ database
        $stmt = $db->prepare("
        SELECT p.verification_document
        FROM providers p
        INNER JOIN users u ON u.user_id = p.user_id
        WHERE p.user_id = :id
          AND u.role = 'Provider'
        LIMIT 1
    ");
        $stmt->execute(['id' => $id]);
        $document = $stmt->fetchColumn();

        if (!is_string($document) || $document === '') {
            http_response_code(404);
            exit('Không tìm thấy tài liệu xác minh.');
        }

        // Thư mục riêng tư chứa giấy tờ xác minh
        $storageDir = realpath(
            dirname(__DIR__, 2) . '/storage/provider_verifications'
        );

        if ($storageDir === false) {
            http_response_code(404);
            exit('Không tìm thấy thư mục tài liệu.');
        }

        // Chỉ chấp nhận tên file, không cho truy cập đường dẫn tùy ý
        $fileName = basename(str_replace('\\', '/', $document));
        $filePath = realpath($storageDir . DIRECTORY_SEPARATOR . $fileName);

        if (
            $filePath === false ||
            dirname($filePath) !== $storageDir ||
            !is_file($filePath) ||
            !is_readable($filePath)
        ) {
            http_response_code(404);
            exit('Tài liệu không tồn tại.');
        }

        // Kiểm tra định dạng thực tế của file
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($filePath);

        $allowedTypes = [
            'application/pdf',
            'image/jpeg',
            'image/png'
        ];

        if (!in_array($mime, $allowedTypes, true)) {
            http_response_code(415);
            exit('Định dạng tài liệu không được hỗ trợ.');
        }

        // Hiển thị file trực tiếp trong trình duyệt
        header('Content-Type: ' . $mime);
        header('Content-Disposition: inline; filename="verification.' .
            match ($mime) {
                'application/pdf' => 'pdf',
                'image/jpeg' => 'jpg',
                'image/png' => 'png'
            } . '"');
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, no-store');
        header('Content-Length: ' . filesize($filePath));

        readfile($filePath);
        exit;
    }


    public function reviewProvider(): void
    {
        // Chỉ Admin đang hoạt động mới được xử lý
        $this->requireActiveUser('Admin');

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

        // Lấy ID nhà cung cấp và quyết định duyệt
        $providerId = filter_var(
            $_POST['provider_id'] ?? null,
            FILTER_VALIDATE_INT
        );

        $action = $_POST['action'] ?? '';

        if (
            !$providerId ||
            !is_string($action) ||
            !in_array($action, ['approve', 'reject'], true)
        ) {
            http_response_code(400);
            exit('Yêu cầu không hợp lệ.');
        }

        // Xác định trạng thái mới
        $newStatus = $action === 'approve'
            ? 'Active'
            : 'Rejected';

        $db = Database::connect();

        // Chỉ cập nhật Provider đang Pending
        $stmt = $db->prepare("
        UPDATE users
        SET status = :new_status
        WHERE user_id = :provider_id
          AND role = 'Provider'
          AND status = 'Pending'
          AND EXISTS (
              SELECT 1
              FROM providers p
              WHERE p.user_id = users.user_id
          )
    ");

        $stmt->execute([
            'new_status' => $newStatus,
            'provider_id' => $providerId
        ]);

        if ($stmt->rowCount() === 0) {
            http_response_code(409);
            exit('Hồ sơ không tồn tại hoặc đã được xử lý.');
        }

        // Quay lại đúng tab Duyệt nhà cung cấp
        header('Location: /admin?tab=providers');
        exit;
    }

    public function reviewScholarship(): void
    {
        // 1. Chỉ Admin đang hoạt động mới được xét duyệt
        $this->requireActiveUser('Admin');

        // 2. Kiểm tra CSRF
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

        // 3. Kiểm tra ID học bổng và hành động
        $scholarshipId = filter_var(
            $_POST['scholarship_id'] ?? null,
            FILTER_VALIDATE_INT
        );

        $action = $_POST['action'] ?? '';

        if (
            !$scholarshipId ||
            $scholarshipId <= 0 ||
            !is_string($action) ||
            !in_array($action, ['approve', 'reject'], true)
        ) {
            http_response_code(400);
            exit('Yêu cầu xét duyệt không hợp lệ.');
        }

        // 4. Cập nhật trạng thái thông qua Model
        $updated = Scholarship::review($scholarshipId, $action);

        if (!$updated) {
            http_response_code(409);
            exit('Học bổng không tồn tại, đã được xử lý hoặc nhà cung cấp không hợp lệ.');
        }

        // 5. Quay lại trang Duyệt học bổng
        header('Location: /admin?tab=reviews');
        exit;
    }
}
