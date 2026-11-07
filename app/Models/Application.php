<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class Application
{
    // Kiểm tra sinh viên đã nộp học bổng này chưa
    public static function hasApplied(
        int $userId,
        int $scholarshipId
    ): bool {
        $db = Database::connect();

        $stmt = $db->prepare("
            SELECT 1
            FROM applications
            WHERE user_id = :user_id
              AND scholarship_id = :scholarship_id
            LIMIT 1
        ");

        $stmt->execute([
            'user_id' => $userId,
            'scholarship_id' => $scholarshipId
        ]);

        return $stmt->fetchColumn() !== false;
    }

    // Lấy danh sách hồ sơ sinh viên đã nộp
    public static function getByStudent(int $userId): array
    {
        $db = Database::connect();

        $stmt = $db->prepare("
            SELECT
                a.application_id,
                a.scholarship_id,
                a.student_gpa,
                a.student_year,
                a.submitted_at,
                a.note,
                a.status,
                s.scholarship_name,
                s.amount,
                s.end_date
            FROM applications a
            INNER JOIN scholarships s
                ON s.scholarship_id = a.scholarship_id
            WHERE a.user_id = :user_id
            ORDER BY a.submitted_at DESC,
                     a.application_id DESC
        ");

        $stmt->execute([
            'user_id' => $userId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lấy một hồ sơ thuộc sinh viên đang đăng nhập
    public static function findById(
        int $applicationId,
        int $userId
    ): array|false {
        $db = Database::connect();

        $stmt = $db->prepare("
            SELECT
                a.*,
                s.scholarship_name,
                s.amount,
                s.end_date
            FROM applications a
            INNER JOIN scholarships s
                ON s.scholarship_id = a.scholarship_id
            WHERE a.application_id = :application_id
              AND a.user_id = :user_id
            LIMIT 1
        ");

        $stmt->execute([
            'application_id' => $applicationId,
            'user_id' => $userId
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Tạo hồ sơ ứng tuyển
    // Chỉ gọi sau khi Controller đã kiểm tra
    // quyền, hạn nộp và tài liệu bắt buộc.

    public static function create(
        int $userId,
        int $scholarshipId,
        ?float $studentGpa,
        ?int $studentYear,
        ?string $note,
        ?PDO $db = null
    ): int {
        $db ??= Database::connect();

        $stmt = $db->prepare("
        INSERT INTO applications (
            user_id,
            scholarship_id,
            student_gpa,
            student_year,
            submitted_at,
            note,
            status
        )
        VALUES (
            :user_id,
            :scholarship_id,
            :student_gpa,
            :student_year,
            NOW(),
            :note,
            'Submitted'
        )
    ");

        $stmt->execute([
            'user_id' => $userId,
            'scholarship_id' => $scholarshipId,
            'student_gpa' => $studentGpa,
            'student_year' => $studentYear,
            'note' => $note
        ]);

        return (int) $db->lastInsertId();
    }
}
