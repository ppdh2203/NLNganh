<?php
/** @var array $application */
/** @var array $uploadedDocuments */
?>
<main style="background:#f8f7fc; min-height:75vh;">
    <section class="py-5">
        <div class="container" style="max-width:900px;">

            <a href="/my-applications"
                class="text-decoration-none d-inline-block mb-4"
                style="color:var(--primary);">
                <i class="bi bi-arrow-left me-2"></i>
                Quay lại hồ sơ đã nộp
            </a>

            <div class="bg-white border rounded-4 p-4 p-md-5">

                <div class="mb-4">
                    <h2 class="fw-bold mb-2">Chi tiết hồ sơ</h2>
                    <p class="text-muted mb-0">
                        Thông tin hồ sơ học bổng đã gửi
                    </p>
                </div>

                <hr>

                <h4 class="fw-bold mb-4">
                    <?= htmlspecialchars(
                        $application['scholarship_name'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </h4>

                <?php
                $statusLabels = [
                    'Submitted' => 'Đã nộp',
                    'Under Review' => 'Đang xem xét',
                    'Reviewed' => 'Đã xử lý'
                ];

                $status = $application['status'] ?? 'Submitted';
                $statusText = $statusLabels[$status] ?? 'Đang xử lý';
                ?>

                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="text-muted small mb-1">Mã hồ sơ</div>
                        <div class="fw-semibold">
                            #<?= (int) $application['application_id'] ?>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="text-muted small mb-1">Trạng thái</div>
                        <span class="badge rounded-pill px-3 py-2"
                            style="background:#eeeaff;
                                     color:var(--primary);">
                            <?= htmlspecialchars($statusText) ?>
                        </span>
                    </div>

                    <div class="col-md-6">
                        <div class="text-muted small mb-1">GPA</div>
                        <div class="fw-semibold">
                            <?= htmlspecialchars(
                                (string) $application['student_gpa']
                            ) ?>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="text-muted small mb-1">Năm học</div>
                        <div class="fw-semibold">
                            Năm <?= (int) $application['student_year'] ?>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="text-muted small mb-1">Ngày nộp</div>
                        <div class="fw-semibold">
                            <?= date(
                                'd/m/Y H:i',
                                strtotime($application['submitted_at'])
                            ) ?>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="text-muted small mb-1">
                            Giá trị học bổng
                        </div>
                        <div class="fw-bold" style="color:var(--primary);">
                            <?= number_format(
                                (float) $application['amount'],
                                0,
                                ',',
                                '.'
                            ) ?> VNĐ
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <h5 class="fw-bold mb-3">Ghi chú đã gửi</h5>

                <div class="border rounded-3 p-3"
                    style="background:#faf9ff; white-space:pre-line;">
                    <?= !empty($application['note'])
                        ? htmlspecialchars(
                            $application['note'],
                            ENT_QUOTES,
                            'UTF-8'
                        )
                        : 'Không có ghi chú.' ?>
                </div>


                <hr class="my-4">

                <h5 class="fw-bold mb-3">
                    <i class="bi bi-file-earmark-pdf me-2"
                        style="color:var(--primary);"></i>
                    Giấy tờ đã nộp
                </h5>

                <?php if (!empty($uploadedDocuments)): ?>

                    <div class="d-flex flex-column gap-3">

                        <?php foreach ($uploadedDocuments as $document): ?>

                            <div class="border rounded-3 p-3
                        d-flex justify-content-between
                        align-items-center gap-3 flex-wrap">

                                <div class="d-flex align-items-center gap-3">
                                    <i class="bi bi-file-earmark-pdf fs-3"
                                        style="color:var(--primary);"></i>

                                    <div>
                                        <div class="fw-semibold">
                                            <?= htmlspecialchars(
                                                $document['doc_name'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>
                                        </div>

                                        <div class="text-muted small">
                                            Tài liệu PDF
                                        </div>
                                    </div>
                                </div>

                                <a href="/my-applications/<?= (int) $application['application_id'] ?>/documents/<?= (int) $document['doc_id'] ?>"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="btn btn-sm fw-semibold"
                                    style="border:1px solid var(--primary);
                          color:var(--primary);">

                                    <i class="bi bi-eye me-1"></i>
                                    Xem PDF
                                </a>

                            </div>

                        <?php endforeach; ?>

                    </div>

                <?php else: ?>

                    <p class="text-muted">
                        Hồ sơ này không có tài liệu đính kèm.
                    </p>

                <?php endif; ?>

                <div class="mt-4">
                    <a href="/scholarships/<?= (int) $application['scholarship_id'] ?>"
                        class="btn fw-semibold"
                        style="border:1px solid var(--primary);
                              color:var(--primary);">
                        Xem học bổng
                    </a>
                </div>

            </div>
        </div>
    </section>
</main>