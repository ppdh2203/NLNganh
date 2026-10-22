<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use PDO;

class ProviderController extends Controller
{
    public function index(): void
    {
        // Chỉ Provider Active mới được truy cập
        $user = $this->requireActiveUser('Provider');

        $userId = (int) $user['user_id'];
        $db = Database::connect();

        // Lấy thông tin nhà cung cấp
        $stmt = $db->prepare("
            SELECT
                provider_name,
                organization,
                provider_logo
            FROM providers
            WHERE user_id = :user_id
            LIMIT 1
        ");

        $stmt->execute(['user_id' => $userId]);
        $provider = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$provider) {
            http_response_code(404);
            exit('Không tìm thấy thông tin nhà cung cấp.');
        }

        // Thống kê học bổng của nhà cung cấp
        $stmt = $db->prepare("
            SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) AS pending,
                SUM(CASE WHEN status = 'Published' THEN 1 ELSE 0 END) AS published,
                SUM(CASE WHEN status = 'Rejected' THEN 1 ELSE 0 END) AS rejected
            FROM scholarships
            WHERE user_id = :user_id
        ");

        $stmt->execute(['user_id' => $userId]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->providerView('provider/dashboard', [
            'provider' => $provider,
            'stats' => $stats
        ]);
    }
}