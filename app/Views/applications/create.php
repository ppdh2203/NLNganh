<?php

/** @var array $scholarship */
/** @var array|false $requirements */
/** @var array $documents */
/** @var string|null $error */

$scholarshipId = (int) $scholarship['scholarship_id'];

$escape = static fn($value) =>
htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>

<main class="py-5" style="background:#f8f7ff; min-height:80vh;">
    <div class="container" style="max-width:1000px;">

        <!-- Quay lại -->
        <a href="/scholarships/<?= $scholarshipId ?>"
            class="text-decoration-none d-inline-block mb-4"
            style="color:var(--primary);">
            <i class="bi bi-arrow-left me-1"></i>
            Quay lại chi tiết học bổng
        </a>

        <!-- Tiêu đề -->
        <div class="mb-4">
            <h1 class="fw-bold mb-2">Nộp hồ sơ học bổng</h1>
            <p class="text-muted mb-0">
                Vui lòng điền thông tin và tải lên các giấy tờ
                theo yêu cầu trước khi nộp hồ sơ.
            </p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger rounded-3"
                role="alert">
                <i class="bi bi-exclamation-circle me-2"></i>
                <?= $escape($error) ?>
            </div>
        <?php endif; ?>

        <form
            action="/scholarships/<?= $scholarshipId ?>/apply"
            method="POST"
            enctype="multipart/form-data"
            id="applicationForm">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= $escape($_SESSION['csrf_token'] ?? '') ?>">

            <!-- Thông tin học bổng -->
            <div class="bg-white border rounded-4 p-4 mb-4">
                <h2 class="h5 fw-bold mb-3">
                    <i class="bi bi-mortarboard me-2"
                        style="color:var(--primary);"></i>
                    Thông tin học bổng
                </h2>

                <h3 class="h6 fw-bold mb-2">
                    <?= $escape($scholarship['scholarship_name']) ?>
                </h3>

                <p class="text-muted mb-2">
                    <i class="bi bi-building me-2"></i>
                    <?= $escape($scholarship['provider_name']) ?>
                </p>

                <p class="mb-0">
                    <i class="bi bi-calendar-event me-2"></i>
                    Hạn nộp:
                    <strong>
                        <?= date(
                            'd/m/Y',
                            strtotime($scholarship['end_date'])
                        ) ?>
                    </strong>
                </p>
            </div>

            <!-- Thông tin sinh viên -->
            <div class="bg-white border rounded-4 p-4 mb-4">
                <h2 class="h5 fw-bold mb-4">
                    <i class="bi bi-person-vcard me-2"
                        style="color:var(--primary);"></i>
                    Thông tin ứng tuyển
                </h2>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="studentGpa"
                            class="form-label fw-semibold">
                            Điểm trung bình GPA (thang 4.0)
                            <span class="text-danger">*</span>
                        </label>

                        <input
                            type="number"
                            id="studentGpa"
                            name="student_gpa"
                            class="form-control"
                            min="0"
                            max="4"
                            step="0.01"
                            placeholder="Ví dụ: 3.50"
                            required>

                        <?php if (
                            $requirements !== false &&
                            $requirements['required_gpa'] !== null
                        ): ?>
                            <div class="form-text">
                                GPA yêu cầu tối thiểu:
                                <strong>
                                    <?= $escape(
                                        $requirements['required_gpa']
                                    ) ?>
                                </strong>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6">
                        <label for="studentYear"
                            class="form-label fw-semibold">
                            Năm học hiện tại
                            <span class="text-danger">*</span>
                        </label>

                        <select
                            id="studentYear"
                            name="student_year"
                            class="form-select"
                            required>
                            <option value="">-- Chọn năm học --</option>

                            <?php for ($year = 1; $year <= 10; $year++): ?>
                                <option value="<?= $year ?>">
                                    Năm <?= $year ?>
                                </option>
                            <?php endfor; ?>
                        </select>

                        <div id="yearFeedback" class="small mt-2"></div>


                        <?php if (
                            $requirements !== false &&
                            isset($requirements['required_year']) &&
                            (int) $requirements['required_year'] > 0
                        ): ?>
                            <div class="form-text">
                                Năm học yêu cầu:
                                <strong>
                                    <?= (int) $requirements['required_year'] ?>
                                </strong>
                            </div>
                        <?php else: ?>
                            <div class="form-text">
                                Năm học yêu cầu:
                                <strong>Không giới hạn</strong>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>


                <div class="mt-3">
                    <label for="studentMajor" class="form-label fw-semibold">
                        Ngành học hiện tại
                        <span class="text-danger">*</span>
                    </label>

                    <input
                        type="text"
                        id="studentMajor"
                        name="student_major"
                        class="form-control"
                        placeholder="Ví dụ: Công nghệ thông tin"
                        maxlength="255"
                        required>

                    <?php
                    $requiredMajor = $requirements !== false
                        ? trim((string) ($requirements['required_major'] ?? ''))
                        : '';
                    ?>

                    <?php if ($requiredMajor !== ''): ?>
                        <div class="form-text">
                            Ngành học yêu cầu:
                            <strong><?= $escape($requiredMajor) ?></strong>
                        </div>
                    <?php endif; ?>

                    <div id="majorFeedback" class="small mt-2"></div>
                </div>

                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const input = document.getElementById('studentMajor');
                        const feedback = document.getElementById('majorFeedback');

                        const requiredMajor = <?= json_encode(
                                                    $requiredMajor,
                                                    JSON_UNESCAPED_UNICODE |
                                                        JSON_HEX_TAG |
                                                        JSON_HEX_AMP |
                                                        JSON_HEX_APOS |
                                                        JSON_HEX_QUOT
                                                ) ?>;

                        function normalizeMajor(value) {
                            return value.normalize('NFD')
                                .replace(/[\u0300-\u036f]/g, '')
                                .replace(/đ/g, 'd')
                                .toLowerCase()
                                .trim()
                                .replace(/\s+/g, ' ');
                        }

                        function checkMajor() {
                            const entered = normalizeMajor(input.value);

                            input.classList.remove('is-valid', 'is-invalid');
                            feedback.textContent = '';
                            feedback.className = 'small mt-2';

                            if (!entered || !requiredMajor.trim()) {
                                return;
                            }

                            const allowed = requiredMajor
                                .split(/[,;\n]+/)
                                .map(normalizeMajor)
                                .filter(Boolean);

                            const valid = allowed.includes(entered);

                            input.classList.add(valid ? 'is-valid' : 'is-invalid');
                            feedback.classList.add(
                                valid ? 'text-success' : 'text-danger'
                            );

                            feedback.textContent = valid ?
                                'Ngành học đáp ứng yêu cầu.' :
                                'Ngành học không phù hợp với yêu cầu của học bổng.';
                        }

                        input.addEventListener('input', checkMajor);
                        input.addEventListener('blur', checkMajor);
                    });
                </script>

            </div>

            <!-- Tải giấy tờ -->
            <div class="bg-white border rounded-4 p-4 mb-4">
                <h2 class="h5 fw-bold mb-2">
                    <i class="bi bi-file-earmark-pdf me-2"
                        style="color:var(--primary);"></i>
                    Giấy tờ ứng tuyển
                </h2>

                <p class="text-muted small mb-4">
                    Chỉ chấp nhận file PDF, tối đa 5 MB mỗi file.
                    Các giấy tờ có dấu
                    <span class="text-danger">*</span>
                    là bắt buộc.
                </p>

                <?php if (!empty($documents)): ?>
                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($documents as $document): ?>
                            <?php
                            $documentId = (int)
                            $document['required_documents_id'];

                            $isRequired =
                                (int) $document['is_required'] === 1;
                            ?>

                            <div class="border rounded-3 p-3">
                                <label
                                    for="document<?= $documentId ?>"
                                    class="form-label fw-semibold">

                                    <?= $escape($document['document_name']) ?>

                                    <?php if ($isRequired): ?>
                                        <span class="text-danger">*</span>
                                    <?php else: ?>
                                        <span class="text-muted fw-normal">
                                            (Không bắt buộc)
                                        </span>
                                    <?php endif; ?>
                                </label>

                                <?php if (!empty($document['description'])): ?>
                                    <p class="small text-muted mb-2">
                                        <?= $escape(
                                            $document['description']
                                        ) ?>
                                    </p>
                                <?php endif; ?>

                                <input
                                    type="file"
                                    id="document<?= $documentId ?>"
                                    name="documents[<?= $documentId ?>]"
                                    class="form-control application-pdf"
                                    accept=".pdf,application/pdf"
                                    <?= $isRequired ? 'required' : '' ?>>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0">
                        Học bổng này không yêu cầu tải lên giấy tờ.
                    </p>
                <?php endif; ?>
            </div>

            <!-- Ghi chú -->
            <div class="bg-white border rounded-4 p-4 mb-4">
                <h2 class="h5 fw-bold mb-3">
                    <i class="bi bi-pencil-square me-2"
                        style="color:var(--primary);"></i>
                    Ghi chú bổ sung
                </h2>

                <textarea
                    name="note"
                    class="form-control"
                    rows="4"
                    maxlength="2000"
                    placeholder="Nhập thông tin bổ sung nếu có..."></textarea>

                <div class="form-text">
                    Không bắt buộc, tối đa 2000 ký tự.
                </div>
            </div>

            <!-- Xác nhận -->
            <div class="bg-white border rounded-4 p-4">
                <div class="form-check mb-4">
                    <input
                        class="form-check-input"
                        type="checkbox"
                        id="confirmApplication"
                        required>

                    <label
                        class="form-check-label"
                        for="confirmApplication">
                        Tôi xác nhận các thông tin và tài liệu
                        đã cung cấp là chính xác.
                    </label>
                </div>

                <div class="d-flex flex-wrap justify-content-end gap-3">
                    <a
                        href="/scholarships/<?= $scholarshipId ?>"
                        class="btn btn-outline-secondary px-4 py-2">
                        Hủy
                    </a>


                    <button
                        type="submit"
                        id="submitApplication"
                        class="btn text-white fw-semibold px-4 py-2"
                        style="background:var(--primary); opacity:0.45;"
                        disabled>
                        <i class="bi bi-send me-2"></i>
                        Nộp hồ sơ
                    </button>

                </div>
            </div>
        </form>
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('applicationForm');
        const submitButton = document.getElementById('submitApplication');

        if (!form) return;

        const maxSize = 5 * 1024 * 1024;

        form.addEventListener('submit', function(event) {
            const inputs = form.querySelectorAll('.application-pdf');

            for (const input of inputs) {
                const file = input.files[0];

                if (!file) continue;

                const isPdf = file.name.toLowerCase().endsWith('.pdf');

                if (!isPdf) {
                    event.preventDefault();
                    alert('Chỉ được tải lên file PDF.');
                    input.focus();
                    return;
                }

                if (file.size > maxSize) {
                    event.preventDefault();
                    alert('Mỗi file PDF không được vượt quá 5 MB.');
                    input.focus();
                    return;
                }
            }

            if (submitButton && !event.defaultPrevented) {
                submitButton.disabled = true;
                submitButton.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-2"></span>' +
                    'Đang nộp hồ sơ...';
            }
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const gpaInput = document.getElementById('studentGpa');
        const yearInput = document.getElementById('studentYear');
        const majorInput = document.getElementById('studentMajor');

        const yearFeedback = document.getElementById('yearFeedback');
        const majorFeedback = document.getElementById('majorFeedback');

        const submitButton = document.getElementById('submitApplication');

        const requiredGpa = <?= json_encode(
                                $requirements !== false
                                    ? $requirements['required_gpa'] ?? null
                                    : null
                            ) ?>;

        const requiredYear = <?= json_encode(
                                    $requirements !== false
                                        ? $requirements['required_year'] ?? null
                                        : null
                                ) ?>;

        const requiredMajor = <?= json_encode(
                                    $requirements !== false
                                        ? $requirements['required_major'] ?? ''
                                        : '',
                                    JSON_UNESCAPED_UNICODE |
                                        JSON_HEX_TAG |
                                        JSON_HEX_AMP |
                                        JSON_HEX_APOS |
                                        JSON_HEX_QUOT
                                ) ?>;


        function normalizeMajor(value) {
            return value.normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .replace(/đ/g, 'd')
                .toLowerCase()
                .trim()
                .replace(/\s+/g, ' ');
        }


        function showFeedback(input, element, valid) {
            input.classList.remove('is-valid', 'is-invalid');

            if (element) {
                element.textContent = '';
            }

            if (valid === null) return;

            input.classList.add(
                valid ? 'is-valid' : 'is-invalid'
            );
        }

        function validateConditions() {
            // Kiểm tra GPA
            const gpaText = gpaInput.value.trim().replace(',', '.');
            const gpa = Number(gpaText);

            const gpaValid = gpaText !== '' &&
                Number.isFinite(gpa) &&
                gpa >= 0 &&
                gpa <= 4 &&
                (
                    requiredGpa === null ||
                    gpa >= Number(requiredGpa)
                );

            gpaInput.classList.remove('is-valid', 'is-invalid');

            if (gpaText !== '') {
                gpaInput.classList.add(
                    gpaValid ? 'is-valid' : 'is-invalid'
                );
            }

            // Kiểm tra năm học
            const year = yearInput.value;


            const hasYearRequirement =
                requiredYear !== null &&
                requiredYear !== '' &&
                Number(requiredYear) > 0;

            const yearValid = year !== '' &&
                (
                    !hasYearRequirement ||
                    Number(year) === Number(requiredYear)
                );


            showFeedback(
                yearInput,
                yearFeedback,
                year === '' ? null : yearValid,
                yearValid ?
                'Năm học đáp ứng yêu cầu.' :
                'Năm học không phù hợp với yêu cầu học bổng.'
            );

            // Kiểm tra ngành học
            const major = normalizeMajor(majorInput.value);

            const allowedMajors = String(requiredMajor)
                .split(/[,;\n]+/)
                .map(normalizeMajor)
                .filter(Boolean);

            const majorValid = major !== '' &&
                (
                    allowedMajors.length === 0 ||
                    allowedMajors.includes(major)
                );

            // Kiểm tra các file PDF bắt buộc
            const fileInputs = form.querySelectorAll('.application-pdf');

            const documentsValid = Array.from(fileInputs).every(input => {
                const file = input.files[0];

                if (!file) {
                    return !input.required;
                }

                return file.name.toLowerCase().endsWith('.pdf') &&
                    file.size > 0 &&
                    file.size <= 5 * 1024 * 1024;
            });

            // Kiểm tra đã tích xác nhận
            const confirmValid =
                document.getElementById('confirmApplication').checked;
            showFeedback(
                majorInput,
                majorFeedback,
                major === '' ? null : majorValid
            );
            // Chỉ hiển thị nút khi cả ba điều kiện đều đạt

            const isEligible =
                gpaValid &&
                yearValid &&
                majorValid &&
                documentsValid &&
                confirmValid;

            submitButton.disabled = !isEligible;
            submitButton.style.opacity = isEligible ? '1' : '0.45';
            submitButton.style.cursor = isEligible ? 'pointer' : 'not-allowed';
            submitButton.disabled = !isEligible;

            submitButton.style.opacity = isEligible ? '1' : '0.45';
            submitButton.style.cursor = isEligible ? 'pointer' : 'not-allowed';

        }

        gpaInput.addEventListener('input', validateConditions);
        yearInput.addEventListener('change', validateConditions);
        majorInput.addEventListener('input', validateConditions);


        document.querySelectorAll('.application-pdf').forEach(input => {
            input.addEventListener('change', validateConditions);
        });

        document.getElementById('confirmApplication')
            .addEventListener('change', validateConditions);
        validateConditions();
    });
</script>