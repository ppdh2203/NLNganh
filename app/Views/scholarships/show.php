<?php

/** @var array $scholarship */
/** @var array|false $requirements */
/** @var array $documents */
?>
<main>
    <!-- Tiêu đề học bổng -->
    <section class="py-5" style="background:var(--light-blue);">
        <div class="container">
            <a href="/scholarships" class="text-decoration-none d-inline-block mb-3" style="color:var(--primary);">
                <i class="bi bi-arrow-left"></i> Back to Scholarships
            </a>
            <h1 class="fw-bold mb-3"><?= htmlspecialchars($scholarship['scholarship_name'], ENT_QUOTES, 'UTF-8') ?></h1>
            <p class="mb-0" style="color:var(--text-muted);">
                <i class="bi bi-building"></i>
                <?= htmlspecialchars($scholarship['provider_name'], ENT_QUOTES, 'UTF-8') ?>
            </p>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <div class="row g-4">
                <!-- Nội dung học bổng -->

                <!-- Nội dung học bổng và yêu cầu ứng tuyển -->
                <div class="col-lg-8">
                    <div class="bg-white border rounded-4 p-4 p-md-5">
                        <!-- Mô tả học bổng -->
                        <h2 class="h4 fw-bold mb-3">Scholarship Description</h2>
                        <p class="lh-lg mb-4" style="white-space:pre-line;"><?= htmlspecialchars($scholarship['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>

                        <hr class="my-4">

                        <!-- Điều kiện ứng tuyển -->
                        <h2 class="h4 fw-bold mb-3">Eligibility Requirements</h2>
                        <?php if ($requirements !== false && array_filter($requirements, fn($value) => $value !== null && $value !== '')): ?>
                            <ul class="list-unstyled mb-4">
                                <?php if ($requirements['required_gpa'] !== null): ?>
                                    <li class="mb-3"><i class="bi bi-check-circle me-2" style="color:var(--primary);"></i>Minimum GPA: <strong><?= htmlspecialchars((string)$requirements['required_gpa'], ENT_QUOTES, 'UTF-8') ?></strong></li>
                                <?php endif; ?>
                                <?php if ($requirements['required_year'] !== null): ?>
                                    <li class="mb-3"><i class="bi bi-check-circle me-2" style="color:var(--primary);"></i>Required Study Year: <strong><?= (int)$requirements['required_year'] ?></strong></li>
                                <?php endif; ?>
                                <?php if (!empty($requirements['required_major'])): ?>
                                    <li class="mb-3"><i class="bi bi-check-circle me-2" style="color:var(--primary);"></i>Required Major: <strong><?= htmlspecialchars($requirements['required_major'], ENT_QUOTES, 'UTF-8') ?></strong></li>
                                <?php endif; ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-muted mb-4">No specific eligibility requirements have been provided.</p>
                        <?php endif; ?>

                        <hr class="my-4">

                        <!-- Danh sách giấy tờ -->
                        <h2 class="h4 fw-bold mb-3">Required Documents</h2>
                        <?php if (!empty($documents)): ?>
                            <div class="d-flex flex-column gap-3">
                                <?php foreach ($documents as $document): ?>
                                    <div class="border rounded-3 p-3">
                                        <div class="d-flex justify-content-between align-items-start gap-2">
                                            <span class="fw-semibold"><?= htmlspecialchars($document['document_name'], ENT_QUOTES, 'UTF-8') ?></span>
                                            <?php if ((int)$document['is_required'] === 1): ?>
                                                <span class="badge text-bg-danger">Required</span>
                                            <?php else: ?>
                                                <span class="badge text-bg-secondary">Optional</span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (!empty($document['description'])): ?>
                                            <p class="text-muted small mt-2 mb-0"><?= htmlspecialchars($document['description'], ENT_QUOTES, 'UTF-8') ?></p>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted mb-0">No documents have been specified.</p>
                        <?php endif; ?>
                    </div>
                </div>


                <!-- Thông tin tóm tắt -->
                <div class="col-lg-4">
                    <div class="bg-white border rounded-4 p-4">
                        <h2 class="h5 fw-bold mb-4">Scholarship Overview</h2>
                        <div class="mb-4">
                            <div class="text-muted small mb-1"><i class="bi bi-cash-coin me-2"></i>Scholarship Amount</div>
                            <div class="fw-bold fs-5" style="color:var(--primary);"><?= number_format((float)$scholarship['amount'], 0, ',', '.') ?> VND</div>
                        </div>
                        <div class="mb-4">
                            <div class="text-muted small mb-1"><i class="bi bi-calendar-check me-2"></i>Application Opens</div>
                            <div class="fw-semibold"><?= date('d/m/Y', strtotime($scholarship['start_date'])) ?></div>
                        </div>
                        <div class="mb-4">
                            <div class="text-muted small mb-1"><i class="bi bi-calendar-event me-2"></i>Application Deadline</div>
                            <div class="fw-semibold"><?= date('d/m/Y', strtotime($scholarship['end_date'])) ?></div>
                        </div>
                        <div class="mb-4">
                            <span class="badge rounded-pill px-3 py-2" style="color:var(--primary-dark);background:var(--light-blue);">Accepting Applications</span>
                        </div>
                        <button type="button" class="btn w-100 text-white fw-semibold py-2" style="background:var(--primary);" disabled>
                            Apply Now
                        </button>
                        <p class="text-muted small text-center mt-2 mb-0">Online applications will be available soon.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>