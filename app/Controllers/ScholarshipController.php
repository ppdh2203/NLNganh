<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Scholarship;
use App\Models\SavedScholarship;

class ScholarshipController extends Controller
{
    public function index(): void
    {
        // Nhận dữ liệu tìm kiếm từ URL
        $keyword = trim((string) ($_GET['keyword'] ?? ''));
        $major = trim((string) ($_GET['major'] ?? ''));

        $minAmountInput = trim((string) ($_GET['min_amount'] ?? ''));
        $maxAmountInput = trim((string) ($_GET['max_amount'] ?? ''));

        // Kiểm tra giá trị tiền hợp lệ
        $minAmount = (
            $minAmountInput !== ''
            && is_numeric($minAmountInput)
            && (float) $minAmountInput >= 0
        ) ? (float) $minAmountInput : null;

        $maxAmount = (
            $maxAmountInput !== ''
            && is_numeric($maxAmountInput)
            && (float) $maxAmountInput >= 0
        ) ? (float) $maxAmountInput : null;

        // Gọi Model tìm kiếm học bổng
        $scholarships = Scholarship::searchPublished(
            $keyword,
            $major,
            $minAmount,
            $maxAmount
        );

        // Truyền dữ liệu sang View
        $this->view('scholarships/index', [
            'scholarships' => $scholarships,
            'keyword' => $keyword,
            'major' => $major,
            'minAmount' => $minAmountInput,
            'maxAmount' => $maxAmountInput
        ]);
    }


    // Hiển thị thông tin chi tiết một học bổng

    // Hiển thị chi tiết học bổng
    public function show(string $id): void
    {
        // Kiểm tra ID học bổng hợp lệ
        if (!ctype_digit($id) || (int)$id <= 0) {
            $this->sendNotFound();
            return;
        }

        // Lấy thông tin học bổng
        $scholarshipId = (int)$id;
        $scholarship = Scholarship::findById($scholarshipId);

        // Không tìm thấy học bổng thì trả về trang 404
        if ($scholarship === false) {
            $this->sendNotFound();
            return;
        }

        // Lấy điều kiện ứng tuyển và giấy tờ cần nộp
        $requirements = Scholarship::getRequirements($scholarshipId);
        $documents = Scholarship::getRequiredDocuments($scholarshipId);


        $isSaved = false;

        if (
            !empty($_SESSION['user_id']) &&
            ($_SESSION['user_role'] ?? '') === 'Student'
        ) {
            $isSaved = SavedScholarship::isSaved(
                (int) $_SESSION['user_id'],
                $scholarshipId
            );
        }

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $message = $_SESSION['saved_scholarship_message'] ?? null;
        unset($_SESSION['saved_scholarship_message']);

        // Truyền dữ liệu sang giao diện chi tiết
        $this->view('scholarships/show', [
            'scholarship' => $scholarship,
            'requirements' => $requirements,
            'documents' => $documents,
            'isSaved' => $isSaved,
            'message' => $message
        ]);
    }
}
