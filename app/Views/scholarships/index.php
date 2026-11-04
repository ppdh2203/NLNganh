<main>
    <!-- Giới thiệu trang học bổng -->
    <section class="py-5 text-center" style="background:var(--light-blue);">
        <div class="container">
            <h1 class="fw-bold mb-3">Khám phá học bổng</h1>
            <p class="mb-0" style="color:var(--text-muted);">
                Khám phá những cơ hội học bổng phù hợp,
                đồng hành cùng bạn trên hành trình học tập.
            </p>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <!-- Giao diện tìm kiếm ngữ nghĩa, sẽ tích hợp AI sau -->
            <div class="bg-white border rounded-4 p-4 mb-4">
                <h2 class="h5 fw-bold mb-2">Gợi ý học bổng phù hợp</h2>
                <p class="small mb-3" style="color:var(--text-muted);">
                    Mô tả nhu cầu của bạn để tìm những học bổng phù hợp.
                    Chức năng gợi ý thông minh sẽ được phát triển sau.
                </p>
                <p class="small mb-3" style="color:var(--text-muted);">Describe your needs to find suitable scholarship opportunities.</p>
                <div class="d-flex gap-2 align-items-stretch">
                    <input type="text"
                        class="form-control flex-grow-1"
                        style="min-width:0;"
                        placeholder="Ví dụ: Tôi là sinh viên CNTT cần hỗ trợ học phí..."
                        aria-label="Mô tả nhu cầu học bổng"
                        disabled>

                    <button type="button"
                        class="btn text-white flex-shrink-0 px-4"
                        style="background:var(--primary);"
                        disabled>
                        <i class="bi bi-stars me-1"></i>
                        Gợi ý
                    </button>
                </div>
            </div>

            <!-- Danh sách học bổng -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="h4 fw-bold mb-0">Danh sách học bổng</h2>
                <span class="text-muted">
                    <?= count($scholarships ?? []) ?> học bổng
                </span>
            </div>

            <div class="row g-4">
                <?php foreach (($scholarships ?? []) as $scholarship): ?>
                    <div class="col-12 col-md-6 col-lg-4">
                        <article class="scholarship-card h-100">
                            <div class="scholarship-card-icon"><i class="bi bi-mortarboard"></i></div>
                            <h3><?= htmlspecialchars($scholarship['scholarship_name'], ENT_QUOTES, 'UTF-8') ?></h3>
                            <p class="scholarship-provider"><?= htmlspecialchars($scholarship['provider_name'], ENT_QUOTES, 'UTF-8') ?></p>
                            <div class="scholarship-info">
                                <span><i class="bi bi-cash-coin"></i> <?= number_format((float)$scholarship['amount'], 0, ',', '.') ?> VND</span>
                                <span>
                                    <i class="bi bi-calendar-event"></i>
                                    Hạn nộp: <?= date('d/m/Y', strtotime($scholarship['end_date'])) ?>
                                </span>
                            </div>
                            <a href="/scholarships/<?= (int)$scholarship['scholarship_id'] ?>" class="scholarship-detail">
                                Xem chi tiết <i class="bi bi-arrow-right"></i>
                            </a>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (empty($scholarships)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                    Hiện chưa có học bổng nào đang nhận hồ sơ.
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>