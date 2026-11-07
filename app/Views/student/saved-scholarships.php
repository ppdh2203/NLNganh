<?php

declare(strict_types=1);

/** @var array $scholarships */
/** @var string|null $message */

$e = static fn($value): string =>
htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');

?>

<main class="student-saved-page">

    <!-- Dải tiêu đề riêng -->
    <section class="student-saved-hero">
        <div class="container">

            <a href="/scholarships"
                class="text-decoration-none d-inline-block">
                <i class="bi bi-arrow-left me-1"></i>
                Quay lại
            </a>

            <h1 class="h4 fw-bold mt-3 mb-0">
                HỌC BỔNG ĐÃ LƯU
            </h1>

        </div>
    </section>

    <!-- Nội dung trang -->
    <section class="student-saved-content">
        <div class="container">

            <!-- Thông báo -->
            <?php if (!empty($message)): ?>
                <div class="alert alert-success alert-dismissible fade show"
                    role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <?= $e($message) ?>

                    <button type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                        aria-label="Đóng"></button>
                </div>
            <?php endif; ?>

            <?php if (empty($scholarships)): ?>

                <!-- Chưa lưu học bổng -->
                <div class="text-center border rounded-4 p-5 bg-white">
                    <i class="bi bi-heart fs-1 text-muted"></i>

                    <h3 class="h5 fw-bold mt-3">
                        Bạn chưa lưu học bổng nào
                    </h3>

                    <p class="text-muted">
                        Khám phá và lưu những học bổng bạn quan tâm.
                    </p>

                    <a href="/scholarships"
                        class="btn text-white"
                        style="background:var(--primary);">
                        Khám phá học bổng
                    </a>
                </div>

            <?php else: ?>

                <!-- Danh sách học bổng -->
                <div class="row g-4">

                    <?php foreach ($scholarships as $scholarship): ?>

                        <?php
                        $id = (int) $scholarship['scholarship_id'];

                        $available =
                            $scholarship['status'] === 'Published'
                            && strtotime($scholarship['start_date']) <= time()
                            && strtotime($scholarship['end_date'] . ' 23:59:59') >= time();
                        ?>

                        <div class="col-12 col-md-6 col-lg-4">
                            <article class="scholarship-card h-100">

                                <div class="scholarship-card-icon">
                                    <i class="bi bi-mortarboard"></i>
                                </div>

                                <h3>
                                    <?= $e($scholarship['scholarship_name']) ?>
                                </h3>

                                <p class="scholarship-provider">
                                    <?= $e($scholarship['provider_name']) ?>
                                </p>

                                <div class="scholarship-info">

                                    <span>
                                        <i class="bi bi-cash-coin"></i>
                                        <?= number_format(
                                            (float) $scholarship['amount'],
                                            0,
                                            ',',
                                            '.'
                                        ) ?> VND
                                    </span>

                                    <span>
                                        <i class="bi bi-calendar-event"></i>
                                        Hạn nộp:
                                        <?= date(
                                            'd/m/Y',
                                            strtotime($scholarship['end_date'])
                                        ) ?>
                                    </span>

                                </div>

                                <!-- Trạng thái học bổng -->
                                <p class="small mt-3 mb-3">
                                    <?php if ($available): ?>
                                        <span class="text-success">
                                            <i class="bi bi-check-circle me-1"></i>
                                            Đang nhận hồ sơ
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">
                                            <i class="bi bi-clock-history me-1"></i>
                                            Hiện không nhận hồ sơ
                                        </span>
                                    <?php endif; ?>
                                </p>

                                <!-- Nút thao tác -->
                                <div class="d-flex justify-content-between align-items-center gap-3 mt-3">

                                    <?php if ($available): ?>
                                        <a href="/scholarships/<?= $id ?>"
                                            class="scholarship-detail mb-0">
                                            Xem chi tiết
                                            <i class="bi bi-arrow-right"></i>
                                        </a>
                                    <?php else: ?>
                                        <span></span>
                                    <?php endif; ?>

                                    <form method="POST"
                                        action="/scholarships/<?= $id ?>/unsave"
                                        class="mb-0">

                                        <input type="hidden"
                                            name="csrf_token"
                                            value="<?= $e($_SESSION['csrf_token'] ?? '') ?>">

                                        <input type="hidden"
                                            name="return_to"
                                            value="saved">

                                        <button type="submit"
                                            class="btn btn-outline-danger btn-sm">
                                            <i class="bi bi-heart-fill me-1"></i>
                                            Bỏ lưu
                                        </button>

                                    </form>

                                </div>

                            </article>
                        </div>

                    <?php endforeach; ?>

                </div>


            <?php endif; ?>

            <!-- Tổng số học bổng đã lưu -->
            <div class="saved-total d-flex justify-content-end">
                <p class="fw-semibold mb-0"
                    style="color:#303741; font-size:16px;">
                    Tổng học bổng đã lưu:
                    <span style="color:var(--primary);">
                        <?= count($scholarships) ?>
                    </span>
                </p>
            </div>

        </div>
    </section>

</main>