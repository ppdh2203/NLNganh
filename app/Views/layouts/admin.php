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
        document.querySelectorAll('[data-admin-tab]').forEach(button => {
            button.addEventListener('click', () => {
                const tab = button.dataset.adminTab;
                document.querySelectorAll('[data-admin-tab]').forEach(item => item.classList.toggle('active', item === button));
                document.querySelectorAll('[data-admin-panel]').forEach(panel => {
                    panel.hidden = panel.dataset.adminPanel !== tab;
                });
            });
        });
    </script>
</body>

</html>