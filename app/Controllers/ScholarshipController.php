<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Scholarship;

class ScholarshipController extends Controller
{
    public function index(): void
    {
        // Lấy danh sách học bổng đang công khai
        $scholarships = Scholarship::getPublished(100);

        // Truyền dữ liệu sang trang danh sách
        $this->view('scholarships/index', [
            'scholarships' => $scholarships
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

        // Truyền dữ liệu sang giao diện chi tiết
        $this->view('scholarships/show', [
            'scholarship' => $scholarship,
            'requirements' => $requirements,
            'documents' => $documents
        ]);
    }

}
