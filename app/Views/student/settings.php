<?php

declare(strict_types=1);

/** @var array $account */
/** @var string|null $error */

$e = static fn($value): string =>
    htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
?>

<main class="student-settings-page">

    <!-- Dải tiêu đề -->
    <section class="student-settings-hero">
        <div class="container">
            <a href="/profile"
               class="text-decoration-none d-inline-block">
                <i class="bi bi-arrow-left me-1"></i>
                Quay lại
            </a>

            <h1 class="h4 fw-bold mt-3 mb-0">
                CÀI ĐẶT TÀI KHOẢN
            </h1>
        </div>
    </section>

    <!-- Nội dung trang -->
    <section class="student-settings-content">
        <div class="container" style="max-width:960px;">

            <!-- Thông báo lỗi -->
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show"
                     role="alert">
                    <i class="bi bi-exclamation-circle-fill me-2"></i>
                    <?= $e($error) ?>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="alert"
                            aria-label="Đóng"></button>
                </div>
            <?php endif; ?>

            <!-- Thông tin tài khoản -->
            <div class="student-settings-card bg-white border rounded-4 p-4 mb-4">

                <h2 class="h5 fw-bold mb-4">
                    <i class="bi bi-person-circle me-2"></i>
                    Thông tin tài khoản
                </h2>

                <div class="row g-3">

                    <div class="col-md-8">
                        <label class="form-label">
                            Email đăng nhập
                        </label>

                        <input type="email"
                               class="form-control"
                               value="<?= $e($account['email'] ?? '') ?>"
                               readonly>

                        <div class="form-text">
                            Email đăng nhập hiện không hỗ trợ thay đổi.
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">
                            Trạng thái tài khoản
                        </label>

                        <div class="form-control bg-light">
                            <?= ($account['status'] ?? '') === 'Active'
                                ? 'Đang hoạt động'
                                : $e($account['status'] ?? 'Không xác định') ?>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Form đổi mật khẩu -->
            <form action="/settings"
                  method="POST"
                  id="passwordForm">

                <input type="hidden"
                       name="csrf_token"
                       value="<?= $e($_SESSION['csrf_token'] ?? '') ?>">

                <div class="student-settings-card bg-white border rounded-4 p-4 mb-4">

                    <h2 class="h5 fw-bold mb-2">
                        <i class="bi bi-shield-lock me-2"></i>
                        Đổi mật khẩu
                    </h2>

                    <p class="text-muted small mb-4">
                        Sử dụng mật khẩu mới khác mật khẩu hiện tại
                        để bảo vệ tài khoản.
                    </p>

                    <div class="row g-3">

                        <!-- Mật khẩu hiện tại -->
                        <div class="col-12">
                            <label for="currentPassword" class="form-label">
                                Mật khẩu hiện tại
                                <span class="text-danger">*</span>
                            </label>

                            <div class="input-group">
                                <input type="password"
                                       name="current_password"
                                       id="currentPassword"
                                       class="form-control"
                                       autocomplete="current-password"
                                       required>

                                <button type="button"
                                        class="btn btn-outline-secondary toggle-password"
                                        data-target="currentPassword"
                                        aria-label="Hiện mật khẩu">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Mật khẩu mới -->
                        <div class="col-md-6">
                            <label for="newPassword" class="form-label">
                                Mật khẩu mới
                                <span class="text-danger">*</span>
                            </label>

                            <div class="input-group">
                                <input type="password"
                                       name="new_password"
                                       id="newPassword"
                                       class="form-control"
                                       minlength="8"
                                       maxlength="72"
                                       autocomplete="new-password"
                                       required>

                                <button type="button"
                                        class="btn btn-outline-secondary toggle-password"
                                        data-target="newPassword"
                                        aria-label="Hiện mật khẩu">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>

                            <div class="form-text">
                                Tối thiểu 8 ký tự.
                            </div>
                        </div>

                        <!-- Xác nhận mật khẩu -->
                        <div class="col-md-6">
                            <label for="confirmPassword" class="form-label">
                                Xác nhận mật khẩu mới
                                <span class="text-danger">*</span>
                            </label>

                            <div class="input-group">
                                <input type="password"
                                       name="confirm_password"
                                       id="confirmPassword"
                                       class="form-control"
                                       minlength="8"
                                       maxlength="72"
                                       autocomplete="new-password"
                                       required>

                                <button type="button"
                                        class="btn btn-outline-secondary toggle-password"
                                        data-target="confirmPassword"
                                        aria-label="Hiện mật khẩu">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                    </div>

                    <!-- Thông báo kiểm tra mật khẩu -->
                    <div id="passwordMessage"
                         class="small mt-3"
                         aria-live="polite"></div>
                </div>

                <!-- Nút thao tác -->
                <div class="d-flex justify-content-end gap-2">

                    <a href="/profile"
                       class="btn btn-outline-secondary px-4">
                        Hủy
                    </a>

                    <button type="submit"
                            id="savePasswordBtn"
                            class="btn text-white px-4"
                            style="background:var(--primary);"
                            disabled>
                        <i class="bi bi-check-lg me-1"></i>
                        Đổi mật khẩu
                    </button>

                </div>

            </form>
        </div>
    </section>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const current = document.getElementById('currentPassword');
    const next = document.getElementById('newPassword');
    const confirm = document.getElementById('confirmPassword');
    const saveButton = document.getElementById('savePasswordBtn');
    const message = document.getElementById('passwordMessage');

    function validateForm() {

        const filled =
            current.value.length > 0 &&
            next.value.length > 0 &&
            confirm.value.length > 0;

        const validLength =
            next.value.length >= 8 &&
            new TextEncoder().encode(next.value).length <= 72;

        const matched = next.value === confirm.value;
        const different = current.value !== next.value;

        saveButton.disabled = !(
            filled && validLength && matched && different
        );

        if (confirm.value && !matched) {
            message.textContent = 'Mật khẩu xác nhận chưa khớp.';
        } else if (next.value && !validLength) {
            message.textContent = 'Mật khẩu mới phải có từ 8 đến 72 byte.';
        } else if (current.value && next.value && !different) {
            message.textContent = 'Mật khẩu mới phải khác mật khẩu hiện tại.';
        } else {
            message.textContent = '';
        }

        message.className = 'small mt-3 text-danger';
    }

    [current, next, confirm].forEach(input => {
        input.addEventListener('input', validateForm);
    });

    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function () {

            const input = document.getElementById(this.dataset.target);
            const icon = this.querySelector('i');

            const show = input.type === 'password';

            input.type = show ? 'text' : 'password';
            icon.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';

            this.setAttribute(
                'aria-label',
                show ? 'Ẩn mật khẩu' : 'Hiện mật khẩu'
            );
        });
    });

    validateForm();
});
</script>
