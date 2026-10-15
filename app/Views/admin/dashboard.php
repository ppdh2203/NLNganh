<?php

/** @var array $stats */
/** @var array $accounts */
?>
<section data-admin-panel="overview">
    <div class="mb-4">
        <h2 class="h4 fw-bold">Tổng quan</h2>
        <p class="text-muted">Theo dõi hoạt động của hệ thống học bổng.</p>
    </div>
    <div class="row g-3">
        <?php
        $cards = [
            ['Sinh viên', 'bi-people', 'students'],
            ['Nhà cung cấp', 'bi-building', 'providers'],
            ['Học bổng', 'bi-mortarboard', 'scholarships'],
            ['Chờ duyệt', 'bi-hourglass-split', 'pending']
        ];
        ?>
        <?php foreach ($cards as $card): ?>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="admin-stat-card">
                    <div class="admin-stat-icon"><i class="bi <?= $card[1] ?>"></i></div>
                    <p class="text-muted mb-1"><?= $card[0] ?></p>
                    <h3 class="h3 fw-bold mb-0">
                        <?= number_format($stats[$card[2]] ?? 0) ?>
                    </h3>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="admin-section-card mt-4">
        <h3 class="h6 fw-bold">Hoạt động hệ thống</h3>
        <p class="text-muted mb-0">Biểu đồ và thống kê chi tiết sẽ được kết nối với cơ sở dữ liệu ở bước tiếp theo.</p>
    </div>
</section>

<section data-admin-panel="users" hidden>
    <div class="mb-4">
        <h2 class="h4 fw-bold">Quản lý tài khoản</h2>
        <p class="text-muted">Theo dõi tài khoản sinh viên và nhà cung cấp trong hệ thống.</p>
    </div>

    <div class="admin-section-card">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="h6 fw-bold mb-0">Danh sách tài khoản</h3>
            <span class="text-muted small">Tổng: <?= count($accounts) ?> tài khoản</span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Họ tên / Nhà cung cấp</th>
                        <th>Email</th>
                        <th>Vai trò</th>
                        <th>Trạng thái</th>
                        <th>Ngày tạo</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($accounts as $account): ?>
                        <?php
                        $role = $account['role'];
                        $status = $account['status'];
                        $statusClass = match ($status) {
                            'Active' => 'success',
                            'Pending' => 'warning',
                            'Locked' => 'danger',
                            default => 'secondary'
                        };
                        ?>
                        <tr>
                            <td class="fw-semibold"><?= htmlspecialchars($account['display_name'] ?: 'Chưa cập nhật', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($account['email'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= $role === 'Student' ? 'Sinh viên' : 'Nhà cung cấp' ?></td>
                            <td><span class="badge text-bg-<?= $statusClass ?>"><?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?></span></td>
                            <td><?= htmlspecialchars(date('d/m/Y', strtotime($account['created_at'])), ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="text-end">
                                <?php if (in_array($status, ['Active', 'Locked'], true)): ?>
                                    <form method="POST" action="/admin/accounts/toggle" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="user_id" value="<?= (int) $account['user_id'] ?>">
                                        <button type="submit"
                                            class="btn btn-sm <?= $status === 'Active' ? 'btn-outline-danger' : 'btn-outline-success' ?>"
                                            onclick="return confirm('Bạn có chắc muốn <?= $status === 'Active' ? 'khóa' : 'mở khóa' ?> tài khoản này?')">
                                            <?= $status === 'Active' ? 'Khóa' : 'Mở khóa' ?>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-muted small">Chờ duyệt</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (empty($accounts)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Chưa có tài khoản nào.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php
$panels = [
    'users' => ['Quản lý tài khoản', 'Danh sách sinh viên, nhà cung cấp và chức năng khóa/mở khóa.'],
    'providers' => ['Duyệt nhà cung cấp', 'Xem hồ sơ xác minh và phê duyệt tài khoản đang chờ.'],
    'reviews' => ['Duyệt học bổng', 'Kiểm tra học bổng được nhà cung cấp gửi lên.'],
    'scholarships' => ['Quản lý học bổng', 'Theo dõi và quản lý trạng thái các học bổng.']
];
?>
<?php foreach ($panels as $key => $panel): ?>
    <section data-admin-panel="<?= $key ?>" hidden>
        <h2 class="h4 fw-bold mb-3"><?= $panel[0] ?></h2>
        <div class="admin-section-card">
            <p class="text-muted mb-0"><?= $panel[1] ?></p>
        </div>
    </section>
<?php endforeach; ?>