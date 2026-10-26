<?php
declare(strict_types=1);
?>

<section class="provider-dashboard">
    <div class="mb-4">
        <a href="/provider" class="text-decoration-none text-secondary">
            <i class="bi bi-arrow-left me-1"></i> Quay lại tổng quan
        </a>
        <h2 class="h4 fw-bold mt-3">Đăng học bổng mới</h2>
        <p class="text-muted">
            Điền thông tin học bổng để gửi Admin xét duyệt.
        </p>
    </div>

    <form action="/provider/scholarships/create" method="POST">
        <input type="hidden" name="csrf_token"
               value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

        <div class="provider-card mb-4">
            <h3 class="h6 fw-bold mb-4">
                <i class="bi bi-mortarboard me-2"></i>Thông tin học bổng
            </h3>

            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">Tên học bổng <span class="text-danger">*</span></label>
                    <input type="text" name="scholarship_name"
                           class="form-control" maxlength="255" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Giá trị học bổng (VNĐ) <span class="text-danger">*</span></label>
                    <input type="number" name="amount"
                           class="form-control" min="1" step="1" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Ngày bắt đầu <span class="text-danger">*</span></label>
                    <input type="date" name="start_date" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Hạn nộp hồ sơ <span class="text-danger">*</span></label>
                    <input type="date" name="end_date" class="form-control" required>
                </div>

                <div class="col-12">
                    <label class="form-label">Mô tả học bổng <span class="text-danger">*</span></label>
                    <textarea name="description" class="form-control"
                              rows="5" required></textarea>
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
                    <input type="number" name="required_gpa"
                           class="form-control" min="0" max="4" step="0.01">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Năm học</label>
                    <input type="text" name="required_year"
                           class="form-control" placeholder="Ví dụ: Năm 2, năm 3">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Ngành học</label>
                    <input type="text" name="required_major"
                           class="form-control" placeholder="Ví dụ: Công nghệ thông tin">
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
            <a href="/provider" class="btn btn-outline-secondary">
                Hủy
            </a>
            <button type="submit" class="btn btn-provider">
                <i class="bi bi-send me-1"></i> Gửi xét duyệt
            </button>
        </div>
    </form>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const list = document.getElementById('documentList');
    const addButton = document.getElementById('addDocument');
    let documentIndex = 0;

    function addDocument() {
        if (documentIndex >= 30) {
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
                           class="form-control"
                           maxlength="255"
                           placeholder="Ví dụ: Bảng điểm"
                           required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Mô tả</label>
                    <input type="text"
                           name="document_description[${index}]"
                           class="form-control"
                           placeholder="Ví dụ: Bảng điểm gần nhất">
                </div>

                <div class="col-md-2">
                    <div class="form-check mb-2">
                        <input type="hidden"
                               name="document_required[${index}]"
                               value="0">
                        <input type="checkbox"
                               name="document_required[${index}]"
                               value="1"
                               class="form-check-input"
                               checked>
                        <label class="form-check-label">Bắt buộc</label>
                    </div>
                </div>

                <div class="col-md-1">
                    <button type="button"
                            class="btn btn-outline-danger btn-sm remove-document">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        `;

        row.querySelector('.remove-document').addEventListener('click', function () {
            row.remove();
        });

        list.appendChild(row);
    }

    addButton.addEventListener('click', addDocument);
    addDocument();
});
</script>
