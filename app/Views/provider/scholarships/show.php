<?php

declare(strict_types=1);

/** @var array $scholarship */
/** @var array|false $requirements */
/** @var array $documents */

$e = static fn($value): string =>
htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');

$statusInfo = match ($scholarship['status']) {
    'Pending' => ['Chờ duyệt', 'warning'],
    'Published' => ['Đã duyệt', 'success'],
    'Rejected' => ['Đã từ chối', 'danger'],
    default => ['Không xác định', 'secondary']
};

[$statusText, $statusColor] = $statusInfo;

$formatDate = static fn($value): string =>
!empty($value) ? date('d/m/Y', strtotime($value)) : '—';
?>

<section class="provider-dashboard">

    <div class="mb-4">
        <a href="/provider/scholarships"
            class="text-decoration-none text-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Quay lại quản lý học bổng
        </a>

        <div class="d-flex flex-wrap justify-content-between
                    align-items-center gap-3 mt-3">

            <h2 class="h4 fw-bold mb-0">
                <?= $e($scholarship['scholarship_name']) ?>
            </h2>


            <span class="badge text-bg-<?= $statusColor ?>">
                <?= $e($statusText) ?>
            </span>

        </div>
    </div>

    <!-- Thông tin học bổng -->
    <div class="provider-card mb-4">
        <h3 class="h6 fw-bold mb-4">
            <i class="bi bi-mortarboard me-2"></i>
            Thông tin học bổng
        </h3>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="text-muted small mb-1">
                    Giá trị học bổng
                </div>
                <div class="fw-semibold">
                    <?= number_format(
                        (float) $scholarship['amount'],
                        0,
                        ',',
                        '.'
                    ) ?> đ
                </div>
            </div>

            <div class="col-md-4">
                <div class="text-muted small mb-1">
                    Ngày bắt đầu
                </div>
                <div class="fw-semibold">
                    <?= $e($formatDate($scholarship['start_date'])) ?>
                </div>
            </div>

            <div class="col-md-4">
                <div class="text-muted small mb-1">
                    Hạn nộp hồ sơ
                </div>
                <div class="fw-semibold">
                    <?= $e($formatDate($scholarship['end_date'])) ?>
                </div>
            </div>

            <div class="col-12">
                <div class="text-muted small mb-2">
                    Mô tả học bổng
                </div>
                <div style="white-space:pre-line;"><?= $e($scholarship['description']) ?></div>
            </div>
        </div>
    </div>



    <!-- Lý do từ chối học bổng -->
    <?php if ($scholarship['status'] === 'Rejected'): ?>
        <div class="alert alert-danger mb-4"
            role="alert">

            <div class="d-flex align-items-center gap-2 mb-2">
                <i class="bi bi-exclamation-circle-fill fs-5"></i>
                <h3 class="h6 fw-bold mb-0">
                    Lý do từ chối học bổng
                </h3>
            </div>

            <div style="white-space: pre-line;">
                <?= $e(
                    $scholarship['rejection_reason']
                        ?: 'Admin chưa cung cấp lý do từ chối.'
                ) ?>
            </div>

        </div>
    <?php endif; ?>

    <!-- Điều kiện ứng tuyển -->
    <div class="provider-card mb-4">
        <h3 class="h6 fw-bold mb-4">
            <i class="bi bi-clipboard-check me-2"></i>
            Điều kiện ứng tuyển
        </h3>

        <div class="row g-3">
            <div class="col-md-4">
                <div class="text-muted small mb-1">GPA tối thiểu</div>
                <div class="fw-semibold">
                    <?= $e($requirements['required_gpa'] ?? 'Không yêu cầu') ?>
                </div>
            </div>

            <div class="col-md-4">
                <div class="text-muted small mb-1">Năm học</div>
                <div class="fw-semibold">
                    <?= $e($requirements['required_year'] ?? null ?: 'Không yêu cầu') ?>
                </div>
            </div>

            <div class="col-md-4">
                <div class="text-muted small mb-1">Ngành học</div>
                <div class="fw-semibold">
                    <?= $e($requirements['required_major'] ?? null ?: 'Không yêu cầu') ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Giấy tờ yêu cầu -->
    <div class="provider-card mb-4">
        <h3 class="h6 fw-bold mb-4">
            <i class="bi bi-file-earmark-text me-2"></i>
            Giấy tờ yêu cầu
        </h3>

        <?php if (empty($documents)): ?>
            <p class="text-muted mb-0">
                Học bổng này không yêu cầu giấy tờ bổ sung.
            </p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Tên giấy tờ</th>
                            <th>Mô tả</th>
                            <th>Yêu cầu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documents as $document): ?>
                            <tr>
                                <td class="fw-semibold">
                                    <?= $e($document['document_name']) ?>
                                </td>
                                <td>
                                    <?= $e($document['description'] ?: '—') ?>
                                </td>
                                <td>
                                    <?php if ($document['is_required']): ?>
                                        <span class="badge text-bg-danger">
                                            Bắt buộc
                                        </span>
                                    <?php else: ?>
                                        <span class="badge text-bg-secondary">
                                            Không bắt buộc
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    <?php if ($scholarship['status'] === 'Rejected'): ?>
        <div class="d-flex justify-content-end mt-3 mb-4">
            <a href="/provider/scholarships/<?= (int) $scholarship['scholarship_id'] ?>/edit"
                class="btn btn-primary px-4 py-2">
                <i class="bi bi-pencil-square me-2"></i>
                Chỉnh sửa học bổng
            </a>
        </div>
    <?php endif; ?>
</section>