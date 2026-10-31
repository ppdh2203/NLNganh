<?php

declare(strict_types=1);

/** @var array $scholarships */
/** @var string|null $success */

$e = static fn($value): string =>
htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

$tabs = [
    'Pending' => 'Chờ duyệt',
    'Published' => 'Đã duyệt',
    'Rejected' => 'Đã từ chối'
];

$counts = array_fill_keys(array_keys($tabs), 0);

foreach ($scholarships as $item) {
    $status = (string) ($item['status'] ?? '');

    if (isset($counts[$status])) {
        $counts[$status]++;
    }
}
?>

<section class="provider-dashboard">

    <div class="d-flex flex-wrap justify-content-between
                align-items-center gap-3 mb-4">

        <div>
            <h2 class="h4 fw-bold mb-2">Quản lý học bổng</h2>
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

        <div class="d-flex flex-wrap justify-content-between
                    align-items-center gap-2 mb-4">
            <h3 class="h6 fw-bold mb-0">
                Danh sách học bổng
            </h3>

            <span class="text-muted small">
                Tổng: <?= count($scholarships) ?> học bổng
            </span>
        </div>

        <!-- Ba tab trạng thái -->
        <ul class="nav nav-tabs provider-scholarship-tabs mb-4"
            id="scholarshipTabs" role="tablist">

            <?php foreach ($tabs as $status => $label): ?>
                <li class="nav-item" role="presentation">
                    <button
                        class="nav-link <?= $status === 'Pending' ? 'active' : '' ?>"
                        id="tab-<?= $e($status) ?>"
                        data-bs-toggle="tab"
                        data-bs-target="#panel-<?= $e($status) ?>"
                        type="button"
                        role="tab"
                        aria-controls="panel-<?= $e($status) ?>"
                        aria-selected="<?= $status === 'Pending' ? 'true' : 'false' ?>">

                        <?= $e($label) ?>

                        <span class="provider-tab-count">
                            <?= $counts[$status] ?>
                        </span>
                    </button>
                </li>
            <?php endforeach; ?>

        </ul>

        <div class="tab-content" id="scholarshipTabContent">

            <?php foreach ($tabs as $status => $label): ?>

                <?php
                $filtered = array_values(array_filter(
                    $scholarships,
                    static fn(array $item): bool => ($item['status'] ?? '') === $status
                ));
                ?>

                <div
                    class="tab-pane fade <?= $status === 'Pending' ? 'show active' : '' ?>"
                    id="panel-<?= $e($status) ?>"
                    role="tabpanel"
                    aria-labelledby="tab-<?= $e($status) ?>">

                    <?php if (empty($filtered)): ?>

                        <div class="text-center py-5">
                            <i class="bi bi-inbox fs-1"
                                style="color:#a5a0d8;"></i>

                            <p class="text-muted mt-3 mb-0">
                                Chưa có học bổng nào trong mục
                                <?= $e(mb_strtolower($label, 'UTF-8')) ?>.
                            </p>
                        </div>

                    <?php else: ?>

                        <div class="table-responsive">
                            <table class="table align-middle provider-scholarship-table">

                                <thead>
                                    <tr>
                                        <th>Học bổng</th>
                                        <th>Giá trị</th>
                                        <th>Thời gian nhận hồ sơ</th>
                                        <th>Trạng thái</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php foreach ($filtered as $item): ?>
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
                                                    <?= $e(date(
                                                        'd/m/Y',
                                                        strtotime($item['start_date'])
                                                    )) ?>
                                                </div>

                                                <small class="text-muted">
                                                    đến
                                                    <?= $e(date(
                                                        'd/m/Y',
                                                        strtotime($item['end_date'])
                                                    )) ?>
                                                </small>
                                            </td>
                                            <td>
                                                <?php
                                                $statusInfo = match ($item['status']) {
                                                    'Pending' => ['Chờ duyệt', 'warning'],
                                                    'Published' => ['Đã duyệt', 'success'],
                                                    'Rejected' => ['Đã từ chối', 'danger'],
                                                    default => ['Không xác định', 'secondary']
                                                };

                                                [$statusText, $statusColor] = $statusInfo;
                                                ?>

                                                <span class="badge text-bg-<?= $statusColor ?>">
                                                    <?= $e($statusText) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="/provider/scholarships/<?= (int) $item['scholarship_id'] ?>"
                                                    class="provider-view-link">
                                                    <i class="bi bi-eye"></i>
                                                    <span>Chi tiết</span>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>

                            </table>
                        </div>

                    <?php endif; ?>

                </div>

            <?php endforeach; ?>

        </div>
    </div>
</section>