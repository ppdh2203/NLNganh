<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class SavedScholarship
{
    public static function isSaved(int $userId, int $scholarshipId): bool
    {
        $db = Database::connect();

        $stmt = $db->prepare("
            SELECT 1
            FROM saved_scholarships
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

    public static function save(int $userId, int $scholarshipId): void
    {
        $db = Database::connect();

        $stmt = $db->prepare("
            INSERT INTO saved_scholarships
                (user_id, scholarship_id, saved_at)
            SELECT :user_id, s.scholarship_id, NOW()
            FROM scholarships s
            WHERE s.scholarship_id = :scholarship_id
              AND s.status = 'Published'
              AND s.start_date <= CURDATE()
              AND s.end_date >= CURDATE()
            ON DUPLICATE KEY UPDATE
                saved_at = saved_at
        ");

        $stmt->execute([
            'user_id' => $userId,
            'scholarship_id' => $scholarshipId
        ]);
    }

    public static function remove(int $userId, int $scholarshipId): void
    {
        $db = Database::connect();

        $stmt = $db->prepare("
            DELETE FROM saved_scholarships
            WHERE user_id = :user_id
              AND scholarship_id = :scholarship_id
        ");

        $stmt->execute([
            'user_id' => $userId,
            'scholarship_id' => $scholarshipId
        ]);
    }

    public static function getByUser(int $userId): array
    {
        $db = Database::connect();

        $stmt = $db->prepare("
            SELECT
                s.scholarship_id,
                s.scholarship_name,
                s.description,
                s.amount,
                s.start_date,
                s.end_date,
                s.status,
                p.provider_name,
                ss.saved_at
            FROM saved_scholarships ss
            INNER JOIN scholarships s
                ON s.scholarship_id = ss.scholarship_id
            INNER JOIN providers p
                ON p.user_id = s.user_id
            WHERE ss.user_id = :user_id
            ORDER BY ss.saved_at DESC, s.scholarship_id DESC
        ");

        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
