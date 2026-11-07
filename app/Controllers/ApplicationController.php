<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\Scholarship;
use PDO;
use Throwable;

class ApplicationController extends Controller
{
    private const MAX_FILE_SIZE = 5 * 1024 * 1024;

    // Thư mục nằm ngoài public, không cho truy cập trực tiếp.
    private function uploadDirectory(): string
    {
        return dirname(__DIR__, 2)
            . '/storage/uploads/applications';
    }

    private function redirectToForm(
        int $scholarshipId,
        string $message
    ): void {
        $_SESSION['application_error'] = $message;

        header(
            'Location: /scholarships/'
                . $scholarshipId
                . '/apply',
            true,
            303
        );
        exit;
    }

    // GET: Hiển thị form nộp hồ sơ
    public function create(string $id): void
    {
        $user = $this->requireActiveUser('Student');

        if (!ctype_digit($id) || (int) $id <= 0) {
            $this->sendNotFound();
            return;
        }

        $scholarshipId = (int) $id;
        $scholarship = Scholarship::findById($scholarshipId);

        if ($scholarship === false) {
            $this->sendNotFound();
            return;
        }

        if (Application::hasApplied(
            (int) $user['user_id'],
            $scholarshipId
        )) {
            $_SESSION['application_message'] =
                'Bạn đã nộp hồ sơ cho học bổng này.';

            header(
                'Location: /scholarships/' . $scholarshipId,
                true,
                303
            );
            exit;
        }

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $requirements = Scholarship::getRequirements(
            $scholarshipId
        );

        $documents = Scholarship::getRequiredDocuments(
            $scholarshipId
        );

        $error = $_SESSION['application_error'] ?? null;
        unset($_SESSION['application_error']);

        $this->view('applications/create', [
            'scholarship' => $scholarship,
            'requirements' => $requirements,
            'documents' => $documents,
            'error' => $error
        ]);
    }

