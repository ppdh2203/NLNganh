<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class Scholarship
{
    // Lấy các học bổng công khai và còn trong thời gian nhận hồ sơ
    public static function getPublished(int $limit = 3): array
    {
        $db = Database::connect();

        $sql = "
            SELECT
                s.scholarship_id,
                s.scholarship_name,
                s.description,
                s.amount,
                s.start_date,
                s.end_date,
                p.provider_name
            FROM scholarships s
            INNER JOIN providers p ON s.user_id = p.user_id
            WHERE s.status = 'Published'
              AND s.start_date <= CURDATE()
              AND s.end_date >= CURDATE()
            ORDER BY s.created_at DESC, s.scholarship_id DESC
            LIMIT :limit
        ";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    // Lấy thông tin chi tiết một học bổng theo ID
    public static function findById(int $id): array|false
    {
        $db = Database::connect();

        $sql = "
            SELECT
                s.scholarship_id,
                s.scholarship_name,
                s.description,
                s.amount,
                s.start_date,
                s.end_date,
                p.provider_name
            FROM scholarships s
            INNER JOIN providers p ON s.user_id = p.user_id
            WHERE s.scholarship_id = :id
              AND s.status = 'Published'
              AND s.start_date <= CURDATE()
              AND s.end_date >= CURDATE()
            LIMIT 1
        ";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    // Lấy điều kiện ứng tuyển của một học bổng
    public static function getRequirements(int $scholarshipId): array|false
    {
        $db = Database::connect();

        $sql = "
            SELECT required_gpa, required_year, required_major
            FROM application_requirements
            WHERE scholarship_id = :scholarship_id
            LIMIT 1
        ";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':scholarship_id', $scholarshipId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Lấy danh sách giấy tờ cần nộp của một học bổng
    public static function getRequiredDocuments(int $scholarshipId): array
    {
        $db = Database::connect();

        $sql = "
            SELECT rd.document_name, rd.description, rd.is_required
            FROM required_documents rd
            INNER JOIN application_requirements ar
                ON rd.requirement_id = ar.requirement_id
            WHERE ar.scholarship_id = :scholarship_id
            ORDER BY rd.is_required DESC, rd.required_documents_id ASC
        ";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':scholarship_id', $scholarshipId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lấy danh sách học bổng theo trạng thái để Admin xét duyệt
    public static function getByStatus(string $status): array
    {
        $allowedStatuses = ['Pending', 'Published', 'Rejected'];

        if (!in_array($status, $allowedStatuses, true)) {
            return [];
        }

        $db = Database::connect();

        $sql = "
            SELECT
                s.scholarship_id,
                s.scholarship_name,
                s.description,
                s.amount,
                s.start_date,
                s.end_date,
                s.status,
                s.created_at,
                s.user_id,
                p.provider_name,
                p.organization
            FROM scholarships s
            INNER JOIN providers p ON p.user_id = s.user_id
            WHERE s.status = :status
            ORDER BY s.created_at DESC, s.scholarship_id DESC
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute(['status' => $status]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function review(int $scholarshipId, string $action): bool
    {
        $newStatus = match ($action) {
            'approve' => 'Published',
            'reject' => 'Rejected',
            default => null
        };

        if ($scholarshipId <= 0 || $newStatus === null) {
            return false;
        }

        $db = Database::connect();

        $sql = "
        UPDATE scholarships
        SET status = :new_status
        WHERE scholarship_id = :scholarship_id
          AND status = 'Pending'
          AND EXISTS (
              SELECT 1
              FROM users u
              WHERE u.user_id = scholarships.user_id
                AND u.role = 'Provider'
                AND u.status = 'Active'
          )
    ";

        $stmt = $db->prepare($sql);

        $stmt->execute([
            'new_status' => $newStatus,
            'scholarship_id' => $scholarshipId
        ]);

        return $stmt->rowCount() === 1;
    }
}
