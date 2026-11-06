<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\SavedScholarship;
use App\Models\Scholarship;

class SavedScholarshipController extends Controller
{
    public function index(): void
    {
        $user = $this->requireActiveUser('Student');

        $this->ensureCsrfToken();

        $scholarships = SavedScholarship::getByUser(
            (int) $user['user_id']
        );

        $message = $_SESSION['saved_scholarship_message'] ?? null;
        unset($_SESSION['saved_scholarship_message']);

        $this->view('student/saved-scholarships', [
            'scholarships' => $scholarships,
            'message' => $message
        ]);
    }

    public function save(string $id): void
    {
        $user = $this->requireActiveUser('Student');
        $this->validateCsrf();

        if (!ctype_digit($id) || (int) $id <= 0) {
            $this->sendNotFound();
            return;
        }

        $scholarshipId = (int) $id;

        if (Scholarship::findById($scholarshipId) === false) {
            $this->sendNotFound();
            return;
        }

        SavedScholarship::save(
            (int) $user['user_id'],
            $scholarshipId
        );

        $_SESSION['saved_scholarship_message'] =
            'Đã lưu học bổng thành công.';

        header('Location: /scholarships/' . $scholarshipId, true, 303);
        exit;
    }

    public function remove(string $id): void
    {
        $user = $this->requireActiveUser('Student');
        $this->validateCsrf();

        if (!ctype_digit($id) || (int) $id <= 0) {
            $this->sendNotFound();
            return;
        }

        $scholarshipId = (int) $id;

        SavedScholarship::remove(
            (int) $user['user_id'],
            $scholarshipId
        );

        $_SESSION['saved_scholarship_message'] =
            'Đã bỏ lưu học bổng.';

        $returnTo = $_POST['return_to'] ?? '';

        $destination = $returnTo === 'saved'
            ? '/saved-scholarships'
            : '/scholarships/' . $scholarshipId;

        header('Location: ' . $destination, true, 303);
        exit;
    }

    private function ensureCsrfToken(): void
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    private function validateCsrf(): void
    {
        $token = $_POST['csrf_token'] ?? '';

        if (
            !is_string($token) ||
            !isset($_SESSION['csrf_token']) ||
            !is_string($_SESSION['csrf_token']) ||
            !hash_equals($_SESSION['csrf_token'], $token)
        ) {
            http_response_code(403);
            exit('Yêu cầu không hợp lệ.');
        }
    }
}
