<?php

declare(strict_types=1);

namespace App\Controllers;
use App\Core\Database;

class LoginController extends Controller
{
    public function showLogin(): void
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
       $this->view('auth/login');
    }

    public function login(): void
    {
        // Kiểm tra CSRF token
        $submittedToken = $_POST['csrf_token'] ?? '';

        if (
            !is_string($submittedToken) ||
            empty($_SESSION['csrf_token']) ||
            !hash_equals($_SESSION['csrf_token'], $submittedToken)
        ) {
            http_response_code(403);
            exit('Yêu cầu không hợp lệ. Vui lòng tải lại trang.');
        }

        // Kiểm tra kiểu dữ liệu trước khi xử lý
        $emailInput = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (!is_string($emailInput) || !is_string($password)) {
            http_response_code(400);
            exit('Dữ liệu đăng nhập không hợp lệ.');
        }

        $email = trim($emailInput);

        // Kiểm tra email và mật khẩu
        if (
            $email === '' ||
            $password === '' ||
            !filter_var($email, FILTER_VALIDATE_EMAIL)
        ) {
            exit('Email hoặc mật khẩu không hợp lệ.');
        }

        // Tìm tài khoản theo email
        $db = Database::connect();

        $stmt = $db->prepare("
            SELECT user_id, email, password_hash, role, status
            FROM users
            WHERE email = :email
            LIMIT 1
        ");

        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        // Kiểm tra tài khoản và mật khẩu
        if (!$user || !password_verify($password, $user['password_hash'])) {
            exit('Email hoặc mật khẩu không chính xác.');
        }

        // Chỉ cho phép tài khoản Active đăng nhập
        if ($user['status'] !== 'Active') {
            exit('Tài khoản chưa được kích hoạt hoặc đã bị khóa.');
        }

        // Tạo phiên đăng nhập mới để tránh session fixation
        session_regenerate_id(true);

        // Lưu thông tin xác thực vào session
        $_SESSION['user_id'] = (int) $user['user_id'];
        $_SESSION['user_role'] = $user['role'];

        // Thông báo đăng nhập thành công
        $_SESSION['login_success'] = true;

        // Chuyển về Trang chủ
        header('Location: /');
        exit;
    }


    public function logout(): void
    {
        // Chỉ xử lý khi người dùng đã đăng nhập
        if (empty($_SESSION['user_id'])) {
            header('Location: /');
            exit;
        }

        // Kiểm tra CSRF token
        $submittedToken = $_POST['csrf_token'] ?? '';

        if (
            !is_string($submittedToken) ||
            !isset($_SESSION['csrf_token']) ||
            !is_string($_SESSION['csrf_token']) ||
            !hash_equals($_SESSION['csrf_token'], $submittedToken)
        ) {
            http_response_code(403);
            exit('Yêu cầu không hợp lệ.');
        }

        // Xóa thông tin đăng nhập và tạo session ID mới
        unset($_SESSION['user_id'], $_SESSION['user_role']);
        session_regenerate_id(true);

        // Tạo thông báo đăng xuất thành công
        $_SESSION['logout_success'] = true;

        // Chuyển về Trang chủ
        header('Location: /');
        exit;
    }
}