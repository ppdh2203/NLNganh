<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class Student
{
    public static function findByUserId(int $userId): ?array
    {
        $db = Database::connect();

        $stmt = $db->prepare("
            SELECT
                s.user_id,
                s.student_name,
                s.student_code,
                s.student_school,
                s.student_major,
                s.student_cohort,
                s.student_address,
                s.student_phone,
                u.email,
                u.avatar
            FROM students s
            INNER JOIN users u ON u.user_id = s.user_id
            WHERE s.user_id = :user_id
              AND u.role = 'Student'
            LIMIT 1
        ");

        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function updateProfile(int $userId, array $data): bool
    {
        $db = Database::connect();

        $stmt = $db->prepare("
            UPDATE students
            SET
                student_name = :student_name,
                student_school = :student_school,
                student_major = :student_major,
                student_cohort = :student_cohort,
                student_address = :student_address,
                student_phone = :student_phone
            WHERE user_id = :user_id
        ");

        return $stmt->execute([
            'user_id' => $userId,
            'student_name' => $data['student_name'],
            'student_school' => $data['student_school'],
            'student_major' => $data['student_major'],
            'student_cohort' => $data['student_cohort'],
            'student_address' => $data['student_address'],
            'student_phone' => $data['student_phone']
        ]);
    }

    public static function schoolCodeExists(
        string $school,
        string $code,
        int $exceptUserId
    ): bool {
        $db = Database::connect();

        $stmt = $db->prepare("
            SELECT 1
            FROM students
            WHERE student_school = :school
              AND student_code = :code
              AND user_id <> :user_id
            LIMIT 1
        ");

        $stmt->execute([
            'school' => $school,
            'code' => $code,
            'user_id' => $exceptUserId
        ]);

        return (bool) $stmt->fetchColumn();
    }
}
