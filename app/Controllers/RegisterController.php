<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;

class RegisterController extends Controller
{
    public function showRegister(): void
    {
        // Tạo CSRF token nếu chưa có
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $this->view('auth/register');
    }

    public function register(): void
    {
        // Kiểm tra CSRF token khi gửi form
        $submittedToken = $_POST['csrf_token'] ?? '';

        if (
            !is_string($submittedToken) ||
            empty($_SESSION['csrf_token']) ||
            !hash_equals($_SESSION['csrf_token'], $submittedToken)
        ) {
            http_response_code(403);
            exit('Yêu cầu không hợp lệ. Vui lòng tải lại trang.');
        }


        // Phân luồng đăng ký theo vai trò
        $role = $_POST['role'] ?? '';

        if ($role === 'provider') {
            $this->registerProvider();
            return;
        }

        if ($role !== 'student') {
            http_response_code(400);
            exit('Loại tài khoản không hợp lệ.');
        }

        // Lấy thông tin từ form
        $fullName = trim($_POST['full_name'] ?? '');
        $studentCode = trim($_POST['student_code'] ?? '');
        $school = trim($_POST['school'] ?? '');
        $major = trim($_POST['major'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $cohort = trim($_POST['cohort'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['password_confirmation'] ?? '';

        // Kiểm tra các trường bắt buộc
        if (
            $fullName === '' || $school === '' ||
            $major === '' || $address === '' ||
            $cohort === '' || $phone === '' ||
            $email === '' || $password === ''
        ) {
            exit('Vui lòng nhập đầy đủ thông tin.');
        }

        // Kiểm tra định dạng email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            exit('Email không hợp lệ.');
        }

        // Kiểm tra xác nhận mật khẩu
        if ($password !== $confirmPassword) {
            exit('Mật khẩu xác nhận không khớp.');
        }


        // Mật khẩu phải có ít nhất 8 ký tự
        if (strlen($password) < 8) {
            exit('Mật khẩu phải có ít nhất 8 ký tự.');
        }

        // Mật khẩu phải có chữ hoa, chữ thường và chữ số
        if (
            !preg_match('/[a-z]/', $password) ||
            !preg_match('/[0-9]/', $password)
        ) {
            exit('Mật khẩu phải có chữ thường và chữ số.');
        }

        // Cohort phải là số nguyên dương
        if (
            filter_var($cohort, FILTER_VALIDATE_INT) === false ||
            (int) $cohort <= 0
        ) {
            exit('Khóa học không hợp lệ.');
        }

        // Kiểm tra độ dài thông tin theo giới hạn database
        if (
            mb_strlen($fullName) > 100 ||
            mb_strlen($studentCode) > 50 ||
            mb_strlen($school) > 150 ||
            mb_strlen($major) > 150 ||
            mb_strlen($address) > 255 ||
            mb_strlen($phone) > 20 ||
            strlen($email) > 255
        ) {
            exit('Thông tin nhập vào vượt quá độ dài cho phép.');
        }

        // Hash mật khẩu trước khi lưu database
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Kết nối database
        $db = Database::connect();

        // Kiểm tra email đã tồn tại chưa
        $stmt = $db->prepare("SELECT user_id FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);

        if ($stmt->fetch()) {
            exit('Email này đã được đăng ký.');
        }

        // Kiểm tra số điện thoại đã được đăng ký chưa
        $stmt = $db->prepare("
            SELECT user_id
            FROM students
            WHERE student_phone = :phone
            LIMIT 1
        ");

        $stmt->execute(['phone' => $phone]);

        if ($stmt->fetch()) {
            exit('Số điện thoại này đã được đăng ký.');
        }

        // Lưu tài khoản và thông tin sinh viên trong cùng một giao dịch
        try {
            $db->beginTransaction();

            // Tạo tài khoản Student
            $stmt = $db->prepare("
                INSERT INTO users (email, password_hash, role, status)
                VALUES (:email, :password_hash, 'Student', 'Active')
            ");

            $stmt->execute([
                'email' => $email,
                'password_hash' => $passwordHash
            ]);

            // Lấy ID tài khoản vừa tạo
            $userId = (int) $db->lastInsertId();

            // Lưu hồ sơ sinh viên gắn với tài khoản
            $stmt = $db->prepare("
                INSERT INTO students (
                    user_id, student_name, student_code,
                    student_school, student_major, student_address,
                    student_cohort, student_phone
                )
                VALUES (
                    :user_id, :student_name, :student_code,
                    :student_school, :student_major, :student_address,
                    :student_cohort, :student_phone
                )
            ");

            $stmt->execute([
                'user_id' => $userId,
                'student_name' => $fullName,
                'student_code' => $studentCode !== '' ? $studentCode : null,
                'student_school' => $school,
                'student_major' => $major,
                'student_address' => $address,
                'student_cohort' => (int) $cohort,
                'student_phone' => $phone
            ]);

            // Chỉ lưu chính thức khi cả hai bảng đều thành công
            $db->commit();

            // Chuyển về Trang chủ sau khi đăng ký thành công
            $_SESSION['register_success'] = true;
            header('Location: /login');
            exit;
        } catch (\PDOException $e) {
            // Hủy toàn bộ giao dịch nếu có lỗi
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            // Email có thể bị đăng ký đồng thời ở yêu cầu khác
            if ($e->getCode() === '23000') {
                exit('Email này đã được đăng ký hoặc dữ liệu bị trùng.');
            }

            // Không hiển thị chi tiết lỗi database cho người dùng
            error_log('[REGISTER ERROR] ' . $e->getMessage());
            http_response_code(500);
            exit('Không thể đăng ký tài khoản. Vui lòng thử lại.');
        }
    }


    private function registerProvider(): void
    {
        // Lấy và kiểm tra dữ liệu form
        $fields = ['provider_name', 'organization', 'email', 'contact_email', 'phone', 'provider_address', 'password', 'password_confirmation'];
        $data = [];

        foreach ($fields as $field) {
            $value = $_POST[$field] ?? '';
            if (!is_string($value)) {
                http_response_code(400);
                exit('Dữ liệu không hợp lệ.');
            }
            $data[$field] = trim($value);
        }

        $name = $data['provider_name'];
        $organization = $data['organization'];
        $email = $data['email'];
        $contactEmail = $data['contact_email'];
        $phone = $data['phone'];
        $address = $data['provider_address'];
        $password = $data['password'];

        if ($name === '' || $email === '' || $address === '' || $password === '') {
            exit('Vui lòng nhập đầy đủ thông tin bắt buộc.');
        }

        if (
            !filter_var($email, FILTER_VALIDATE_EMAIL) ||
            ($contactEmail !== '' && !filter_var($contactEmail, FILTER_VALIDATE_EMAIL))
        ) {
            exit('Email không hợp lệ.');
        }

        if ($password !== $data['password_confirmation']) {
            exit('Mật khẩu xác nhận không khớp.');
        }

        if (
            strlen($password) < 8 ||
            !preg_match('/[a-z]/', $password) ||
            !preg_match('/[0-9]/', $password)
        ) {
            exit('Mật khẩu phải có ít nhất 8 ký tự, chữ thường và chữ số.');
        }

        if (
            mb_strlen($name) > 255 || mb_strlen($organization) > 255 ||
            mb_strlen($address) > 255 || mb_strlen($phone) > 20 ||
            strlen($email) > 255 || strlen($contactEmail) > 255
        ) {
            exit('Thông tin vượt quá độ dài cho phép.');
        }

        // Kiểm tra file xác minh
        $file = $_FILES['verification_document'] ?? null;
        if (
            !is_array($file) || is_array($file['error'] ?? null) ||
            ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK
        ) {
            exit('Vui lòng tải lên giấy tờ xác minh hợp lệ.');
        }

        $tmpPath = $file['tmp_name'] ?? '';
        $fileSize = $file['size'] ?? 0;

        if (
            !is_string($tmpPath) || !is_uploaded_file($tmpPath) ||
            !is_int($fileSize) || $fileSize <= 0 || $fileSize > 5 * 1024 * 1024
        ) {
            exit('Giấy tờ xác minh phải nhỏ hơn hoặc bằng 5MB.');
        }

        // Xác định loại file từ nội dung, không tin đuôi file người dùng gửi
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($tmpPath);
        $extensions = [
            'application/pdf' => 'pdf',
            'image/jpeg' => 'jpg',
            'image/png' => 'png'
        ];

        if (!isset($extensions[$mime])) {
            exit('Chỉ chấp nhận file PDF, JPG hoặc PNG.');
        }

        $signature = file_get_contents($tmpPath, false, null, 0, 8);
        $validSignature = match ($mime) {
            'application/pdf' => str_starts_with((string)$signature, '%PDF-'),
            'image/jpeg' => str_starts_with((string)$signature, "\xFF\xD8\xFF"),
            'image/png' => str_starts_with((string)$signature, "\x89PNG\r\n\x1A\n"),
            default => false
        };

        if (!$validSignature) {
            exit('Nội dung giấy tờ xác minh không hợp lệ.');
        }

        // Lưu giấy tờ bên ngoài thư mục public
        $uploadDir = dirname(__DIR__, 2) . '/storage/provider_verifications';
        $filename = 'provider_' . bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
        $relativePath = 'provider_verifications/' . $filename;
        $savedPath = null;
        $db = Database::connect();

        try {
            // Kiểm tra email trùng
            $stmt = $db->prepare("SELECT user_id FROM users WHERE email = :email LIMIT 1");
            $stmt->execute(['email' => $email]);

            if ($stmt->fetch()) {
                exit('Email này đã được đăng ký.');
            }

            if (!is_dir($uploadDir) && !mkdir($uploadDir, 0700, true) && !is_dir($uploadDir)) {
                throw new \RuntimeException('Không thể tạo thư mục lưu giấy tờ.');
            }

            $db->beginTransaction();

            // Tạo tài khoản Provider ở trạng thái chờ duyệt
            $stmt = $db->prepare("
            INSERT INTO users (email, password_hash, role, status)
            VALUES (:email, :password_hash, 'Provider', 'Pending')
        ");
            $stmt->execute([
                'email' => $email,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT)
            ]);

            $userId = (int)$db->lastInsertId();

            if (!move_uploaded_file($tmpPath, $uploadDir . '/' . $filename)) {
                throw new \RuntimeException('Không thể lưu giấy tờ xác minh.');
            }
            $savedPath = $uploadDir . '/' . $filename;
            chmod($savedPath, 0600);

            // Lưu hồ sơ nhà cung cấp
            $stmt = $db->prepare("
            INSERT INTO providers (
                user_id, provider_name, provider_address,
                verification_document, organization,
                contact_email, provider_phone
            )
            VALUES (
                :user_id, :provider_name, :provider_address,
                :verification_document, :organization,
                :contact_email, :provider_phone
            )
        ");

            $stmt->execute([
                'user_id' => $userId,
                'provider_name' => $name,
                'provider_address' => $address,
                'verification_document' => $relativePath,
                'organization' => $organization !== '' ? $organization : null,
                'contact_email' => $contactEmail !== '' ? $contactEmail : null,
                'provider_phone' => $phone !== '' ? $phone : null
            ]);

            $db->commit();

            $_SESSION['provider_register_pending'] = true;
            header('Location: /login');
            exit;
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            // Xóa file nếu giao dịch thất bại
            if ($savedPath !== null && is_file($savedPath)) {
                unlink($savedPath);
            }

            error_log('[PROVIDER REGISTER ERROR] ' . $e->getMessage());

            if ($e instanceof \PDOException && $e->getCode() === '23000') {
                exit('Email đã tồn tại hoặc dữ liệu bị trùng.');
            }

            http_response_code(500);
            exit('Không thể đăng ký Provider. Vui lòng thử lại.');
        }
    }
}
