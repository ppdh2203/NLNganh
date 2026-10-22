<section>
    <div class="mb-4">
        <h2 class="h4 fw-bold">Tổng quan</h2>
        <p class="text-muted">
            Theo dõi và quản lý các học bổng do nhà cung cấp đăng tải.
        </p>
    </div>

    <?php
    $cards = [
        ['Tổng học bổng', 'bi-mortarboard', 'total'],
        ['Chờ duyệt', 'bi-hourglass-split', 'pending'],
        ['Đã phê duyệt', 'bi-check-circle', 'published'],
        ['Đã từ chối', 'bi-x-circle', 'rejected']
    ];
    ?>

    <div class="row g-3">
        <?php foreach ($cards as $card): ?>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="provider-card">
                    <div class="mb-3" style="color: #6052db;">
                        <i class="bi <?= $card[1] ?> fs-3"></i>
                    </div>

                    <p class="text-muted mb-1">
                        <?= $card[0] ?>
                    </p>

                    <h3 class="fw-bold mb-0">
                        <?= number_format((int) ($stats[$card[2]] ?? 0)) ?>
                    </h3>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="provider-card mt-4">
        <h3 class="h6 fw-bold">Quản lý học bổng</h3>

        <p class="text-muted">
            Đăng tải và theo dõi trạng thái xét duyệt các học bổng của bạn.
        </p>

        <a href="/provider/scholarships/create"
           class="btn text-white"
           style="background: #6052db;">
            <i class="bi bi-plus-circle me-1"></i>
            Đăng học bổng mới
        </a>
    </div>
</section>