<?php

declare(strict_types=1);

/** @var array $scholarship */
/** @var array|false $requirements */
/** @var array $documents */

$e = static fn($value): string =>
htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');

$id = (int) $scholarship['scholarship_id'];
?>

<section class="provider-dashboard">
    <div class="mb-4">
        <a href="/provider" class="text-decoration-none text-secondary">
            <i class="bi bi-arrow-left me-1"></i> Quay lại chi tiết học bổng
        </a>
        <h2 class="h4 fw-bold mt-3">Chỉnh sửa học bổng</h2>
        <p class="text-muted">
            Cập nhật thông tin theo lý do từ chối và gửi Admin xét duyệt lại.
        </p>
    </div>

    <form action="/provider/scholarships/<?= $id ?>/edit" method="POST">
        <input type="hidden" name="csrf_token"
            value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

        <div class="provider-card mb-4">
            <h3 class="h6 fw-bold mb-4">
                <i class="bi bi-mortarboard me-2"></i>Thông tin học bổng
            </h3>

            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">Tên học bổng <span class="text-danger">*</span></label>
                    <input type="text"
                        name="scholarship_name"
                        class="form-control"
                        maxlength="255"
                        value="<?= $e($scholarship['scholarship_name'] ?? '') ?>"
                        required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Giá trị học bổng (VNĐ) <span class="text-danger">*</span></label>
                    <input type="number"
                        name="amount"
                        class="form-control"
                        min="1"
                        step="1"
                        value="<?= $e($scholarship['amount'] ?? '') ?>"
                        required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Ngày bắt đầu <span class="text-danger">*</span></label>
                    <input type="date"
                        name="start_date"
                        class="form-control"
                        value="<?= $e(substr((string) ($scholarship['start_date'] ?? ''), 0, 10)) ?>"
                        required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Hạn nộp hồ sơ <span class="text-danger">*</span></label>
                    <input type="date"
                        name="end_date"
                        class="form-control"
                        value="<?= $e(substr((string) ($scholarship['end_date'] ?? ''), 0, 10)) ?>"
                        required>
                </div>

                <div class="col-12">
                    <label class="form-label">Mô tả học bổng <span class="text-danger">*</span></label>
                    <textarea name="description"
                        class="form-control"
                        rows="5"
                        required><?= $e($scholarship['description']) ?></textarea>
                </div>
            </div>
        </div>

        <div class="provider-card mb-4">
            <h3 class="h6 fw-bold mb-4">
                <i class="bi bi-clipboard-check me-2"></i>Điều kiện ứng tuyển
            </h3>

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">GPA tối thiểu (thang 4)</label>
                    <input type="number"
                        name="required_gpa"
                        class="form-control"
                        min="0" max="4" step="0.01"
                        value="<?= $e($requirements['required_gpa'] ?? '') ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Năm học</label>
                    <input type="number"
                        name="required_year"
                        class="form-control"
                        min="1" step="1"
                        value="<?= $e($requirements['required_year'] ?? '') ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Ngành học</label>
                    <input type="text"
                        name="required_major"
                        class="form-control"
                        value="<?= $e($requirements['required_major'] ?? '') ?>">
                </div>
            </div>
        </div>

        <div class="provider-card mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="h6 fw-bold mb-0">
                    <i class="bi bi-file-earmark-text me-2"></i>Giấy tờ yêu cầu
                </h3>

                <button type="button" class="btn btn-outline-primary btn-sm"
                    id="addDocument">
                    <i class="bi bi-plus-lg me-1"></i> Thêm giấy tờ
                </button>
            </div>

            <p class="text-muted small">
                Có thể thêm giấy tờ bắt buộc hoặc không bắt buộc.
            </p>

            <div id="documentList"></div>
        </div>

        <div class="d-flex justify-content-end gap-2 mb-4">
            <a href="/provider/scholarships/<?= $id ?>"
                class="btn btn-outline-secondary">
                Hủy
            </a>
            <button type="submit" class="btn btn-provider">
                <i class="bi bi-arrow-repeat me-1"></i>
                Lưu và gửi duyệt lại
            </button>
        </div>
    </form>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const list = document.getElementById('documentList');
        const addButton = document.getElementById('addDocument');

        const existingDocuments = <?= json_encode(
                                        $documents,
                                        JSON_HEX_TAG | JSON_HEX_AMP |
                                            JSON_HEX_APOS | JSON_HEX_QUOT
                                    ) ?>;

        let documentIndex = 0;

        function addDocument(document = {}) {
            if (list.children.length >= 30) {
                alert('Chỉ được thêm tối đa 30 giấy tờ.');
                return;
            }

            const index = documentIndex++;
            const row = document.createElement('div');
            row.className = 'border rounded-3 p-3 mb-3';

            row.innerHTML = `
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label">Tên giấy tờ</label>
                    <input type="text"
                           name="document_name[${index}]"
                           class="form-control document-name"
                           maxlength="255"
                           required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Mô tả</label>
                    <input type="text"
                           name="document_description[${index}]"
                           class="form-control document-description">
                </div>

                <div class="col-md-2">
                    <div class="form-check mb-2">
                        <input type="hidden"
                               name="document_required[${index}]"
                               value="0">
                        <input type="checkbox"
                               name="document_required[${index}]"
                               value="1"
                               class="form-check-input document-required">
                        <label class="form-check-label">
                            Bắt buộc
                        </label>
                    </div>
                </div>

                <div class="col-md-1">
                    <button type="button"
                            class="btn btn-outline-danger btn-sm remove-document"
                            aria-label="Xóa giấy tờ">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        `;

            row.querySelector('.document-name').value =
                document.document_name ?? '';

            row.querySelector('.document-description').value =
                document.description ?? '';

            row.querySelector('.document-required').checked =
                document.is_required === undefined ?
                true :
                Number(document.is_required) === 1;

            row.querySelector('.remove-document')
                .addEventListener('click', function() {
                    row.remove();
                });

            list.appendChild(row);
        }

        addButton.addEventListener('click', function() {
            addDocument();
        });

        existingDocuments.forEach(addDocument);
    });
</script>