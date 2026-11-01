<?php

declare(strict_types=1);

/** @var array $provider */
/** @var string|null $success */

$e = static fn($value): string =>
htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');

$logo = (string) ($provider['provider_logo'] ?? '');

?>

<section class="provider-dashboard">

    <div class="mb-4">
        <a href="/provider" class="text-decoration-none text-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Quay lại tổng quan
        </a>

        <h2 class="h4 fw-bold mt-3 mb-2">
            Thông tin nhà cung cấp
        </h2>

        <p class="text-muted mb-0">
            Xem và cập nhật thông tin liên hệ của tổ chức cấp học bổng.
        </p>
    </div>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?= $e($success) ?>

            <button type="button"
                class="btn-close"
                data-bs-dismiss="alert"
                aria-label="Đóng"></button>
        </div>
    <?php endif; ?>

    <form action="/provider/profile"
        method="POST"
        enctype="multipart/form-data">

        <input type="hidden"
            name="csrf_token"
            value="<?= $e($_SESSION['csrf_token'] ?? '') ?>">

        <!-- Thông tin tổ chức -->
        <div class="provider-card mb-4">

            <h3 class="h6 fw-bold mb-4">
                <i class="bi bi-building me-2"></i>
                Thông tin tổ chức
            </h3>

            <div class="row g-4">

                <!-- Logo -->
                <div class="col-lg-3 text-center">

                    <div class="border rounded-3 p-3 bg-light mb-3"
                        style="min-height:180px; display:flex; align-items:center; justify-content:center;">

                        <img
                            id="logoPreview"
                            src="<?= $e($logo) ?>"
                            alt="Logo nhà cung cấp"
                            style="max-width:100%; max-height:150px; object-fit:contain; <?= $logo === '' ? 'display:none;' : '' ?>">

                        <div id="logoPlaceholder"
                            class="text-muted <?= $logo !== '' ? 'd-none' : '' ?>">
                            <i class="bi bi-building fs-1 d-block mb-2"></i>
                            Chưa có logo
                        </div>

                    </div>

                    <label for="providerLogo"
                        class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-image me-1"></i>
                        Thay đổi logo
                    </label>

                    <input type="file"
                        id="providerLogo"
                        name="provider_logo"
                        accept=".jpg,.jpeg,.png,.webp"
                        class="d-none">

                    <div class="text-muted small mt-2">
                        JPG, PNG hoặc WEBP. Tối đa 2 MB.
                    </div>
                </div>

                <!-- Thông tin không chỉnh sửa -->
                <div class="col-lg-9">

                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label">
                                Tên nhà cung cấp
                            </label>
                            <input type="text"
                                class="form-control"
                                value="<?= $e($provider['provider_name'] ?? '') ?>"
                                readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Tên tổ chức
                            </label>
                            <input type="text"
                                class="form-control"
                                value="<?= $e($provider['organization'] ?? '') ?>"
                                readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Email đăng nhập
                            </label>
                            <input type="email"
                                class="form-control"
                                value="<?= $e($provider['email'] ?? '') ?>"
                                readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Trạng thái tài khoản
                            </label>
                            <div class="form-control bg-light">
                                <?= ($provider['status'] ?? '') === 'Active'
                                    ? 'Đang hoạt động'
                                    : $e($provider['status'] ?? 'Không xác định') ?>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>

        <!-- Thông tin liên hệ -->
        <div class="provider-card mb-4">

            <h3 class="h6 fw-bold mb-4">
                <i class="bi bi-geo-alt me-2"></i>
                Thông tin liên hệ
            </h3>

            <div class="row g-3">

                <div class="col-12">
                    <label class="form-label">
                        Địa chỉ nhà cung cấp
                        <span class="text-danger">*</span>
                    </label>

                    <input type="text"
                        name="provider_address"
                        class="form-control"
                        maxlength="255"
                        value="<?= $e($provider['provider_address'] ?? '') ?>"
                        required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">
                        Email liên hệ
                    </label>

                    <input type="email"
                        name="contact_email"
                        class="form-control"
                        maxlength="255"
                        value="<?= $e($provider['contact_email'] ?? '') ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">
                        Số điện thoại
                    </label>

                    <input type="tel"
                        name="provider_phone"
                        class="form-control"
                        maxlength="20"
                        value="<?= $e($provider['provider_phone'] ?? '') ?>">
                </div>

            </div>
        </div>

        <!-- Nút thao tác -->
        <div class="d-flex justify-content-end gap-2 mb-4">

            <a href="/provider"
                class="btn btn-outline-secondary">
                Hủy
            </a>

            <button type="submit"
                id="saveProfileBtn"
                class="btn btn-provider"
                disabled>
                <i class="bi bi-check-lg me-1"></i>
                Lưu thay đổi
            </button>

        </div>

    </form>

</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const input = document.getElementById('providerLogo');
        const preview = document.getElementById('logoPreview');
        const placeholder = document.getElementById('logoPlaceholder');

        let previewUrl = null;

        input.addEventListener('change', function() {
            const file = this.files[0];

            if (!file) return;

            const allowedTypes = [
                'image/jpeg',
                'image/png',
                'image/webp'
            ];

            if (!allowedTypes.includes(file.type)) {
                alert('Chỉ hỗ trợ ảnh JPG, PNG hoặc WEBP.');
                this.value = '';
                return;
            }

            if (file.size > 2 * 1024 * 1024) {
                alert('Logo không được vượt quá 2 MB.');
                this.value = '';
                return;
            }

            if (previewUrl !== null) {
                URL.revokeObjectURL(previewUrl);
            }

            previewUrl = URL.createObjectURL(file);
            preview.src = previewUrl;
            preview.style.display = 'block';
            placeholder.classList.add('d-none');
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form[action="/provider/profile"]');
        const saveButton = document.getElementById('saveProfileBtn');

        if (!form || !saveButton) return;

        const fields = [
            form.elements['provider_address'],
            form.elements['contact_email'],
            form.elements['provider_phone']
        ];

        const logoInput = document.getElementById('providerLogo');

        // Lưu dữ liệu ban đầu để so sánh
        const originalValues = fields.map(field => field.value);

        function checkChanges() {
            const textChanged = fields.some(
                (field, index) => field.value !== originalValues[index]
            );

            const logoChanged = logoInput.files.length > 0;

            saveButton.disabled = !(textChanged || logoChanged);
        }

        fields.forEach(field => {
            field.addEventListener('input', checkChanges);
        });

        logoInput.addEventListener('change', checkChanges);

        checkChanges();
    });
</script>