    // POST: Xử lý nộp hồ sơ
    public function store(string $id): void
    {
        $user = $this->requireActiveUser('Student');

        if (!ctype_digit($id) || (int) $id <= 0) {
            $this->sendNotFound();
            return;
        }

        $scholarshipId = (int) $id;
        $userId = (int) $user['user_id'];

        // Kiểm tra CSRF
        $sessionToken = (string) (
            $_SESSION['csrf_token'] ?? ''
        );

        $submittedToken = (string) (
            $_POST['csrf_token'] ?? ''
        );

        if (
            $sessionToken === '' ||
            !hash_equals($sessionToken, $submittedToken)
        ) {
            http_response_code(403);
            exit('Phiên gửi hồ sơ không hợp lệ.');
        }

        // Kiểm tra học bổng
        $scholarship = Scholarship::findById($scholarshipId);

        if ($scholarship === false) {
            $this->sendNotFound();
            return;
        }

        if (Application::hasApplied(
            $userId,
            $scholarshipId
        )) {
            $this->redirectToForm(
                $scholarshipId,
                'Bạn đã nộp hồ sơ cho học bổng này.'
            );
        }

        // Kiểm tra GPA
        $gpaInput = trim((string) (
            $_POST['student_gpa'] ?? ''
        ));

        if (
            $gpaInput === '' ||
            !is_numeric($gpaInput) ||
            (float) $gpaInput < 0 ||
            (float) $gpaInput > 4
        ) {
            $this->redirectToForm(
                $scholarshipId,
                'Vui lòng nhập GPA hợp lệ từ 0.00 đến 4.00.'
            );
        }

        $studentGpa = (float) $gpaInput;

        // Kiểm tra năm học
        $yearInput = filter_var(
            $_POST['student_year'] ?? null,
            FILTER_VALIDATE_INT,
            [
                'options' => [
                    'min_range' => 1,
                    'max_range' => 10
                ]
            ]
        );

        if ($yearInput === false) {
            $this->redirectToForm(
                $scholarshipId,
                'Vui lòng nhập năm học hợp lệ.'
            );
        }

        $studentYear = (int) $yearInput;


        $studentMajor = trim((string) (
            $_POST['student_major'] ?? ''
        ));

        if (
            $studentMajor === '' ||
            mb_strlen($studentMajor, 'UTF-8') > 255
        ) {
            $this->redirectToForm(
                $scholarshipId,
                'Vui lòng nhập ngành học hợp lệ.'
            );
        }

        $requirements = Scholarship::getRequirements($scholarshipId);


        $requiredYear = $requirements !== false
            ? (int) ($requirements['required_year'] ?? 0)
            : 0;

        // 0 hoặc NULL nghĩa là không giới hạn năm học
        if ($requiredYear > 0 && $studentYear !== $requiredYear) {
            $this->redirectToForm(
                $scholarshipId,
                'Năm học không phù hợp với yêu cầu học bổng.'
            );
        }


        $requiredGpa = $requirements !== false
            ? (float) ($requirements['required_gpa'] ?? 0)
            : 0;

        if ($requiredGpa > 0 && $studentGpa < $requiredGpa) {
            $this->redirectToForm(
                $scholarshipId,
                'GPA không đáp ứng yêu cầu tối thiểu của học bổng.'
            );
        }


        $requiredMajor = $requirements !== false
            ? trim((string) ($requirements['required_major'] ?? ''))
            : '';

        $normalizeMajor = static function (string $value): string {
            $value = mb_strtolower(trim($value), 'UTF-8');

            if (class_exists(\Normalizer::class)) {
                $value = \Normalizer::normalize(
                    $value,
                    \Normalizer::FORM_D
                ) ?: $value;
            }

            $value = preg_replace('/\p{Mn}+/u', '', $value);
            $value = str_replace('đ', 'd', $value);
            $value = preg_replace('/\s+/u', ' ', $value);

            return trim($value);
        };

        if ($requiredMajor !== '') {
            $allowedMajors = preg_split(
                '/[,;\r\n]+/u',
                $requiredMajor
            );

            $normalizedInput = $normalizeMajor($studentMajor);

            $isValidMajor = false;

            foreach ($allowedMajors as $allowedMajor) {
                if (
                    $normalizeMajor($allowedMajor)
                    === $normalizedInput
                ) {
                    $isValidMajor = true;
                    break;
                }
            }

            if (!$isValidMajor) {
                $this->redirectToForm(
                    $scholarshipId,
                    'Ngành học không phù hợp với yêu cầu học bổng.'
                );
            }
        }

        $note = trim((string) ($_POST['note'] ?? ''));

        if (mb_strlen($note, 'UTF-8') > 2000) {
            $this->redirectToForm(
                $scholarshipId,
                'Ghi chú không được vượt quá 2000 ký tự.'
            );
        }

        // Danh sách giấy tờ Provider yêu cầu
        $documents = Scholarship::getRequiredDocuments(
            $scholarshipId
        );

        $uploadedFiles = $_FILES['documents'] ?? [];
        $validatedFiles = [];

        $fileInfo = new \finfo(FILEINFO_MIME_TYPE);

        // Chỉ chấp nhận file tương ứng với giấy tờ đã khai báo
        $allowedIds = [];

        foreach ($documents as $document) {
            $requiredId = (int) (
                $document['required_documents_id']
            );

            $allowedIds[] = $requiredId;
        }

        foreach (
            array_keys($uploadedFiles['name'] ?? [])
            as $submittedId
        ) {
            if (!in_array((int) $submittedId, $allowedIds, true)) {
                $this->redirectToForm(
                    $scholarshipId,
                    'Danh sách giấy tờ gửi lên không hợp lệ.'
                );
            }
        }

        foreach ($documents as $document) {
            $requiredId = (int) (
                $document['required_documents_id']
            );

            $isRequired = (int) (
                $document['is_required']
            ) === 1;

            $errorCode = $uploadedFiles['error'][$requiredId]
                ?? UPLOAD_ERR_NO_FILE;

            // Không tải file
            if ($errorCode === UPLOAD_ERR_NO_FILE) {
                if ($isRequired) {
                    $this->redirectToForm(
                        $scholarshipId,
                        'Bạn chưa tải lên giấy tờ bắt buộc: '
                            . $document['document_name']
                    );
                }

                continue;
            }

            if ($errorCode !== UPLOAD_ERR_OK) {
                $this->redirectToForm(
                    $scholarshipId,
                    'Không thể tải lên giấy tờ: '
                        . $document['document_name']
                );
            }

            $tmpName = $uploadedFiles['tmp_name'][$requiredId]
                ?? '';

            $fileSize = (int) (
                $uploadedFiles['size'][$requiredId] ?? 0
            );

            $originalName = (string) (
                $uploadedFiles['name'][$requiredId] ?? ''
            );

            if (
                !is_string($tmpName) ||
                !is_uploaded_file($tmpName) ||
                $fileSize <= 0 ||
                $fileSize > self::MAX_FILE_SIZE
            ) {
                $this->redirectToForm(
                    $scholarshipId,
                    'File không hợp lệ hoặc vượt quá 5 MB.'
                );
            }

            // Kiểm tra phần mở rộng
            $extension = strtolower(
                pathinfo($originalName, PATHINFO_EXTENSION)
            );

            if ($extension !== 'pdf') {
                $this->redirectToForm(
                    $scholarshipId,
                    'Chỉ chấp nhận tài liệu PDF.'
                );
            }

            // Kiểm tra MIME thực tế
            $mime = $fileInfo->file($tmpName);

            if ($mime !== 'application/pdf') {
                $this->redirectToForm(
                    $scholarshipId,
                    'Nội dung file không phải PDF hợp lệ.'
                );
            }

            // Kiểm tra chữ ký PDF
            $handle = fopen($tmpName, 'rb');

            if ($handle === false) {
                $this->redirectToForm(
                    $scholarshipId,
                    'Không thể đọc file PDF.'
                );
            }

            $signature = fread($handle, 5);
            fclose($handle);

            if ($signature !== '%PDF-') {
                $this->redirectToForm(
                    $scholarshipId,
                    'File PDF có cấu trúc không hợp lệ.'
                );
            }

            $validatedFiles[] = [
                'required_id' => $requiredId,
                'doc_name' => (string) $document['document_name'],
                'tmp_name' => $tmpName
            ];
        }

        $db = Database::connect();
        $savedPaths = [];

        try {
            $uploadDir = $this->uploadDirectory();

            if (
                !is_dir($uploadDir) &&
                !mkdir($uploadDir, 0700, true) &&
                !is_dir($uploadDir)
            ) {
                throw new \RuntimeException(
                    'Không thể tạo thư mục lưu hồ sơ.'
                );
            }

            $db->beginTransaction();

            // Khóa bản ghi học bổng và kiểm tra lại
            // trạng thái, thời hạn tại thời điểm nộp.
            $stmt = $db->prepare("
                SELECT scholarship_id
                FROM scholarships
                WHERE scholarship_id = :id
                  AND status = 'Published'
                  AND start_date <= CURDATE()
                  AND end_date >= CURDATE()
                FOR UPDATE
            ");

            $stmt->execute(['id' => $scholarshipId]);

            if (!$stmt->fetchColumn()) {
                throw new \RuntimeException(
                    'Học bổng không còn nhận hồ sơ.'
                );
            }

            // Lưu thông tin ứng tuyển
            $applicationId = Application::create(
                $userId,
                $scholarshipId,
                $studentGpa,
                $studentYear,
                $note !== '' ? $note : null,
                $db
            );

            // Lưu từng file PDF
            foreach ($validatedFiles as $file) {
                $filename = bin2hex(random_bytes(16)) . '.pdf';

                $targetPath = $uploadDir . '/' . $filename;

                if (!move_uploaded_file(
                    $file['tmp_name'],
                    $targetPath
                )) {
                    throw new \RuntimeException(
                        'Không thể lưu tài liệu PDF.'
                    );
                }

                $savedPaths[] = $targetPath;

                // Đường dẫn tương đối trong vùng lưu trữ riêng
                $fileUrl = 'applications/' . $filename;

                ApplicationDocument::create(
                    $applicationId,
                    $file['required_id'],
                    $file['doc_name'],
                    $fileUrl,
                    'application/pdf',
                    $db
                );
            }

            $db->commit();

            $_SESSION['application_message'] =
                'Nộp hồ sơ học bổng thành công!';

            header(
                'Location: /scholarships/' . $scholarshipId,
                true,
                303
            );
            exit;
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            // Xóa những file đã lưu nếu có lỗi
            foreach ($savedPaths as $path) {
                if (is_file($path)) {
                    unlink($path);
                }
            }

            error_log(
                'Application submission failed: '
                    . $e->getMessage()
            );

            $this->redirectToForm(
                $scholarshipId,
                'Không thể nộp hồ sơ. '
                    . 'Vui lòng kiểm tra lại và thử lần nữa.'
            );
        }
    }

    // Danh sách hồ sơ đã nộp của Student
    public function index(): void
    {
        $user = $this->requireActiveUser('Student');

        $applications = Application::getByStudent(
            (int) $user['user_id']
        );

        $this->view('applications/index', [
            'applications' => $applications
        ]);
    }


    public function viewDocument(string $id, string $docId): void
    {
        $user = $this->requireActiveUser('Student');

        if (
            !ctype_digit($id) ||
            !ctype_digit($docId) ||
            (int) $id <= 0 ||
            (int) $docId <= 0
        ) {
            $this->sendNotFound();
            return;
        }

        $application = Application::findById(
            (int) $id,
            (int) $user['user_id']
        );

        if ($application === false) {
            $this->sendNotFound();
            return;
        }

        $documents = ApplicationDocument::getByApplication(
            (int) $application['application_id']
        );

        $document = null;

        foreach ($documents as $item) {
            if ((int) $item['doc_id'] === (int) $docId) {
                $document = $item;
                break;
            }
        }

        if ($document === null) {
            $this->sendNotFound();
            return;
        }

        // Chỉ chấp nhận tên file PDF do hệ thống tạo
        $fileUrl = (string) $document['file_url'];


        if (!preg_match(
            '~^applications/[a-f0-9]{32}\.pdf$~',
            $fileUrl
        )) {
            $this->sendNotFound();
            return;
        }


        $filePath = $this->uploadDirectory()
            . '/' . basename($fileUrl);


        if (!is_file($filePath) || !is_readable($filePath)) {
            http_response_code(404);
            exit('Không tìm thấy hoặc không đọc được file PDF.');
        }


        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="document.pdf"');
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, no-store');
        header('Content-Length: ' . filesize($filePath));

        readfile($filePath);
        exit;
    }


    // Chi tiết một hồ sơ đã nộp
    public function show(string $id): void
    {
        $user = $this->requireActiveUser('Student');

        if (!ctype_digit($id) || (int) $id <= 0) {
            $this->sendNotFound();
            return;
        }

        $application = Application::findById(
            (int) $id,
            (int) $user['user_id']
        );

        if ($application === false) {
            $this->sendNotFound();
            return;
        }

        $uploadedDocuments = ApplicationDocument::getByApplication(
            (int) $application['application_id']
        );

        $this->view('applications/show', [
            'application' => $application,
            'uploadedDocuments' => $uploadedDocuments
        ]);
    }
}
