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

    public static function getByApplication(int $applicationId): array
    {
        $db = \App\Core\Database::connect();

        $stmt = $db->prepare("
        SELECT
            doc_id,
            application_id,
            doc_name,
            file_url,
            doc_type,
            uploaded_at
        FROM application_documents
        WHERE application_id = :application_id
        ORDER BY doc_id ASC
    ");

        $stmt->execute([
            'application_id' => $applicationId
        ]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
