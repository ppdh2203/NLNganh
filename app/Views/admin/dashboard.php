<?php

/** @var array $stats */
/** @var array $accounts */
/** @var array $pendingProviders */
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
                        <th class="text-center" style="width: 130px;">Thao tác</th>
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
                            <td class="text-center">
                                <?php if (in_array($status, ['Active', 'Locked'], true)): ?>
                                    <form method="POST" action="/admin/accounts/toggle" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="user_id" value="<?= (int) $account['user_id'] ?>">
                                        <button type="submit"
                                            class="btn btn-sm admin-account-btn <?= $status === 'Active' ? 'btn-outline-danger' : 'btn-outline-success' ?>" onclick="return confirm('Bạn có chắc muốn <?= $status === 'Active' ? 'khóa' : 'mở khóa' ?> tài khoản này?')">
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


<!-- DUYỆT NHÀ CUNG CẤP -->
<section data-admin-panel="providers" hidden>
    <div class="mb-4">
        <h2 class="h4 fw-bold">Duyệt nhà cung cấp</h2>
        <p class="text-muted">
            Kiểm tra thông tin và hồ sơ xác minh của nhà cung cấp trước khi kích hoạt tài khoản.
        </p>
    </div>

    <div class="admin-section-card">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="h6 fw-bold mb-0">Danh sách chờ duyệt</h3>
            <span class="text-muted small">
                Tổng: <?= count($pendingProviders) ?> hồ sơ
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nhà cung cấp</th>
                        <th>Email đăng nhập</th>
                        <th>Tổ chức</th>
                        <th>Ngày đăng ký</th>
                        <th>Trạng thái</th>
                        <th class="text-center">Thao tác</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($pendingProviders as $provider): ?>
                        <tr>
                            <td class="fw-semibold">
                                <?= htmlspecialchars($provider['provider_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($provider['email'], ENT_QUOTES, 'UTF-8') ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($provider['organization'] ?: 'Chưa cập nhật', ENT_QUOTES, 'UTF-8') ?>
                            </td>

                            <td>
                                <?= date('d/m/Y', strtotime($provider['created_at'])) ?>
                            </td>

                            <td>
                                <span class="badge text-bg-warning">Pending</span>
                            </td>

                            <td class="text-center">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-primary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#providerModal<?= (int) $provider['user_id'] ?>">
                                    <i class="bi bi-eye me-1"></i>
                                    Xem hồ sơ
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (empty($pendingProviders)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                Hiện không có nhà cung cấp nào đang chờ duyệt.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- MODAL CHI TIẾT HỒ SƠ -->
<?php foreach ($pendingProviders as $provider): ?>
    <?php $modalId = 'providerModal' . (int) $provider['user_id']; ?>

    <div class="modal fade" id="<?= $modalId ?>" tabindex="-1"
        aria-labelledby="<?= $modalId ?>Label" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">

                <div class="modal-header admin-provider-header">
                    <div class="admin-provider-heading">
                        <?php if (!empty($provider['provider_logo'])): ?>
                            <img
                                src="<?= htmlspecialchars($provider['provider_logo'], ENT_QUOTES, 'UTF-8') ?>"
                                alt="Logo nhà cung cấp"
                                class="admin-provider-logo">
                        <?php else: ?>
                            <div class="admin-provider-logo-placeholder">
                                <i class="bi bi-building"></i>
                            </div>
                        <?php endif; ?>

                        <h5 class="modal-title admin-provider-title"
                            id="<?= $modalId ?>Label">
                            <?= htmlspecialchars(
                                $provider['organization'] ?: $provider['provider_name'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </h5>
                    </div>

                    <button type="button"
                        class="btn-close admin-provider-close"
                        data-bs-dismiss="modal"
                        aria-label="Đóng"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <?php
                        $fields = [
                            'Tên nhà cung cấp' => $provider['provider_name'],
                            'Tên tổ chức' => $provider['organization'],
                            'Email đăng nhập' => $provider['email'],
                            'Email liên hệ' => $provider['contact_email'],
                            'Số điện thoại' => $provider['provider_phone'],
                            'Địa chỉ' => $provider['provider_address']
                        ];
                        ?>

                        <?php foreach ($fields as $label => $value): ?>
                            <div class="col-md-6">
                                <div class="text-muted small mb-1">
                                    <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                                </div>
                                <div class="fw-semibold">
                                    <?= htmlspecialchars($value ?: 'Chưa cập nhật', ENT_QUOTES, 'UTF-8') ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <hr>

                    <h6 class="fw-bold mb-2">Giấy tờ xác minh</h6>

                    <?php if (!empty($provider['verification_document'])): ?>
                        <?php
                        $documentUrl = '/admin/providers/'
                            . (int) $provider['user_id']
                            . '/document';
                        ?>

                        <div class="border rounded-3 p-3">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                                <span>
                                    <i class="bi bi-file-earmark-check text-primary me-2"></i>
                                    Đã nộp giấy tờ xác minh
                                </span>

                                <a href="<?= htmlspecialchars($documentUrl, ENT_QUOTES, 'UTF-8') ?>"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-box-arrow-up-right me-1"></i>
                                    Mở trong tab mới
                                </a>
                            </div>

                            <?php
                            $extension = strtolower(pathinfo(
                                $provider['verification_document'],
                                PATHINFO_EXTENSION
                            ));
                            ?>

                            <?php if (in_array($extension, ['jpg', 'jpeg', 'png'], true)): ?>
                                <div class="admin-document-image">
                                    <img
                                        src="<?= htmlspecialchars($documentUrl, ENT_QUOTES, 'UTF-8') ?>"
                                        alt="Giấy tờ xác minh của nhà cung cấp"
                                        class="img-fluid">
                                </div>
                            <?php else: ?>
                                <iframe
                                    src="<?= htmlspecialchars($documentUrl, ENT_QUOTES, 'UTF-8') ?>"
                                    class="admin-document-preview"
                                    title="Tài liệu xác minh của nhà cung cấp"
                                    loading="lazy">
                                </iframe>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning mb-0">
                            Nhà cung cấp chưa có giấy tờ xác minh.
                        </div>
                    <?php endif; ?>

                </div>


                <div class="modal-footer d-flex justify-content-between align-items-center">

                    <span class="text-muted small">
                        Trạng thái: Chờ duyệt
                    </span>

                    <div class="d-flex gap-2">
                        <form method="POST"
                            action="/admin/providers/review"
                            onsubmit="return confirm('Bạn có chắc muốn từ chối nhà cung cấp này?')">

                            <input type="hidden"
                                name="csrf_token"
                                value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

                            <input type="hidden"
                                name="provider_id"
                                value="<?= (int) $provider['user_id'] ?>">

                            <input type="hidden"
                                name="action"
                                value="reject">

                            <button type="submit" class="btn btn-outline-danger">
                                <i class="bi bi-x-circle me-1"></i>
                                Từ chối
                            </button>
                        </form>

                        <form method="POST"
                            action="/admin/providers/review"
                            onsubmit="return confirm('Bạn có chắc muốn phê duyệt nhà cung cấp này?')">

                            <input type="hidden"
                                name="csrf_token"
                                value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

                            <input type="hidden"
                                name="provider_id"
                                value="<?= (int) $provider['user_id'] ?>">

                            <input type="hidden"
                                name="action"
                                value="approve">

                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle me-1"></i>
                                Phê duyệt
                            </button>
                        </form>
                    </div>
                </div>


            </div>
        </div>
    </div>
<?php endforeach; ?>


<?php
$panels = [
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