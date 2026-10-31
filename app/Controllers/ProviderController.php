<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Models\Scholarship;
use PDO;

class ProviderController extends Controller
{
    public function index(): void
    {
        // Chỉ Provider Active mới được truy cập
        $user = $this->requireActiveUser('Provider');

        $userId = (int) $user['user_id'];
        $db = Database::connect();

        // Lấy thông tin nhà cung cấp
        $stmt = $db->prepare("
            SELECT
                provider_name,
                organization,
                provider_logo
            FROM providers
            WHERE user_id = :user_id
            LIMIT 1
        ");

        $stmt->execute(['user_id' => $userId]);
        $provider = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$provider) {
            http_response_code(404);
            exit('Không tìm thấy thông tin nhà cung cấp.');
        }

        // Thống kê học bổng của nhà cung cấp
        $stmt = $db->prepare("
            SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) AS pending,
                SUM(CASE WHEN status = 'Published' THEN 1 ELSE 0 END) AS published,
                SUM(CASE WHEN status = 'Rejected' THEN 1 ELSE 0 END) AS rejected
            FROM scholarships
            WHERE user_id = :user_id
        ");

        $stmt->execute(['user_id' => $userId]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->providerView('provider/dashboard', [
            'provider' => $provider,
            'stats' => $stats
        ]);
    }

    // Hiển thị form đăng học bổng
    public function createScholarship(): void
    {
        $this->requireActiveUser('Provider');

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $this->providerView('provider/scholarships/create');
    }


    // Xử lý dữ liệu Provider gửi từ form
    public function storeScholarship(): void
    {
        $user = $this->requireActiveUser('Provider');

        // 1. Kiểm tra CSRF
        $token = $_POST['csrf_token'] ?? '';

        if (
            !is_string($token) ||
            empty($_SESSION['csrf_token']) ||
            !hash_equals($_SESSION['csrf_token'], $token)
        ) {
            http_response_code(403);
            exit('Yêu cầu không hợp lệ.');
        }

        // 2. Lấy và kiểm tra thông tin học bổng
        $fields = [
            'scholarship_name',
            'description',
            'amount',
            'start_date',
            'end_date',
            'required_gpa',
            'required_year',
            'required_major'
        ];

        $data = [];

        foreach ($fields as $field) {
            $value = $_POST[$field] ?? '';

            if (!is_string($value)) {
                http_response_code(400);
                exit('Dữ liệu không hợp lệ.');
            }

            $data[$field] = trim($value);
        }

        // Các trường bắt buộc
        foreach (
            [
                'scholarship_name',
                'description',
                'amount',
                'start_date',
                'end_date'
            ] as $field
        ) {
            if ($data[$field] === '') {
                http_response_code(422);
                exit('Vui lòng nhập đầy đủ thông tin học bổng.');
            }
        }

        // 3. Kiểm tra số tiền
        if (
            !ctype_digit($data['amount']) ||
            (float) $data['amount'] <= 0
        ) {
            http_response_code(422);
            exit('Giá trị học bổng phải là số dương.');
        }

        // 4. Kiểm tra ngày
        $start = \DateTimeImmutable::createFromFormat(
            '!Y-m-d',
            $data['start_date']
        );

        $end = \DateTimeImmutable::createFromFormat(
            '!Y-m-d',
            $data['end_date']
        );

        if (
            !$start ||
            !$end ||
            $start->format('Y-m-d') !== $data['start_date'] ||
            $end->format('Y-m-d') !== $data['end_date'] ||
            $end < $start
        ) {
            http_response_code(422);
            exit('Ngày bắt đầu hoặc hạn nộp không hợp lệ.');
        }

        // 5. Kiểm tra GPA nếu có nhập
        if ($data['required_gpa'] !== '') {
            if (
                !is_numeric($data['required_gpa']) ||
                (float) $data['required_gpa'] < 0 ||
                (float) $data['required_gpa'] > 4
            ) {
                http_response_code(422);
                exit('GPA phải nằm trong khoảng 0 đến 4.');
            }
        } else {
            $data['required_gpa'] = null;
        }

        // 6. Lấy danh sách giấy tờ
        $documentNames = $_POST['document_name'] ?? [];
        $documentDescriptions = $_POST['document_description'] ?? [];
        $documentRequired = $_POST['document_required'] ?? [];

        if (
            !is_array($documentNames) ||
            !is_array($documentDescriptions) ||
            !is_array($documentRequired) ||
            count($documentNames) > 30
        ) {
            http_response_code(400);
            exit('Danh sách giấy tờ không hợp lệ.');
        }

        $documents = [];

        foreach ($documentNames as $index => $name) {
            if (!is_string($name)) {
                http_response_code(400);
                exit('Tên giấy tờ không hợp lệ.');
            }

            $name = trim($name);

            if ($name === '') {
                continue;
            }

            $description = $documentDescriptions[$index] ?? '';

            if (!is_string($description)) {
                http_response_code(400);
                exit('Mô tả giấy tờ không hợp lệ.');
            }

            $documents[] = [
                'document_name' => $name,
                'description' => trim($description),
                'is_required' => isset($documentRequired[$index])
                    && (string) $documentRequired[$index] === '1'
            ];
        }

        // 7. Lưu vào database
        try {
            $scholarshipId = Scholarship::create(
                (int) $user['user_id'],
                $data,
                $documents
            );

            $_SESSION['provider_success'] =
                'Đăng học bổng thành công! Học bổng đang chờ Admin xét duyệt.';

            header('Location: /provider/scholarships');
            exit;
        } catch (\Throwable $e) {
            error_log('Create scholarship failed: ' . $e->getMessage());

            http_response_code(500);
            exit('Không thể đăng học bổng. Vui lòng thử lại.');
        }
    }

    // Trang quản lý học bổng của Provider
    public function scholarships(): void
    {
        $user = $this->requireActiveUser('Provider');

        $scholarships = Scholarship::getByProvider(
            (int) $user['user_id']
        );

        $success = $_SESSION['provider_success'] ?? null;
        unset($_SESSION['provider_success']);

        $this->providerView('provider/scholarships/index', [
            'scholarships' => $scholarships,
            'success' => $success
        ]);
    }

    // Xem chi tiết học bổng của Provider
    public function showScholarship(int $id): void
    {
        $user = $this->requireActiveUser('Provider');

        $scholarship = Scholarship::findByProvider(
            $id,
            (int) $user['user_id']
        );

        if (!$scholarship) {
            $this->sendNotFound();
            return;
        }

        $requirements = Scholarship::getRequirements($id);
        $documents = Scholarship::getRequiredDocuments($id);

        $this->providerView('provider/scholarships/show', [
            'scholarship' => $scholarship,
            'requirements' => $requirements,
            'documents' => $documents
        ]);
    }
}
