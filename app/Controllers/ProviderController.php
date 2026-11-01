<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Models\Scholarship;
use PDO;

class ProviderController extends Controller
{
    // Trang tổng quan Provider
    public function index(): void
    {
        $user = $this->requireActiveUser('Provider');

        $userId = (int) $user['user_id'];
        $db = Database::connect();

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

    // Xử lý đăng học bổng
    public function storeScholarship(): void
    {
        $user = $this->requireActiveUser('Provider');

        $token = $_POST['csrf_token'] ?? '';

        if (
            !is_string($token) ||
            empty($_SESSION['csrf_token']) ||
            !hash_equals($_SESSION['csrf_token'], $token)
        ) {
            http_response_code(403);
            exit('Yêu cầu không hợp lệ.');
        }

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

        if (mb_strlen($data['scholarship_name']) > 255) {
            http_response_code(422);
            exit('Tên học bổng quá dài.');
        }

        if (
            !ctype_digit($data['amount']) ||
            (float) $data['amount'] <= 0 ||
            strlen($data['amount']) > 15
        ) {
            http_response_code(422);
            exit('Giá trị học bổng không hợp lệ.');
        }

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

        if ($data['required_gpa'] === '') {
            $data['required_gpa'] = null;
        } elseif (
            !preg_match('/^\d+(?:\.\d{1,2})?$/', $data['required_gpa']) ||
            (float) $data['required_gpa'] > 4
        ) {
            http_response_code(422);
            exit('GPA phải từ 0 đến 4, tối đa 2 chữ số thập phân.');
        }

        if ($data['required_year'] === '') {
            $data['required_year'] = null;
        } elseif (
            !ctype_digit($data['required_year']) ||
            (int) $data['required_year'] < 1 ||
            (int) $data['required_year'] > 20
        ) {
            http_response_code(422);
            exit('Năm học phải là số nguyên từ 1 đến 20.');
        }

        if ($data['required_major'] === '') {
            $data['required_major'] = null;
        } elseif (mb_strlen($data['required_major']) > 255) {
            http_response_code(422);
            exit('Tên ngành học quá dài.');
        }

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
            $isRequired = $documentRequired[$index] ?? '0';

            if (
                !is_string($description) ||
                !is_string($isRequired) ||
                !in_array($isRequired, ['0', '1'], true) ||
                mb_strlen($name) > 255
            ) {
                http_response_code(422);
                exit('Thông tin giấy tờ không hợp lệ.');
            }

            $documents[] = [
                'document_name' => $name,
                'description' => trim($description),
                'is_required' => $isRequired === '1'
            ];
        }

        try {
            Scholarship::create(
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

    // Danh sách học bổng Provider
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

    // Xem chi tiết học bổng
    public function showScholarship(string $id): void
    {
        $user = $this->requireActiveUser('Provider');
        $id = (int) $id;

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

    // Hiển thị form chỉnh sửa học bổng bị từ chối
    public function editScholarship(string $id): void
    {
        $user = $this->requireActiveUser('Provider');
        $id = (int) $id;

        $scholarship = Scholarship::findByProvider(
            $id,
            (int) $user['user_id']
        );

        if (!$scholarship) {
            $this->sendNotFound();
            return;
        }

        if ($scholarship['status'] !== 'Rejected') {
            http_response_code(403);
            exit('Chỉ được chỉnh sửa học bổng đã bị từ chối.');
        }

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $this->providerView('provider/scholarships/edit', [
            'scholarship' => $scholarship,
            'requirements' => Scholarship::getRequirements($id),
            'documents' => Scholarship::getRequiredDocuments($id)
        ]);
    }

    // Lưu chỉnh sửa và gửi duyệt lại
    public function updateScholarship(string $id): void
    {
        $user = $this->requireActiveUser('Provider');

        $id = (int) $id;
        $providerId = (int) $user['user_id'];

        $token = $_POST['csrf_token'] ?? '';

        if (
            !is_string($token) ||
            empty($_SESSION['csrf_token']) ||
            !hash_equals($_SESSION['csrf_token'], $token)
        ) {
            http_response_code(403);
            exit('Yêu cầu không hợp lệ.');
        }

        $scholarship = Scholarship::findByProvider(
            $id,
            $providerId
        );

        if (!$scholarship) {
            $this->sendNotFound();
            return;
        }

        if ($scholarship['status'] !== 'Rejected') {
            http_response_code(403);
            exit('Học bổng này không được phép chỉnh sửa.');
        }

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

        if (mb_strlen($data['scholarship_name']) > 255) {
            http_response_code(422);
            exit('Tên học bổng quá dài.');
        }

        if (
            !ctype_digit($data['amount']) ||
            (float) $data['amount'] <= 0 ||
            strlen($data['amount']) > 15
        ) {
            http_response_code(422);
            exit('Giá trị học bổng không hợp lệ.');
        }

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

        if ($data['required_gpa'] === '') {
            $data['required_gpa'] = null;
        } elseif (
            !preg_match('/^\d+(?:\.\d{1,2})?$/', $data['required_gpa']) ||
            (float) $data['required_gpa'] > 4
        ) {
            http_response_code(422);
            exit('GPA phải từ 0 đến 4, tối đa 2 chữ số thập phân.');
        }

        if ($data['required_year'] === '') {
            $data['required_year'] = null;
        } elseif (
            !ctype_digit($data['required_year']) ||
            (int) $data['required_year'] < 1 ||
            (int) $data['required_year'] > 20
        ) {
            http_response_code(422);
            exit('Năm học phải là số nguyên từ 1 đến 20.');
        }

        if ($data['required_major'] === '') {
            $data['required_major'] = null;
        } elseif (mb_strlen($data['required_major']) > 255) {
            http_response_code(422);
            exit('Tên ngành học quá dài.');
        }

        $names = $_POST['document_name'] ?? [];
        $descriptions = $_POST['document_description'] ?? [];
        $required = $_POST['document_required'] ?? [];

        if (
            !is_array($names) ||
            !is_array($descriptions) ||
            !is_array($required) ||
            count($names) > 30
        ) {
            http_response_code(400);
            exit('Danh sách giấy tờ không hợp lệ.');
        }

        $documents = [];

        foreach ($names as $index => $name) {
            if (!is_string($name) || trim($name) === '') {
                http_response_code(422);
                exit('Tên giấy tờ không được để trống.');
            }

            $description = $descriptions[$index] ?? '';
            $isRequired = $required[$index] ?? '0';

            if (
                !is_string($description) ||
                !is_string($isRequired) ||
                !in_array($isRequired, ['0', '1'], true) ||
                mb_strlen(trim($name)) > 255
            ) {
                http_response_code(422);
                exit('Thông tin giấy tờ không hợp lệ.');
            }

            $documents[] = [
                'document_name' => trim($name),
                'description' => trim($description),
                'is_required' => $isRequired === '1'
            ];
        }

        try {
            $updated = Scholarship::resubmitRejected(
                $id,
                $providerId,
                $data,
                $documents
            );

            if (!$updated) {
                http_response_code(409);
                exit('Học bổng đã thay đổi trạng thái. Vui lòng tải lại trang.');
            }

            $_SESSION['provider_success'] =
                'Cập nhật thành công! Học bổng đã được gửi xét duyệt lại.';

            header('Location: /provider/scholarships');
            exit;
        } catch (\Throwable $e) {
            error_log('Resubmit scholarship failed: ' . $e->getMessage());

            http_response_code(500);
            exit('Không thể cập nhật học bổng. Vui lòng thử lại.');
        }
    }

    // Hiển thị thông tin nhà cung cấp
    public function profile(): void
    {
        $user = $this->requireActiveUser('Provider');

        $db = Database::connect();

        $stmt = $db->prepare("
        SELECT
            p.user_id,
            p.provider_name,
            p.organization,
            p.provider_address,
            p.contact_email,
            p.provider_phone,
            p.provider_logo,
            p.verification_document,
            u.email,
            u.status
        FROM providers p
        INNER JOIN users u ON u.user_id = p.user_id
        WHERE p.user_id = :user_id
        LIMIT 1
    ");

        $stmt->execute([
            'user_id' => (int) $user['user_id']
        ]);

        $provider = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$provider) {
            $this->sendNotFound();
            return;
        }

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $success = $_SESSION['provider_profile_success'] ?? null;
        unset($_SESSION['provider_profile_success']);

        $this->providerView('provider/profile', [
            'provider' => $provider,
            'success' => $success
        ]);
    }


    // Cập nhật thông tin nhà cung cấp
    public function updateProfile(): void
    {
        $user = $this->requireActiveUser('Provider');

        // Kiểm tra CSRF
        $token = $_POST['csrf_token'] ?? '';

        if (
            !is_string($token) ||
            empty($_SESSION['csrf_token']) ||
            !hash_equals($_SESSION['csrf_token'], $token)
        ) {
            http_response_code(403);
            exit('Yêu cầu không hợp lệ.');
        }

        $userId = (int) $user['user_id'];
        $db = Database::connect();

        // Kiểm tra hồ sơ Provider
        $stmt = $db->prepare("
        SELECT provider_logo
        FROM providers
        WHERE user_id = :user_id
        LIMIT 1
    ");

        $stmt->execute(['user_id' => $userId]);
        $provider = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$provider) {
            $this->sendNotFound();
            return;
        }

        // Chỉ cho phép sửa 3 trường này
        $fields = [
            'provider_address',
            'contact_email',
            'provider_phone'
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

        // Kiểm tra địa chỉ
        if (
            $data['provider_address'] === '' ||
            mb_strlen($data['provider_address']) > 255
        ) {
            http_response_code(422);
            exit('Địa chỉ nhà cung cấp không hợp lệ.');
        }

        // Kiểm tra email liên hệ (có thể để trống)
        if (
            $data['contact_email'] !== '' &&
            (
                mb_strlen($data['contact_email']) > 255 ||
                !filter_var($data['contact_email'], FILTER_VALIDATE_EMAIL)
            )
        ) {
            http_response_code(422);
            exit('Email liên hệ không hợp lệ.');
        }

        // Kiểm tra số điện thoại
        if (
            $data['provider_phone'] !== '' &&
            (
                mb_strlen($data['provider_phone']) > 20 ||
                !preg_match('/^[0-9+\s().-]{9,20}$/', $data['provider_phone'])
            )
        ) {
            http_response_code(422);
            exit('Số điện thoại không hợp lệ.');
        }

        $logoPath = $provider['provider_logo'];
        $newLogoFile = null;

        // Xử lý logo mới nếu có
        if (
            isset($_FILES['provider_logo']) &&
            $_FILES['provider_logo']['error'] !== UPLOAD_ERR_NO_FILE
        ) {
            $file = $_FILES['provider_logo'];

            if (
                !is_array($file) ||
                is_array($file['error'] ?? null) ||
                $file['error'] !== UPLOAD_ERR_OK ||
                !is_string($file['tmp_name'] ?? null) ||
                !is_uploaded_file($file['tmp_name'])
            ) {
                http_response_code(422);
                exit('Không thể tải logo lên.');
            }

            // Tối đa 2 MB
            if (
                !is_int($file['size'] ?? null) ||
                $file['size'] <= 0 ||
                $file['size'] > 2 * 1024 * 1024
            ) {
                http_response_code(422);
                exit('Logo phải có dung lượng tối đa 2 MB.');
            }

            // Kiểm tra định dạng ảnh thực tế
            $mime = (new \finfo(FILEINFO_MIME_TYPE))
                ->file($file['tmp_name']);

            $allowedTypes = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp'
            ];

            if (!isset($allowedTypes[$mime])) {
                http_response_code(422);
                exit('Logo chỉ hỗ trợ JPG, PNG hoặc WEBP.');
            }

            // Kiểm tra file có thực sự là ảnh
            if (@getimagesize($file['tmp_name']) === false) {
                http_response_code(422);
                exit('File logo không phải ảnh hợp lệ.');
            }

            $uploadDir = dirname(__DIR__, 2)
                . '/public/assets/uploads/provider-logos/';

            if (
                !is_dir($uploadDir) &&
                !mkdir($uploadDir, 0755, true) &&
                !is_dir($uploadDir)
            ) {
                http_response_code(500);
                exit('Không thể tạo thư mục lưu logo.');
            }

            $fileName = 'provider_logo_'
                . bin2hex(random_bytes(16))
                . '.'
                . $allowedTypes[$mime];

            $newLogoFile = $uploadDir . $fileName;

            if (!move_uploaded_file($file['tmp_name'], $newLogoFile)) {
                http_response_code(500);
                exit('Không thể lưu logo.');
            }

            $logoPath = '/assets/uploads/provider-logos/' . $fileName;
        }

        // Cập nhật database
        try {
            $stmt = $db->prepare("
            UPDATE providers
            SET
                provider_address = :provider_address,
                contact_email = :contact_email,
                provider_phone = :provider_phone,
                provider_logo = :provider_logo
            WHERE user_id = :user_id
        ");

            $stmt->execute([
                'provider_address' => $data['provider_address'],
                'contact_email' => $data['contact_email'] !== ''
                    ? $data['contact_email']
                    : null,
                'provider_phone' => $data['provider_phone'] !== ''
                    ? $data['provider_phone']
                    : null,
                'provider_logo' => $logoPath,
                'user_id' => $userId
            ]);

            $_SESSION['provider_profile_success'] =
                'Cập nhật thông tin nhà cung cấp thành công!';

            header('Location: /provider/profile');
            exit;
        } catch (\Throwable $e) {
            // Nếu cập nhật DB thất bại, xóa ảnh mới vừa upload
            if ($newLogoFile !== null && is_file($newLogoFile)) {
                unlink($newLogoFile);
            }

            error_log('Update provider profile failed: ' . $e->getMessage());

            http_response_code(500);
            exit('Không thể cập nhật thông tin. Vui lòng thử lại.');
        }
    }

    // Hiển thị trang cài đặt tài khoản Provider
    public function settings(): void
    {
        $user = $this->requireActiveUser('Provider');

        $db = Database::connect();

        $stmt = $db->prepare("
        SELECT email, status
        FROM users
        WHERE user_id = :user_id
        LIMIT 1
    ");

        $stmt->execute([
            'user_id' => (int) $user['user_id']
        ]);

        $account = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$account) {
            $this->sendNotFound();
            return;
        }

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $success = $_SESSION['provider_settings_success'] ?? null;
        $error = $_SESSION['provider_settings_error'] ?? null;

        unset(
            $_SESSION['provider_settings_success'],
            $_SESSION['provider_settings_error']
        );

        $this->providerView('provider/settings', [
            'account' => $account,
            'success' => $success,
            'error' => $error
        ]);
    }


    // Xử lý đổi mật khẩu Provider
    public function updatePassword(): void
    {
        $user = $this->requireActiveUser('Provider');

        $token = $_POST['csrf_token'] ?? '';

        if (
            !is_string($token) ||
            !isset($_SESSION['csrf_token']) ||
            !is_string($_SESSION['csrf_token']) ||
            !hash_equals($_SESSION['csrf_token'], $token)
        ) {
            http_response_code(403);
            exit('Yêu cầu không hợp lệ.');
        }

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (
            !is_string($currentPassword) ||
            !is_string($newPassword) ||
            !is_string($confirmPassword)
        ) {
            http_response_code(400);
            exit('Dữ liệu không hợp lệ.');
        }

        $redirectError = static function (string $message): void {
            $_SESSION['provider_settings_error'] = $message;
            header('Location: /provider/settings');
            exit;
        };

        if (
            $currentPassword === '' ||
            $newPassword === '' ||
            $confirmPassword === ''
        ) {
            $redirectError('Vui lòng nhập đầy đủ các trường mật khẩu.');
        }

        if (strlen($newPassword) < 8 || strlen($newPassword) > 72) {
            $redirectError('Mật khẩu mới phải có từ 8 đến 72 byte.');
        }

        if (!hash_equals($newPassword, $confirmPassword)) {
            $redirectError('Xác nhận mật khẩu mới không khớp.');
        }

        $db = Database::connect();

        try {
            $db->beginTransaction();

            $stmt = $db->prepare("
            SELECT password_hash
            FROM users
            WHERE user_id = :user_id
              AND role = 'Provider'
              AND status = 'Active'
            LIMIT 1
            FOR UPDATE
        ");

            $stmt->execute([
                'user_id' => (int) $user['user_id']
            ]);

            $account = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$account) {
                $db->rollBack();
                $redirectError('Tài khoản không còn hợp lệ.');
            }

            if (!password_verify(
                $currentPassword,
                $account['password_hash']
            )) {
                $db->rollBack();
                $redirectError('Mật khẩu hiện tại không chính xác.');
            }

            if (password_verify(
                $newPassword,
                $account['password_hash']
            )) {
                $db->rollBack();
                $redirectError('Mật khẩu mới phải khác mật khẩu hiện tại.');
            }

            $newHash = password_hash(
                $newPassword,
                PASSWORD_DEFAULT
            );

            $stmt = $db->prepare("
            UPDATE users
            SET password_hash = :password_hash
            WHERE user_id = :user_id
        ");

            $stmt->execute([
                'password_hash' => $newHash,
                'user_id' => (int) $user['user_id']
            ]);

            $db->commit();

            // Đổi session ID sau khi thay đổi thông tin bảo mật
            session_regenerate_id(true);

            $_SESSION['provider_settings_success'] =
                'Đổi mật khẩu thành công!';

            header('Location: /provider/settings');
            exit;
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            error_log('Update Provider password failed: ' . $e->getMessage());

            http_response_code(500);
            exit('Không thể đổi mật khẩu. Vui lòng thử lại.');
        }
    }
}
