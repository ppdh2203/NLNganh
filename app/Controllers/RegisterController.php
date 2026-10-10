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


        // Chỉ xử lý đăng ký Student ở bước này
        if (($_POST['role'] ?? '') !== 'student') {
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
}