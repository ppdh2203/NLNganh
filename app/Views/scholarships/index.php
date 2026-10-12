
<main>
    <!-- Giới thiệu trang học bổng -->
    <section class="py-5 text-center" style="background:var(--light-blue);">
        <div class="container">
            <h1 class="fw-bold mb-3">Explore Scholarships</h1>
            <p class="mb-0" style="color:var(--text-muted);">Discover scholarship opportunities that support your academic journey.</p>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <!-- Giao diện tìm kiếm ngữ nghĩa, sẽ tích hợp AI sau -->
            <div class="bg-white border rounded-4 p-4 mb-4">
                <h2 class="h5 fw-bold mb-2">Find Your Scholarship</h2>
                <p class="small mb-3" style="color:var(--text-muted);">Describe your needs to find suitable scholarship opportunities.</p>
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="E.g. I am an IT student looking for financial support..." aria-label="Describe your scholarship needs" disabled>
                    <button type="button" class="btn text-white" style="background:var(--primary);" disabled>
                        <i class="bi bi-search"></i> Search
                    </button>
                </div>
            </div>

            <!-- Danh sách học bổng -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="h4 fw-bold mb-0">Available Scholarships</h2>
                <span class="text-muted"><?= count($scholarships ?? []) ?> scholarships</span>
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
                                <span><i class="bi bi-calendar-event"></i> Deadline: <?= date('d/m/Y', strtotime($scholarship['end_date'])) ?></span>
                            </div>
                            <a href="/scholarships/<?= (int)$scholarship['scholarship_id'] ?>" class="scholarship-detail">
                                View Details <i class="bi bi-arrow-right"></i>
                            </a>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (empty($scholarships)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                    No scholarships are currently available.
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>
