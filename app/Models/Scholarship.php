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



    // Tìm kiếm và lọc học bổng công khai
    // Tìm học bổng công khai theo tên
    public static function searchPublished(string $keyword = ''): array
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
        INNER JOIN providers p ON p.user_id = s.user_id
        WHERE s.status = 'Published'
          AND s.start_date <= CURDATE()
          AND s.end_date >= CURDATE()
    ";

        $params = [];

        if ($keyword !== '') {
            $sql .= " AND s.scholarship_name LIKE :keyword";
            $params['keyword'] = '%' . $keyword . '%';
        }

        $sql .= "
        ORDER BY s.created_at DESC, s.scholarship_id DESC
        LIMIT 100
    ";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lấy dữ liệu học bổng phục vụ tìm kiếm ngữ nghĩa
    public static function getSemanticSearchData(): array
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
            p.provider_name,
            ar.required_gpa,
            ar.required_year,
            ar.required_major
        FROM scholarships s
        INNER JOIN providers p
            ON p.user_id = s.user_id
        LEFT JOIN application_requirements ar
            ON ar.scholarship_id = s.scholarship_id
        WHERE s.status = 'Published'
          AND s.start_date <= CURDATE()
          AND s.end_date >= CURDATE()
        ORDER BY s.scholarship_id DESC
    ";

        $stmt = $db->prepare($sql);
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
            SELECT
                rd.required_documents_id,
                rd.document_name,
                rd.description,
                rd.is_required
            FROM required_documents rd
            INNER JOIN application_requirements ar
                ON rd.requirement_id = ar.requirement_id
            WHERE ar.scholarship_id = :scholarship_id
            ORDER BY
                rd.is_required DESC,
                rd.required_documents_id ASC
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


    public static function review(
        int $scholarshipId,
        string $action,
        ?string $reason = null
    ): bool {
        $newStatus = match ($action) {
            'approve' => 'Published',
            'reject' => 'Rejected',
            default => null
        };

        if ($scholarshipId <= 0 || $newStatus === null) {
            return false;
        }

        if ($action === 'reject' && ($reason === null || trim($reason) === '')) {
            return false;
        }

        $db = Database::connect();

        $sql = "
        UPDATE scholarships
        SET
            status = :new_status,
            rejection_reason = :rejection_reason
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
            'rejection_reason' => $action === 'reject' ? $reason : null,
            'scholarship_id' => $scholarshipId
        ]);

        return $stmt->rowCount() === 1;
    }


    // Tạo học bổng cùng điều kiện và danh sách giấy tờ
    public static function create(
        int $providerId,
        array $data,
        array $documents
    ): int {
        $db = Database::connect();

        try {
            // Bắt đầu transaction
            $db->beginTransaction();

            // 1. Lưu thông tin học bổng
            $stmt = $db->prepare("
            INSERT INTO scholarships (
                user_id,
                scholarship_name,
                description,
                amount,
                start_date,
                end_date,
                status
            )
            VALUES (
                :user_id,
                :scholarship_name,
                :description,
                :amount,
                :start_date,
                :end_date,
                'Pending'
            )
        ");

            $stmt->execute([
                'user_id' => $providerId,
                'scholarship_name' => $data['scholarship_name'],
                'description' => $data['description'],
                'amount' => $data['amount'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date']
            ]);

            $scholarshipId = (int) $db->lastInsertId();

            // 2. Lưu điều kiện ứng tuyển
            $stmt = $db->prepare("
            INSERT INTO application_requirements (
                scholarship_id,
                required_gpa,
                required_year,
                required_major
            )
            VALUES (
                :scholarship_id,
                :required_gpa,
                :required_year,
                :required_major
            )
        ");

            $stmt->execute([
                'scholarship_id' => $scholarshipId,
                'required_gpa' => $data['required_gpa'],
                'required_year' => $data['required_year'],
                'required_major' => $data['required_major']
            ]);

            $requirementId = (int) $db->lastInsertId();

            // 3. Lưu các giấy tờ yêu cầu
            $stmt = $db->prepare("
            INSERT INTO required_documents (
                requirement_id,
                document_name,
                description,
                is_required
            )
            VALUES (
                :requirement_id,
                :document_name,
                :description,
                :is_required
            )
        ");

            foreach ($documents as $document) {
                $stmt->execute([
                    'requirement_id' => $requirementId,
                    'document_name' => $document['document_name'],
                    'description' => $document['description'] ?? null,
                    'is_required' => $document['is_required'] ? 1 : 0
                ]);
            }

            // 4. Xác nhận lưu toàn bộ
            $db->commit();

            return $scholarshipId;
        } catch (\Throwable $e) {
            // Có lỗi thì hủy toàn bộ thay đổi
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            throw $e;
        }
    }

    // Lấy danh sách học bổng của một Provider
    public static function getByProvider(int $providerId): array
    {
        $db = Database::connect();

        $stmt = $db->prepare("
        SELECT
            scholarship_id,
            scholarship_name,
            description,
            amount,
            start_date,
            end_date,
            status,
            created_at
        FROM scholarships
        WHERE user_id = :user_id
        ORDER BY created_at DESC, scholarship_id DESC
    ");

        $stmt->execute([
            'user_id' => $providerId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lấy chi tiết học bổng thuộc Provider đang đăng nhập
    public static function findByProvider(
        int $scholarshipId,
        int $providerId
    ): array|false {
        $db = Database::connect();

        $stmt = $db->prepare("
        SELECT
            scholarship_id,
            scholarship_name,
            description,
            amount,
            start_date,
            end_date,
            status,
            rejection_reason,
            created_at
        FROM scholarships
        WHERE scholarship_id = :scholarship_id
          AND user_id = :user_id
        LIMIT 1
    ");

        $stmt->execute([
            'scholarship_id' => $scholarshipId,
            'user_id' => $providerId
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    public static function resubmitRejected(
        int $scholarshipId,
        int $providerId,
        array $data,
        array $documents
    ): bool {
        $db = Database::connect();

        try {
            $db->beginTransaction();

            // Khóa học bổng để tránh cập nhật đồng thời.
            $stmt = $db->prepare("
            SELECT scholarship_id
            FROM scholarships
            WHERE scholarship_id = :id
              AND user_id = :provider_id
              AND status = 'Rejected'
            FOR UPDATE
        ");
            $stmt->execute([
                'id' => $scholarshipId,
                'provider_id' => $providerId
            ]);

            if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
                $db->rollBack();
                return false;
            }

            // Cập nhật học bổng cũ, chuyển về chờ duyệt.
            $stmt = $db->prepare("
            UPDATE scholarships
            SET scholarship_name = :name,
                description = :description,
                amount = :amount,
                start_date = :start_date,
                end_date = :end_date,
                status = 'Pending',
                rejection_reason = NULL
            WHERE scholarship_id = :id
              AND user_id = :provider_id
              AND status = 'Rejected'
        ");
            $stmt->execute([
                'name' => $data['scholarship_name'],
                'description' => $data['description'],
                'amount' => $data['amount'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'id' => $scholarshipId,
                'provider_id' => $providerId
            ]);

            // Tìm bộ điều kiện ứng tuyển hiện có.
            $stmt = $db->prepare("
            SELECT requirement_id
            FROM application_requirements
            WHERE scholarship_id = :id
            FOR UPDATE
        ");
            $stmt->execute(['id' => $scholarshipId]);

            $requirementId = $stmt->fetchColumn();

            if ($requirementId === false) {
                $stmt = $db->prepare("
                INSERT INTO application_requirements
                    (scholarship_id, required_gpa,
                     required_year, required_major)
                VALUES (:id, :gpa, :year, :major)
            ");
                $stmt->execute([
                    'id' => $scholarshipId,
                    'gpa' => $data['required_gpa'],
                    'year' => $data['required_year'],
                    'major' => $data['required_major']
                ]);

                $requirementId = (int) $db->lastInsertId();
            } else {
                $requirementId = (int) $requirementId;

                $stmt = $db->prepare("
                UPDATE application_requirements
                SET required_gpa = :gpa,
                    required_year = :year,
                    required_major = :major
                WHERE requirement_id = :id
            ");
                $stmt->execute([
                    'gpa' => $data['required_gpa'],
                    'year' => $data['required_year'],
                    'major' => $data['required_major'],
                    'id' => $requirementId
                ]);
            }

            // Thay danh sách giấy tờ của bộ điều kiện này.
            $stmt = $db->prepare("
            DELETE FROM required_documents
            WHERE requirement_id = :id
        ");
            $stmt->execute(['id' => $requirementId]);

            $stmt = $db->prepare("
            INSERT INTO required_documents
                (requirement_id, document_name,
                 description, is_required)
            VALUES (:id, :name, :description, :required)
        ");

            foreach ($documents as $document) {
                $stmt->execute([
                    'id' => $requirementId,
                    'name' => $document['document_name'],
                    'description' => $document['description'],
                    'required' => $document['is_required'] ? 1 : 0
                ]);
            }

            $db->commit();
            return true;
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            throw $e;
        }
    }
}
