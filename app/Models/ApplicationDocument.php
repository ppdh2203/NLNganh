<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class ApplicationDocument
{
    // Lưu thông tin một tài liệu đã tải lên

    public static function create(
        int $applicationId,
        int $requiredDocumentId,
        string $docName,
        string $fileUrl,
        string $docType,
        ?PDO $db = null
    ): int {
        $db ??= Database::connect();

        $stmt = $db->prepare("
        INSERT INTO application_documents (
            application_id,
            required_document_id,
            doc_name,
            file_url,
            doc_type,
            uploaded_at
        )
        VALUES (
            :application_id,
            :required_document_id,
            :doc_name,
            :file_url,
            :doc_type,
            NOW()
        )
    ");

        $stmt->execute([
            'application_id' => $applicationId,
            'required_document_id' => $requiredDocumentId,
            'doc_name' => $docName,
            'file_url' => $fileUrl,
            'doc_type' => $docType
        ]);

        return (int) $db->lastInsertId();
    }


    // Lấy các tài liệu thuộc một hồ sơ
    public static function getByApplication(
        int $applicationId,
        int $userId
    ): array {
        $db = Database::connect();

        $stmt = $db->prepare("
            SELECT
                ad.doc_id,
                ad.doc_name,
                ad.file_url,
                ad.doc_type,
                ad.uploaded_at,
                ad.required_document_id
            FROM application_documents ad
            INNER JOIN applications a
                ON a.application_id = ad.application_id
            WHERE ad.application_id = :application_id
              AND a.user_id = :user_id
            ORDER BY ad.doc_id ASC
        ");

        $stmt->execute([
            'application_id' => $applicationId,
            'user_id' => $userId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
