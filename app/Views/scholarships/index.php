<main>
    <!-- Giới thiệu trang học bổng -->

    <!-- Banner trang khám phá học bổng -->
    <section class="scholarship-banner">
        <div id="scholarshipBanner"
            class="carousel slide carousel-fade"
            data-bs-ride="carousel"
            data-bs-interval="5000">

            <div class="carousel-indicators">
                <button type="button"
                    data-bs-target="#scholarshipBanner"
                    data-bs-slide-to="0"
                    class="active"
                    aria-current="true"
                    aria-label="Banner 1"></button>

                <button type="button"
                    data-bs-target="#scholarshipBanner"
                    data-bs-slide-to="1"
                    aria-label="Banner 2"></button>
            </div>

            <div class="carousel-inner">

                <!-- Banner 3 -->
                <div class="carousel-item active">
                    <img src="/assets/images/banner3.jpg"
                        class="d-block w-100"
                        alt="Khám phá học bổng">

                    <div class="scholarship-banner-overlay"></div>

                    <div class="carousel-caption scholarship-banner-caption">
                        <h1>Khám phá học bổng</h1>
                        <p>
                            Khám phá những cơ hội học bổng phù hợp,
                            đồng hành cùng bạn trên hành trình học tập.
                        </p>
                    </div>
                </div>

                <!-- Banner 4 -->
                <div class="carousel-item">
                    <img src="/assets/images/banner4.jpg"
                        class="d-block w-100"
                        alt="Cơ hội học bổng dành cho sinh viên">

                    <div class="scholarship-banner-overlay"></div>

                    <div class="carousel-caption scholarship-banner-caption">
                        <h2>Chắp cánh ước mơ</h2>
                        <p>
                            Tiếp cận những cơ hội học bổng,
                            phát triển năng lực và chinh phục tương lai.
                        </p>
                    </div>
                </div>

            </div>

            <button class="carousel-control-prev"
                type="button"
                data-bs-target="#scholarshipBanner"
                data-bs-slide="prev"
                aria-label="Ảnh trước">
                <span class="carousel-control-prev-icon"
                    aria-hidden="true"></span>
            </button>

            <button class="carousel-control-next"
                type="button"
                data-bs-target="#scholarshipBanner"
                data-bs-slide="next"
                aria-label="Ảnh tiếp theo">
                <span class="carousel-control-next-icon"
                    aria-hidden="true"></span>
            </button>
        </div>
    </section>


    <section class="py-5">
        <div class="container">
            <!-- Giao diện tìm kiếm ngữ nghĩa, sẽ tích hợp AI sau -->
            <!-- Tìm kiếm học bổng -->
            <form action="/scholarships" method="GET"
                class="bg-white border rounded-4 p-4 mb-4">

                <h2 class="h5 fw-bold mb-2">Tìm kiếm học bổng</h2>

                <p class="small mb-3" style="color:var(--text-muted);">
                    Tìm theo tên học bổng hoặc mô tả nhu cầu của bạn
                    để khám phá những cơ hội phù hợp.
                </p>
                <div class="d-flex gap-2">
                    <input type="text"
                        name="keyword"
                        class="form-control flex-grow-1"
                        style="min-width:0;"
                        placeholder="Ví dụ: Học bổng CNTT hoặc học bổng cho sinh viên có hoàn cảnh khó khăn..." value="<?= htmlspecialchars($keyword ?? '', ENT_QUOTES, 'UTF-8') ?>">

                    <button type="submit"
                        class="btn text-white flex-shrink-0 px-4"
                        style="background:var(--primary);">
                        <i class="bi bi-search me-1"></i>
                        Tìm kiếm
                    </button>
                </div>
            </form>

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