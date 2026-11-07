
<main style="background:#f8f7fc; min-height:75vh;">
    <section class="py-5">
        <div class="container">
            <h2 class="fw-bold mb-2">Hồ sơ đã nộp</h2>
            <p class="text-muted mb-4">
                Theo dõi các hồ sơ học bổng bạn đã ứng tuyển.
            </p>

            <?php if (empty($applications)): ?>
                <div class="bg-white border rounded-4 p-5 text-center">
                    <i class="bi bi-file-earmark-text fs-1"
                       style="color:var(--primary);"></i>
                    <h5 class="fw-bold mt-3">Chưa có hồ sơ nào</h5>
                    <p class="text-muted">
                        Bạn chưa nộp hồ sơ ứng tuyển học bổng.
                    </p>
                    <a href="/scholarships"
                       class="btn text-white px-4"
                       style="background:var(--primary);">
                        Khám phá học bổng
                    </a>
                </div>
            <?php else: ?>

                <div class="row g-4">
                    <?php foreach ($applications as $application): ?>
                        <?php
                        $status = $application['status'] ?? 'Submitted';

                        $statusLabels = [
                            'Submitted' => 'Đã nộp',
                            'Under Review' => 'Đang xem xét',
                            'Reviewed' => 'Đã xử lý'
                        ];

                        $statusText = $statusLabels[$status]
                            ?? 'Đang xử lý';
                        ?>

                        <div class="col-lg-6">
                            <div class="bg-white border rounded-4 p-4 h-100">

                                <div class="d-flex justify-content-between
                                            align-items-start gap-3 mb-3">
                                    <h5 class="fw-bold mb-0">
                                        <?= htmlspecialchars(
                                            $application['scholarship_name'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </h5>

                                    <span class="badge rounded-pill px-3 py-2"
                                          style="background:#eeeaff;
                                                 color:var(--primary);">
                                        <?= htmlspecialchars($statusText) ?>
                                    </span>
                                </div>

                                <p class="text-muted mb-2">
                                    <i class="bi bi-calendar-check me-2"></i>
                                    Ngày nộp:
                                    <?= date(
                                        'd/m/Y H:i',
                                        strtotime($application['submitted_at'])
                                    ) ?>
                                </p>

                                <p class="text-muted mb-2">
                                    <i class="bi bi-mortarboard me-2"></i>
                                    GPA:
                                    <?= htmlspecialchars(
                                        (string) $application['student_gpa']
                                    ) ?>
                                </p>

                                <p class="text-muted mb-3">
                                    <i class="bi bi-cash-coin me-2"></i>
                                    Giá trị học bổng:
                                    <strong style="color:var(--primary);">
                                        <?= number_format(
                                            (float) $application['amount'],
                                            0, ',', '.'
                                        ) ?> VNĐ
                                    </strong>
                                </p>

                                <a href="/my-applications/<?= (int) $application['application_id'] ?>"
                                   class="btn text-white fw-semibold w-100"
                                   style="background:var(--primary);">
                                    <i class="bi bi-eye me-2"></i>
                                    Xem chi tiết hồ sơ
                                </a>

                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

            <?php endif; ?>
        </div>
    </section>
</main>
