<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Student;
use PDOException;

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

    private function redirectWithError(string $message): never
    {
        $_SESSION['student_profile_error'] = $message;

        header('Location: /profile');
        exit;
    }
}
