<?php
/** @var array<string, mixed> $provider */
/** @var string $content */
declare(strict_types=1);


$providerName = $provider['organization']
    ?: $provider['provider_name']
    ?: 'Nhà cung cấp';

?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Nhà cung cấp - Quản lý học bổng</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        rel="stylesheet">

    <link rel="stylesheet" href="/assets/css/app.css">

    <style>
        .provider-layout {
            min-height: 100vh;
            display: flex;
            background: #f7f7fc;
        }

        .provider-sidebar {
            width: 260px;
            flex-shrink: 0;
            background: #fff;
            border-right: 1px solid #e8e7f0;
            padding: 24px 16px;
        }

        .provider-brand {
            color: #6052db;
            font-weight: 700;
            font-size: 22px;
            margin-bottom: 32px;
        }

        .provider-nav {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .provider-nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 14px;
            border-radius: 10px;
            color: #475569;
            text-decoration: none;
            font-weight: 500;
        }

        .provider-nav a:hover,
        .provider-nav a.active {
            background: #efedff;
            color: #6052db;
        }

        .provider-main {
            flex: 1;
            min-width: 0;
            padding: 32px;
        }

        .provider-card {
            background: #fff;
            border: 1px solid #e8e7f0;
            border-radius: 14px;
            padding: 24px;
        }

        @media (max-width: 767px) {
            .provider-layout {
                flex-direction: column;
            }

            .provider-sidebar {
                width: 100%;
            }

            .provider-main {
                padding: 16px;
            }
        }
    </style>
</head>

<body>

    <div class="provider-layout">

        <aside class="provider-sidebar">
            <div class="provider-brand">
                <i class="bi bi-mortarboard-fill me-2"></i>
                Scholarship
            </div>

            <nav class="provider-nav">
                <a href="/provider" class="active">
                    <i class="bi bi-grid"></i>
                    Tổng quan
                </a>

                <a href="/provider/scholarships">
                    <i class="bi bi-journal-text"></i>
                    Quản lý học bổng
                </a>

                <a href="/provider/scholarships/create">
                    <i class="bi bi-plus-circle"></i>
                    Đăng học bổng
                </a>

                <a href="/provider/profile">
                    <i class="bi bi-person"></i>
                    Hồ sơ nhà cung cấp
                </a>
            </nav>
        </aside>

        <main class="provider-main">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <div class="text-muted small">Khu vực nhà cung cấp</div>
                    <div class="fw-semibold">
                        <?= htmlspecialchars($providerName, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                </div>

                <form method="POST" action="/logout">
                    <button type="submit" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-box-arrow-right me-1"></i>
                        Đăng xuất
                    </button>
                </form>
            </div>

            <?= $content ?>

        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>