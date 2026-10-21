<?php

/** @var array $stats */
/** @var array $accounts */
/** @var array $pendingProviders */
/** @var array $approvedProviders */
/** @var array $rejectedProviders */

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

<!-- DUYỆT NHÀ CUNG CẤP -->
<section data-admin-panel="providers" hidden>
    <div class="mb-4">
        <h2 class="h4 fw-bold">Duyệt nhà cung cấp</h2>
        <p class="text-muted">
            Theo dõi hồ sơ đăng ký và kết quả xét duyệt nhà cung cấp.
        </p>
    </div>

    <?php
    $providerGroups = [
        'pending' => [
            'title' => 'Chờ duyệt',
            'status' => 'Pending',
            'badge' => 'warning',
            'data' => $pendingProviders
        ],
        'approved' => [
            'title' => 'Đã phê duyệt',
            'status' => 'Active',
            'badge' => 'success',
            'data' => $approvedProviders
        ],
        'rejected' => [
            'title' => 'Đã từ chối',
            'status' => 'Rejected',
            'badge' => 'danger',
            'data' => $rejectedProviders
        ]
    ];
    ?>

    <!-- Các tab phân loại -->
    <ul class="nav nav-pills gap-2 mb-3" role="tablist">
        <?php foreach ($providerGroups as $key => $group): ?>
            <li class="nav-item" role="presentation">
                <button
                    type="button"
                    class="nav-link <?= $key === 'pending' ? 'active' : '' ?>"
                    data-bs-toggle="pill"
                    data-bs-target="#provider-group-<?= $key ?>"
                    role="tab"
                    aria-controls="provider-group-<?= $key ?>"
                    aria-selected="<?= $key === 'pending' ? 'true' : 'false' ?>">
                    <?= $group['title'] ?>
                    <span class="badge rounded-pill text-bg-light ms-1">
                        <?= count($group['data']) ?>
                    </span>
                </button>
            </li>
        <?php endforeach; ?>
    </ul>

    <!-- Nội dung từng tab -->
    <div class="tab-content">
        <?php foreach ($providerGroups as $key => $group): ?>
            <div
                class="tab-pane fade <?= $key === 'pending' ? 'show active' : '' ?>"
                id="provider-group-<?= $key ?>"
                role="tabpanel">

                <div class="admin-section-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 class="h6 fw-bold mb-0">
                            Danh sách <?= mb_strtolower($group['title'], 'UTF-8') ?>
                        </h3>
                        <span class="text-muted small">
                            Tổng: <?= count($group['data']) ?> hồ sơ
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
                                <?php foreach ($group['data'] as $provider): ?>
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
                                            <span class="badge text-bg-<?= $group['badge'] ?>">
                                                <?= $group['title'] ?>
                                            </span>
                                        </td>

                                        <td class="text-center">
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-primary admin-provider-btn"
                                                data-bs-toggle="modal"
                                                data-bs-target="#providerModal<?= (int) $provider['user_id'] ?>">
                                                <i class="bi bi-eye me-1"></i>
                                                Xem hồ sơ
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>

                                <?php if (empty($group['data'])): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            Không có hồ sơ trong nhóm này.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>


<!-- MODAL CHI TIẾT HỒ SƠ -->
<?php
$modalProviders = array_merge(
    $pendingProviders,
    $approvedProviders,
    $rejectedProviders
);
?>

<?php foreach ($modalProviders as $provider): ?>
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
                                    class="btn btn-sm btn-outline-primary admin-provider-btn">
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

                    <?php
                    $statusLabel = match ($provider['status']) {
                        'Pending' => 'Chờ duyệt',
                        'Active' => 'Đã phê duyệt',
                        'Rejected' => 'Đã từ chối',
                        default => $provider['status']
                    };
                    ?>

                    <span class="text-muted small">
                        Trạng thái: <?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8') ?>
                    </span>
                    <?php if ($provider['status'] === 'Pending'): ?>

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
                    <?php else: ?>

                        <button
                            type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">
                            Đóng
                        </button>

                    <?php endif; ?>
                </div>


            </div>
        </div>
    </div>
<?php endforeach; ?>

<?php
// Các nhóm học bổng trong trang xét duyệt
$scholarshipGroups = [
    'pending' => [
        'title' => 'Chờ duyệt',
        'badge' => 'warning',
        'data' => $pendingScholarships ?? []
    ],
    'published' => [
        'title' => 'Đã phê duyệt',
        'badge' => 'success',
        'data' => $publishedScholarships ?? []
    ],
    'rejected' => [
        'title' => 'Đã từ chối',
        'badge' => 'danger',
        'data' => $rejectedScholarships ?? []
    ]
];

$modalScholarships = array_merge(
    $pendingScholarships ?? [],
    $publishedScholarships ?? [],
    $rejectedScholarships ?? []
);
?>

