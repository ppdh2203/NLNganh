<?php

declare(strict_types=1);

/** @var array $student */
/** @var string|null $success */
/** @var string|null $error */

$e = static fn($value): string =>
htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');

?>


<section class="student-profile student-profile-bg">

    <!-- Dải tiêu đề riêng -->
    <div class="student-profile-hero">
        <div class="container">
            <a href="/" class="text-decoration-none text-secondary">
                <i class="bi bi-arrow-left me-1"></i>
                Quay lại
            </a>

            <h1 class="h4 fw-bold mt-3 mb-2">
                THÔNG TIN CÁ NHÂN
            </h1>

        </div>
    </div>

    <div class="container student-profile-content">
        <!-- Thông báo -->
        <?php if (!empty($success)): ?>
            <div class="alert alert-success alert-dismissible fade show"
                role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                <?= $e($success) ?>

                <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                    aria-label="Đóng"></button>
            </div>
        <?php endif; ?>

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

        <form action="/profile" method="POST" id="studentProfileForm">

            <input type="hidden"
                name="csrf_token"
                value="<?= $e($_SESSION['csrf_token'] ?? '') ?>">

            <!-- Thông tin tài khoản -->
            <div class="student-profile-card mb-4">

                <h2 class="h6 fw-bold mb-4">
                    <i class="bi bi-person-badge me-2"></i>
                    Thông tin tài khoản
                </h2>

                <div class="row g-3">

                    <div class="col-md-6">
                        <label for="studentCode" class="form-label">
                            Mã sinh viên
                        </label>
                        <input type="text"
                            id="studentCode"
                            class="form-control"
                            value="<?= $e($student['student_code']) ?>"
                            readonly>
                        <div class="form-text">
                            Mã sinh viên không thể thay đổi.
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="studentEmail" class="form-label">
                            Email đăng nhập
                        </label>
                        <input type="email"
                            id="studentEmail"
                            class="form-control"
                            value="<?= $e($student['email']) ?>"
                            readonly>
                    </div>

                </div>
            </div>

            <!-- Thông tin sinh viên -->
            <div class="student-profile-card mb-4">

                <h2 class="h6 fw-bold mb-4">
                    <i class="bi bi-mortarboard me-2"></i>
                    Thông tin sinh viên
                </h2>

                <div class="row g-3">

                    <div class="col-md-6">
                        <label for="studentName" class="form-label">
                            Họ và tên <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                            id="studentName"
                            name="student_name"
                            class="form-control"
                            maxlength="255"
                            value="<?= $e($student['student_name']) ?>"
                            required>
                    </div>

                    <div class="col-md-6">
                        <label for="studentSchool" class="form-label">
                            Trường học <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                            id="studentSchool"
                            name="student_school"
                            class="form-control"
                            maxlength="255"
                            value="<?= $e($student['student_school']) ?>"
                            required>
                    </div>

                    <div class="col-md-6">
                        <label for="studentMajor" class="form-label">
                            Ngành học <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                            id="studentMajor"
                            name="student_major"
                            class="form-control"
                            maxlength="255"
                            value="<?= $e($student['student_major']) ?>"
                            required>
                    </div>

                    <div class="col-md-6">
                        <label for="studentCohort" class="form-label">
                            Khóa học <span class="text-danger">*</span>
                        </label>
                        <input type="number"
                            id="studentCohort"
                            name="student_cohort"
                            class="form-control"
                            min="1"
                            max="4294967295"
                            value="<?= $e($student['student_cohort']) ?>"
                            required>
                    </div>

                    <div class="col-md-6">
                        <label for="studentPhone" class="form-label">
                            Số điện thoại <span class="text-danger">*</span>
                        </label>
                        <input type="tel"
                            id="studentPhone"
                            name="student_phone"
                            class="form-control"
                            maxlength="20"
                            value="<?= $e($student['student_phone']) ?>"
                            required>
                    </div>

                    <div class="col-md-6">
                        <label for="studentAddress" class="form-label">
                            Địa chỉ <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                            id="studentAddress"
                            name="student_address"
                            class="form-control"
                            maxlength="255"
                            value="<?= $e($student['student_address']) ?>"
                            required>
                    </div>

                </div>
            </div>

            <!-- Nút thao tác -->
            <div class="d-flex justify-content-end gap-2 mb-4">
                <button type="button"
                    id="cancelProfileBtn"
                    class="btn btn-outline-secondary"
                    disabled>
                    Hủy thay đổi
                </button>

                <button type="submit"
                    id="saveProfileBtn"
                    class="btn btn-student-primary"
                    disabled>
                    <i class="bi bi-check-lg me-1"></i>
                    Lưu thay đổi
                </button>
            </div>

        </form>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('studentProfileForm');
        const saveButton = document.getElementById('saveProfileBtn');
        const cancelButton = document.getElementById('cancelProfileBtn');

        if (!form || !saveButton || !cancelButton) return;

        const fields = [
            ...form.querySelectorAll(
                'input[name]:not([type="hidden"])'
            )
        ];

        const originalValues = fields.map(field => field.value);

        function checkChanges() {
            const changed = fields.some(
                (field, index) => field.value !== originalValues[index]
            );

            saveButton.disabled = !changed;
            cancelButton.disabled = !changed;
        }

        fields.forEach(field => {
            field.addEventListener('input', checkChanges);
        });

        cancelButton.addEventListener('click', function() {
            fields.forEach((field, index) => {
                field.value = originalValues[index];
            });

            checkChanges();
        });

        checkChanges();
    });
</script>