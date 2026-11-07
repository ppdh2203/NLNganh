<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Student;
use PDOException;
use App\Core\Database;
use PDO;

class StudentController extends Controller
{
    public function profile(): void
    {
        $user = $this->requireActiveUser('Student');

        $student = Student::findByUserId((int) $user['user_id']);

        if ($student === null) {
            $this->sendNotFound();
            return;
        }

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $success = $_SESSION['student_profile_success'] ?? null;
        $error = $_SESSION['student_profile_error'] ?? null;

        unset(
            $_SESSION['student_profile_success'],
            $_SESSION['student_profile_error']
        );

        $this->view('student/profile', [
            'student' => $student,
            'success' => $success,
            'error' => $error
        ]);
    }

    public function updateProfile(): void
    {
        $user = $this->requireActiveUser('Student');
        $userId = (int) $user['user_id'];

        $token = $_POST['csrf_token'] ?? '';

        if (
            !is_string($token) ||
            empty($_SESSION['csrf_token']) ||
            !hash_equals($_SESSION['csrf_token'], $token)
        ) {
            http_response_code(403);
            exit('Yêu cầu không hợp lệ.');
        }

        $student = Student::findByUserId($userId);

        if ($student === null) {
            $this->sendNotFound();
            return;
        }

        $fields = [
            'student_name',
            'student_school',
            'student_major',
            'student_cohort',
            'student_address',
            'student_phone'
        ];

        $data = [];

        foreach ($fields as $field) {
            $value = $_POST[$field] ?? '';

            if (!is_string($value)) {
                $this->redirectWithError('Dữ liệu không hợp lệ.');
            }

            $data[$field] = trim($value);

            if ($data[$field] === '') {
                $this->redirectWithError(
                    'Vui lòng nhập đầy đủ thông tin bắt buộc.'
                );
            }
        }

        if (
            mb_strlen($data['student_name']) > 255 ||
            mb_strlen($data['student_school']) > 255 ||
            mb_strlen($data['student_major']) > 255 ||
            mb_strlen($data['student_address']) > 255 ||
            mb_strlen($data['student_phone']) > 20
        ) {
            $this->redirectWithError(
                'Thông tin vượt quá độ dài cho phép.'
            );
        }

        $cohort = filter_var(
            $data['student_cohort'],
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1, 'max_range' => 4294967295]]
        );

        if ($cohort === false) {
            $this->redirectWithError('Khóa học không hợp lệ.');
        }

        $data['student_cohort'] = $cohort;

        // Mã sinh viên không được sửa, lấy từ database.
        if (Student::schoolCodeExists(
            $data['student_school'],
            $student['student_code'],
            $userId
        )) {
            $this->redirectWithError(
                'Mã sinh viên đã tồn tại tại trường này.'
            );
        }

        try {
            Student::updateProfile($userId, $data);

            $_SESSION['student_profile_success'] =
                'Cập nhật thông tin cá nhân thành công.';
        } catch (PDOException $e) {
            error_log('[STUDENT PROFILE] ' . $e->getMessage());

            $_SESSION['student_profile_error'] =
                'Không thể cập nhật hồ sơ. Vui lòng thử lại.';
        }

        header('Location: /profile');
        exit;
    }


    public function settings(): void
    {
        $user = $this->requireActiveUser('Student');

        $db = Database::connect();

        $stmt = $db->prepare("
        SELECT email, status
        FROM users
        WHERE user_id = :user_id
          AND role = 'Student'
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

        $error = $_SESSION['student_settings_error'] ?? null;
        unset($_SESSION['student_settings_error']);

        $this->view('student/settings', [
            'account' => $account,
            'error' => $error
        ]);
    }

    public function updatePassword(): void
    {
        $user = $this->requireActiveUser('Student');

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

        $redirectError = static function (string $message): never {
            $_SESSION['student_settings_error'] = $message;
            header('Location: /settings', true, 303);
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
              AND role = 'Student'
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

            $newHash = password_hash($newPassword, PASSWORD_DEFAULT);

            $stmt = $db->prepare("
            UPDATE users
            SET password_hash = :password_hash
            WHERE user_id = :user_id
              AND role = 'Student'
              AND status = 'Active'
        ");

            $stmt->execute([
                'password_hash' => $newHash,
                'user_id' => (int) $user['user_id']
            ]);

            $db->commit();

            // Đăng xuất phiên hiện tại sau khi đổi mật khẩu.
            $_SESSION = [];
            session_regenerate_id(true);

            $_SESSION['login_success_message'] =
                'Đổi mật khẩu thành công. Vui lòng đăng nhập lại.';

            header('Location: /login', true, 303);
            exit;
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            error_log(
                'Update Student password failed: ' . $e->getMessage()
            );

            $redirectError(
                'Không thể đổi mật khẩu. Vui lòng thử lại.'
            );
        }
    }

    private function redirectWithError(string $message): never
    {
        $_SESSION['student_profile_error'] = $message;

        header('Location: /profile');
        exit;
    }
}