<!-- DUYỆT HỌC BỔNG -->
<section data-admin-panel="reviews" hidden>
    <div class="mb-4">
        <h2 class="h4 fw-bold">Duyệt học bổng</h2>
        <p class="text-muted">
            Kiểm tra thông tin và kết quả xét duyệt học bổng.
        </p>
    </div>

    <!-- Tab trạng thái -->
    <ul class="nav nav-pills gap-2 mb-3 admin-scholarship-tabs"
        role="tablist">

        <?php foreach ($scholarshipGroups as $key => $group): ?>
            <li class="nav-item" role="presentation">
                <button
                    type="button"
                    class="nav-link <?= $key === 'pending' ? 'active' : '' ?>"
                    data-bs-toggle="pill"
                    data-bs-target="#scholarship-group-<?= $key ?>"
                    role="tab"
                    aria-controls="scholarship-group-<?= $key ?>"
                    aria-selected="<?= $key === 'pending' ? 'true' : 'false' ?>">

                    <?= $group['title'] ?>

                    <span class="badge rounded-pill text-bg-light ms-1">
                        <?= count($group['data']) ?>
                    </span>
                </button>
            </li>
        <?php endforeach; ?>
    </ul>

    <!-- Danh sách học bổng -->
    <div class="tab-content">
        <?php foreach ($scholarshipGroups as $key => $group): ?>
            <div
                class="tab-pane fade <?= $key === 'pending' ? 'show active' : '' ?>"
                id="scholarship-group-<?= $key ?>"
                role="tabpanel">

                <div class="admin-section-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 class="h6 fw-bold mb-0">
                            Danh sách <?= htmlspecialchars(
                                            mb_strtolower($group['title'], 'UTF-8'),
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                        </h3>

                        <span class="text-muted small">
                            Tổng: <?= count($group['data']) ?> học bổng
                        </span>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Tên học bổng</th>
                                    <th>Nhà cung cấp</th>
                                    <th>Số tiền</th>
                                    <th>Hạn nộp</th>
                                    <th>Trạng thái</th>
                                    <th class="text-center">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($group['data'] as $scholarship): ?>
                                    <tr>
                                        <td class="fw-semibold">
                                            <?= htmlspecialchars(
                                                $scholarship['scholarship_name'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars(
                                                $scholarship['organization']
                                                    ?: $scholarship['provider_name'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>
                                        </td>

                                        <td>
                                            <?= number_format(
                                                (float) $scholarship['amount'],
                                                0,
                                                ',',
                                                '.'
                                            ) ?> VNĐ
                                        </td>

                                        <td>
                                            <?= date(
                                                'd/m/Y',
                                                strtotime($scholarship['end_date'])
                                            ) ?>
                                        </td>

                                        <td>
                                            <span class="badge text-bg-<?= $group['badge'] ?>">
                                                <?= $group['title'] ?>
                                            </span>
                                        </td>

                                        <td class="text-center">
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-primary admin-provider-btn"
                                                data-bs-toggle="modal"
                                                data-bs-target="#scholarshipModal<?= (int) $scholarship['scholarship_id'] ?>">

                                                <i class="bi bi-eye me-1"></i>
                                                Xem chi tiết
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>

                                <?php if (empty($group['data'])): ?>
                                    <tr>
                                        <td colspan="6"
                                            class="text-center text-muted py-4">
                                            Không có học bổng trong nhóm này.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- MODAL CHI TIẾT HỌC BỔNG -->
<?php foreach ($modalScholarships as $scholarship): ?>
    <?php
    $scholarshipId = (int) $scholarship['scholarship_id'];
    $modalId = 'scholarshipModal' . $scholarshipId;

    $scholarshipStatus = match ($scholarship['status']) {
        'Pending' => 'Chờ duyệt',
        'Published' => 'Đã phê duyệt',
        'Rejected' => 'Đã từ chối',
        default => $scholarship['status']
    };

    $requirements = \App\Models\Scholarship::getRequirements($scholarshipId);
    $documents = \App\Models\Scholarship::getRequiredDocuments($scholarshipId);
    ?>

    <div class="modal fade"
        id="<?= $modalId ?>"
        tabindex="-1"
        aria-labelledby="<?= $modalId ?>Label"
        aria-hidden="true">

        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">

                <div class="modal-header admin-provider-header">
                    <div class="admin-provider-heading">
                        <div class="admin-provider-logo-placeholder">
                            <i class="bi bi-mortarboard"></i>
                        </div>

                        <h5 class="modal-title admin-provider-title"
                            id="<?= $modalId ?>Label">
                            <?= htmlspecialchars(
                                $scholarship['scholarship_name'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </h5>
                    </div>

                    <button
                        type="button"
                        class="btn-close admin-provider-close"
                        data-bs-dismiss="modal"
                        aria-label="Đóng">
                    </button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <?php
                        $fields = [
                            'Nhà cung cấp' =>
                            $scholarship['provider_name'],
                            'Tổ chức' =>
                            $scholarship['organization'],
                            'Số tiền học bổng' =>
                            number_format(
                                (float) $scholarship['amount'],
                                0,
                                ',',
                                '.'
                            ) . ' VNĐ',
                            'Ngày bắt đầu' =>
                            date('d/m/Y', strtotime($scholarship['start_date'])),
                            'Hạn nộp' =>
                            date('d/m/Y', strtotime($scholarship['end_date'])),
                            'Ngày tạo' =>
                            date('d/m/Y', strtotime($scholarship['created_at']))
                        ];
                        ?>

                        <?php foreach ($fields as $label => $value): ?>
                            <div class="col-md-6">
                                <div class="text-muted small mb-1">
                                    <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                                </div>
                                <div class="fw-semibold">
                                    <?= htmlspecialchars(
                                        (string) ($value ?: 'Chưa cập nhật'),
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <hr>

                    <h6 class="fw-bold">Mô tả học bổng</h6>
                    <p class="lh-lg" style="white-space: pre-line;"><?= htmlspecialchars(
                                                                        $scholarship['description'] ?? '',
                                                                        ENT_QUOTES,
                                                                        'UTF-8'
                                                                    ) ?></p>

                    <hr>

                    <h6 class="fw-bold mb-3">Điều kiện ứng tuyển</h6>

                    <?php if ($requirements !== false): ?>
                        <div class="row g-3">
                            <?php
                            $requirementFields = [
                                'GPA tối thiểu' => $requirements['required_gpa'],
                                'Năm học yêu cầu' => $requirements['required_year'],
                                'Ngành học' => $requirements['required_major']
                            ];
                            ?>

                            <?php foreach ($requirementFields as $label => $value): ?>
                                <div class="col-md-6">
                                    <div class="text-muted small mb-1">
                                        <?= $label ?>
                                    </div>
                                    <div class="fw-semibold">
                                        <?= htmlspecialchars(
                                            (string) ($value !== null && $value !== ''
                                                ? $value
                                                : 'Không yêu cầu'),
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">
                            Chưa có thông tin điều kiện ứng tuyển.
                        </p>
                    <?php endif; ?>

                    <hr>

                    <h6 class="fw-bold mb-3">Giấy tờ cần nộp</h6>

                    <?php if (!empty($documents)): ?>
                        <div class="d-flex flex-column gap-2">
                            <?php foreach ($documents as $document): ?>
                                <div class="border rounded-3 p-3">
                                    <div class="d-flex justify-content-between gap-2">
                                        <span class="fw-semibold">
                                            <?= htmlspecialchars(
                                                $document['document_name'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>
                                        </span>

                                        <span class="badge <?= (int) $document['is_required'] === 1
                                                                ? 'text-bg-danger'
                                                                : 'text-bg-secondary' ?>">
                                            <?= (int) $document['is_required'] === 1
                                                ? 'Bắt buộc'
                                                : 'Không bắt buộc' ?>
                                        </span>
                                    </div>

                                    <?php if (!empty($document['description'])): ?>
                                        <p class="text-muted small mt-2 mb-0">
                                            <?= htmlspecialchars(
                                                $document['description'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">
                            Chưa có danh sách giấy tờ yêu cầu.
                        </p>
                    <?php endif; ?>
                </div>

                <div class="modal-footer d-flex justify-content-between align-items-center">

                    <span class="text-muted small">
                        Trạng thái:
                        <?= htmlspecialchars($scholarshipStatus, ENT_QUOTES, 'UTF-8') ?>
                    </span>

                    <?php if ($scholarship['status'] === 'Pending'): ?>

                        <div class="d-flex gap-2">

                            <form method="POST"
                                action="/admin/scholarships/review"
                                onsubmit="return confirm('Bạn có chắc muốn từ chối học bổng này?')">

                                <input type="hidden"
                                    name="csrf_token"
                                    value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

                                <input type="hidden"
                                    name="scholarship_id"
                                    value="<?= (int) $scholarship['scholarship_id'] ?>">

                                <input type="hidden" name="action" value="reject">

                                <button type="submit" class="btn btn-outline-danger">
                                    <i class="bi bi-x-circle me-1"></i>
                                    Từ chối
                                </button>
                            </form>

                            <form method="POST"
                                action="/admin/scholarships/review"
                                onsubmit="return confirm('Bạn có chắc muốn phê duyệt học bổng này?')">

                                <input type="hidden"
                                    name="csrf_token"
                                    value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

                                <input type="hidden"
                                    name="scholarship_id"
                                    value="<?= (int) $scholarship['scholarship_id'] ?>">

                                <input type="hidden" name="action" value="approve">

                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check-circle me-1"></i>
                                    Phê duyệt
                                </button>
                            </form>

                        </div>

                    <?php else: ?>

                        <button type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">
                            Đóng
                        </button>

                    <?php endif; ?>

                </div>

            </div>
        </div>
    </div>
<?php endforeach; ?>

<!-- QUẢN LÝ HỌC BỔNG -->
<section data-admin-panel="scholarships" hidden>
    <h2 class="h4 fw-bold mb-3">Quản lý học bổng</h2>
    <div class="admin-section-card">
        <p class="text-muted mb-0">
            Theo dõi và quản lý trạng thái các học bổng.
        </p>
    </div>
</section>