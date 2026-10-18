<?php

/** @var string $content */
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản trị hệ thống</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>

<body class="admin-body">
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <a href="/admin" class="admin-brand">
                <img src="/assets/images/logo.png" alt="Scholarship" class="admin-logo">
                <span>Scholarship</span>
            </a>
            <div class="admin-menu-label">QUẢN TRỊ HỆ THỐNG</div>
            <nav class="admin-nav" aria-label="Điều hướng quản trị">
                <button type="button" class="admin-nav-item active" data-admin-tab="overview"><i class="bi bi-grid-1x2"></i> Tổng quan</button>
                <button type="button" class="admin-nav-item" data-admin-tab="users"><i class="bi bi-people"></i> Quản lý tài khoản</button>
                <button type="button" class="admin-nav-item" data-admin-tab="providers"><i class="bi bi-building-check"></i> Duyệt nhà cung cấp</button>
                <button type="button" class="admin-nav-item" data-admin-tab="reviews"><i class="bi bi-patch-check"></i> Duyệt học bổng</button>
                <button type="button" class="admin-nav-item" data-admin-tab="scholarships"><i class="bi bi-mortarboard"></i> Quản lý học bổng</button>
            </nav>
            <div class="admin-sidebar-bottom">
                <form action="/logout" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit" class="admin-nav-item"><i class="bi bi-box-arrow-right"></i> Đăng xuất</button>
                </form>
            </div>
        </aside>
        <div class="admin-main">
            <header class="admin-topbar">
                <div>
                    <h1 class="h5 fw-bold mb-1">Quản trị hệ thống</h1>
                    <span class="text-muted small">Hệ thống quản lý học bổng</span>
                </div>
                <div class="admin-profile"><i class="bi bi-person-circle"></i><span>Administrator</span></div>
            </header>
            <main class="admin-content"><?= $content ?></main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const tabButtons = document.querySelectorAll('[data-admin-tab]');
        const tabPanels = document.querySelectorAll('[data-admin-panel]');

        // Hàm hiển thị tab được chọn
        function openAdminTab(tabName) {
            const selectedPanel = document.querySelector(
                `[data-admin-panel="${tabName}"]`
            );

            // Nếu tab không tồn tại thì không thực hiện
            if (!selectedPanel) return;

            // Cập nhật trạng thái các nút Sidebar
            tabButtons.forEach(button => {
                button.classList.toggle(
                    'active',
                    button.dataset.adminTab === tabName
                );
            });

            // Chỉ hiển thị nội dung của tab được chọn
            tabPanels.forEach(panel => {
                panel.hidden = panel.dataset.adminPanel !== tabName;
            });
        }

        // Khi bấm vào một mục trên Sidebar
        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                const tabName = button.dataset.adminTab;

                openAdminTab(tabName);

                // Cập nhật URL nhưng không tải lại trang
                const url = new URL(window.location.href);
                url.searchParams.set('tab', tabName);
                window.history.replaceState({}, '', url);
            });
        });

        // Khi tải trang, đọc tab từ URL
        const params = new URLSearchParams(window.location.search);
        const currentTab = params.get('tab') || 'overview';

        // Mở đúng tab đang được lưu trên URL
        openAdminTab(currentTab);
    </script>
</body>

</html>