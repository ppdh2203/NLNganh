<?php

declare(strict_types=1);

$statuses = [
    'Pending' => ['Chờ duyệt', 'warning'],
    'Published' => ['Đã phê duyệt', 'success'],
    'Rejected' => ['Đã từ chối', 'danger']
];

$e = static fn($value): string =>
htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>

<section class="provider-dashboard">

    <div class="d-flex flex-wrap justify-content-between
                align-items-center gap-3 mb-4">

        <div>
            <h2 class="h4 fw-bold mb-2">
                Quản lý học bổng
            </h2>
            <p class="text-muted mb-0">
                Theo dõi danh sách và trạng thái xét duyệt
                các học bổng đã đăng.
            </p>
        </div>

        <a href="/provider/scholarships/create"
            class="btn btn-provider">
            <i class="bi bi-plus-circle me-1"></i>
            Đăng học bổng mới
        </a>
    </div>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success alert-dismissible fade show"
            role="alert">
            <i class="bi bi-check-circle me-2"></i>
            <?= $e($success) ?>

            <button type="button"
                class="btn-close"
                data-bs-dismiss="alert"
                aria-label="Đóng"></button>
        </div>
    <?php endif; ?>

    <div class="provider-card">

        <div class="d-flex justify-content-between
                    align-items-center mb-4">

            <h3 class="h6 fw-bold mb-0">
                Danh sách học bổng
            </h3>

            <span class="text-muted small">
                Tổng: <?= count($scholarships) ?> học bổng
            </span>
        </div>

        <?php if (empty($scholarships)): ?>

            <div class="text-center py-5">
                <i class="bi bi-mortarboard fs-1"
                    style="color:#6052db;"></i>

                <h4 class="h6 fw-bold mt-3">
                    Chưa có học bổng nào
                </h4>

                <p class="text-muted">
                    Bạn chưa đăng học bổng nào trên hệ thống.
                </p>

                <a href="/provider/scholarships/create"
                    class="btn btn-provider">
                    Đăng học bổng đầu tiên
                </a>
            </div>

        <?php else: ?>

            <div class="table-responsive">
                <table class="table align-middle">

                    <thead>
                        <tr>
                            <th>Học bổng</th>
                            <th>Giá trị</th>
                            <th>Thời gian nhận hồ sơ</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($scholarships as $item): ?>

                            <?php
                            $status = $item['status'];

                            [$statusText, $statusColor] =
                                $statuses[$status]
                                ?? ['Không xác định', 'secondary'];
                            ?>

                            <tr>
                                <td>
                                    <div class="fw-semibold">
                                        <?= $e($item['scholarship_name']) ?>
                                    </div>
                                </td>

                                <td class="fw-semibold">
                                    <?= number_format(
                                        (float) $item['amount'],
                                        0,
                                        ',',
                                        '.'
                                    ) ?> đ
                                </td>

                                <td>
                                    <div>
                                        <?= $e($item['start_date']) ?>
                                    </div>

                                    <small class="text-muted">
                                        đến <?= $e($item['end_date']) ?>
                                    </small>
                                </td>

                                <td>
                                    <span class="badge text-bg-<?= $statusColor ?>">
                                        <?= $statusText ?>
                                    </span>
                                </td>
                            </tr>

                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php endif; ?>

    </div>
</section>