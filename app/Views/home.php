<main>
    <section>
        <div id="carouselScholarship"
            class="carousel slide"
            data-bs-ride="carousel"
            data-bs-interval="4500">

            <div class="carousel-indicators">
                <button type="button" data-bs-target="#carouselScholarship" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                <button type="button" data-bs-target="#carouselScholarship" data-bs-slide-to="1" aria-label="Slide 2"></button>
            </div>
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img src="/assets/images/banner1.jpeg"
                        alt="Học bổng"
                        class="d-block w-100 object-fit-cover">

                    <div class="carousel-caption align-items-center h-100 top-0 start-0 end-0 bottom-0 position-absolute d-flex flex-column justify-content-center"
                        style="background:linear-gradient(to bottom, rgba(0,0,0,0.45), rgba(60,51,148,0.75));">

                        <div class="home-banner-content">
                            <h1>Find the scholarship for your future</h1>

                            <p>
                                Discover opportunities that support your
                                academic journey and help you achieve your goals.
                            </p>

                            <a href="/scholarships" class="home-banner-btn">
                                Explore Scholarships
                                <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="carousel-item">
                    <img src="/assets/images/banner2.jpg"
                        alt="Học bổng"
                        class="d-block w-100 object-fit-cover">

                    <div class="carousel-caption align-items-center h-100 top-0 start-0 end-0 bottom-0 position-absolute d-flex flex-column justify-content-center"
                        style="background:linear-gradient(to bottom, rgba(0,0,0,0.45), rgba(60,51,148,0.75));">

                        <div class="home-banner-content">
                            <h1>Empower the next generation</h1>

                            <p>
                                Create scholarship opportunities and connect
                                with promising students.
                            </p>

                            <a href="/register" class="home-banner-btn">
                                Become a Provider
                                <i class="bi bi-arrow-right"></i>
                            </a>

                        </div>
                    </div>
                </div>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#carouselScholarship" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Trước</span>
            </button>

            <button class="carousel-control-next" type="button" data-bs-target="#carouselScholarship" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Sau</span>
            </button>

        </div>
    </section>
<!-- Chọn loại tài khoản -->
    <section class="home-role-section">
        <div class="container">
            <div class="home-role-header">
                <h2>Start your journey with us</h2>
                <p>Choose how you would like to continue.</p>
            </div>

            <div class="home-role-options">
                <!-- Student -->
                <a href="/register?role=student" class="home-role-card">
                    <div>
                        <i class="bi bi-mortarboard home-role-icon"></i>
                        <h3>Continue as a Student</h3>
                        <p>Discover scholarship opportunities and submit your applications.</p>
                    </div>

                    <span class="home-role-arrow">
                        <i class="bi bi-arrow-right"></i>
                    </span>
                </a>

                <!-- Provider -->
                <a href="/register?role=provider" class="home-role-card">
                    <div>
                        <i class="bi bi-building home-role-icon"></i>
                        <h3>Continue as a Scholarship Provider</h3>
                        <p>Create scholarship opportunities and connect with students.</p>
                    </div>

                    <span class="home-role-arrow">
                        <i class="bi bi-arrow-right"></i>
                    </span>
                </a>
            </div>
        </div>
    </section>

    <!-- Học bổng nổi bật -->
    <section class="featured-scholarships">
        <div class="container">
            <div class="featured-header">
                <div>
                    <h2>Featured Scholarships</h2>
                    <p>Explore scholarship opportunities selected for students.</p>
                </div>

                <a href="/scholarships" class="featured-view-all">
                    View All Scholarships
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>
            
            <div class="featured-grid">
                <?php if (!empty($scholarships)): ?>
                    <?php foreach ($scholarships as $scholarship): ?>
                        <article class="scholarship-card">
                            <div class="scholarship-card-icon">
                                <i class="bi bi-mortarboard"></i>
                            </div>
                            <h3><?= htmlspecialchars($scholarship['scholarship_name'], ENT_QUOTES, 'UTF-8') ?></h3>
                            <p class="scholarship-provider"><?= htmlspecialchars($scholarship['provider_name'], ENT_QUOTES, 'UTF-8') ?></p>
                            <div class="scholarship-info">
                                <span>
                                    <i class="bi bi-cash-coin"></i>
                                    <?= number_format((float)$scholarship['amount'], 0, ',', '.') ?> VND
                                </span>
                                <span>
                                    <i class="bi bi-calendar-event"></i>
                                    <?= date('d/m/Y', strtotime($scholarship['end_date'])) ?>
                                </span>
                            </div>
                            <a href="/scholarships/<?= (int)$scholarship['scholarship_id'] ?>" class="scholarship-detail">
                                View Details <i class="bi bi-arrow-right"></i>
                            </a>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted">Hiện chưa có học bổng nào đang mở nhận hồ sơ.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <!-- How It Works -->
    <section class="how-it-works">
        <div class="container">
            <div class="how-header">
                <h2>How It Works</h2>
                <p>Start your scholarship journey in just a few simple steps.</p>
            </div>

            <!-- Nội dung được thay đổi theo Student/Provider trên Header -->
            <div class="how-steps" id="how-steps"></div>
        </div>
    </section>
</main>