
<?php
// Mặc định chưa có người dùng đăng nhập
$currentUser = null;

if (!empty($_SESSION['user_id'])) {
    // Lấy thông tin Student đang đăng nhập
    $db = \App\Core\Database::connect();

    $stmt = $db->prepare("
        SELECT u.user_id, u.role, u.avatar, s.student_name
        FROM users u
        INNER JOIN students s ON s.user_id = u.user_id
        WHERE u.user_id = :user_id
          AND u.role = 'Student'
          AND u.status = 'Active'
        LIMIT 1
    ");

    $stmt->execute([
        'user_id' => $_SESSION['user_id']
    ]);

    $currentUser = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}
?>

<!-- Thanh điều hướng phụ phía trên Header -->
<div class="top-bar">
    <div class="container">
        <div class="top-bar-links">
            <button type="button" class="top-role-btn active" id="student-header-btn">For Students</button>
            <button type="button" class="top-role-btn" id="provider-header-btn">For Scholarship Providers</button>
        </div>
    </div>
</div>

<header class="site-header">
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid px-4 px-xl-5">
            <!-- Logo website -->
            <a class="navbar-brand" href="/">
                <img src="/assets/images/logo.png" alt="Scholarship Logo" class="site-logo-icon">
                <span>Scholarship</span>
            </a>

            <!-- Nút menu trên màn hình nhỏ -->
            <button class="navbar-toggler" type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#main-navbar"
                    aria-controls="main-navbar"
                    aria-expanded="false"
                    aria-label="Open menu">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="main-navbar">
                <!-- Menu được JavaScript thay đổi -->
                <ul class="navbar-nav gap-lg-3" id="main-menu"></ul>

                <!-- Khu vực tài khoản -->
                <div class="header-actions">
                    <?php if ($currentUser): ?>
                        <!-- Tài khoản Student đã đăng nhập -->
                        <div class="dropdown">
                            <button class="btn d-flex align-items-center gap-2 dropdown-toggle"
                                    type="button"
                                    data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                <?php if (!empty($currentUser['avatar'])): ?>
                                    <img src="<?= htmlspecialchars($currentUser['avatar'], ENT_QUOTES, 'UTF-8') ?>"
                                         alt="Avatar"
                                         class="rounded-circle object-fit-cover"
                                         width="36" height="36">
                                <?php else: ?>
                                    <i class="bi bi-person-circle fs-3"></i>
                                <?php endif; ?>

                                <span class="fw-semibold">
                                    <?= htmlspecialchars($currentUser['student_name'], ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </button>

                            <!-- Menu tài khoản -->
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                <li>
                                    <a class="dropdown-item" href="/profile">
                                        <i class="bi bi-person me-2"></i>Thông tin cá nhân
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="/my-applications">
                                        <i class="bi bi-file-earmark-text me-2"></i>Hồ sơ đã nộp
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="/saved-scholarships">
                                        <i class="bi bi-bookmark me-2"></i>Học bổng đã lưu
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="/settings">
                                        <i class="bi bi-gear me-2"></i>Cài đặt tài khoản
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="/logout" method="POST">
                                        <input type="hidden" name="csrf_token"
                                               value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="bi bi-box-arrow-right me-2"></i>Đăng xuất
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <!-- Chưa đăng nhập -->
                        <a class="btn-login" href="/login">Log in</a>
                        <a class="btn-register" href="/register">Sign up</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
</header>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Các thành phần cần thay đổi
    const mainMenu = document.getElementById('main-menu');
    const studentHeaderBtn = document.getElementById('student-header-btn');
    const providerHeaderBtn = document.getElementById('provider-header-btn');
    const howSteps = document.getElementById('how-steps');

    // Menu dành cho Student
    const studentMenu = `
        <li class="nav-item"><a class="nav-link" href="/">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="/scholarships">Scholarships</a></li>
        <li class="nav-item"><a class="nav-link" href="/about">About Us</a></li>
        <li class="nav-item"><a class="nav-link" href="/contact">Contact</a></li>
    `;

    // Menu dành cho Provider
    const providerMenu = `
        <li class="nav-item"><a class="nav-link" href="/">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="/scholarships">Scholarships</a></li>
        <li class="nav-item"><a class="nav-link" href="/how-it-works">How It Works</a></li>
        <li class="nav-item"><a class="nav-link" href="/about">About Us</a></li>
    `;

    // Các bước dành cho Student
    const studentSteps = `
        <div class="how-step">
            <div class="how-step-icon">
                <i class="bi bi-person-plus"></i>
            </div>
            <h3>Create an Account</h3>
            <p>Create your student account to get started.</p>
        </div>

        <div class="how-step">
            <div class="how-step-icon">
                <i class="bi bi-search"></i>
            </div>
            <h3>Find Scholarships</h3>
            <p>Explore scholarship opportunities that suit you.</p>
        </div>

        <div class="how-step">
            <div class="how-step-icon">
                <i class="bi bi-file-earmark-check"></i>
            </div>
            <h3>Submit Application</h3>
            <p>Complete and submit your scholarship application.</p>
        </div>
    `;

    // Các bước dành cho Provider
    const providerSteps = `
        <div class="how-step">
            <div class="how-step-icon">
                <i class="bi bi-building-add"></i>
            </div>
            <h3>Create an Account</h3>
            <p>Register your organization as a scholarship provider.</p>
        </div>

        <div class="how-step">
            <div class="how-step-icon">
                <i class="bi bi-mortarboard"></i>
            </div>
            <h3>Create Scholarships</h3>
            <p>Create scholarship opportunities for students.</p>
        </div>

        <div class="how-step">
            <div class="how-step-icon">
                <i class="bi bi-people"></i>
            </div>
            <h3>Review Applications</h3>
            <p>Review and evaluate submitted student applications.</p>
        </div>
    `;

    // Khôi phục giao diện đã chọn
    const selectedRole = sessionStorage.getItem('publicRole') || 'student';

    function updateRole(role) {
        const isProvider = role === 'provider';

        // Thay đổi menu và nội dung Home
        mainMenu.innerHTML = isProvider ? providerMenu : studentMenu;

        if (howSteps) {
            howSteps.innerHTML = isProvider ? providerSteps : studentSteps;
        }

        // Cập nhật nút đang được chọn
        studentHeaderBtn.classList.toggle('active', !isProvider);
        providerHeaderBtn.classList.toggle('active', isProvider);
    }

    updateRole(selectedRole);

    // Chuyển giao diện Student
    studentHeaderBtn.addEventListener('click', function () {
        sessionStorage.setItem('publicRole', 'student');
        updateRole('student');
    });

    // Chuyển giao diện Provider
    providerHeaderBtn.addEventListener('click', function () {
        sessionStorage.setItem('publicRole', 'provider');
        updateRole('provider');
    });
});
</script>
