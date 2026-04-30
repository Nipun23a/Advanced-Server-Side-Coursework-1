<?= $this->extend('layouts/analytics') ?>
<?= $this->section('content') ?>

<?php $a = $alumni ?? []; ?>

<div class="mb-3">
    <a href="/alumni" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to Browse
    </a>
</div>

<!-- Profile header -->
<div class="chart-card mb-4">
    <div class="d-flex align-items-start gap-4 flex-wrap">

        <?php if (! empty($a['profile_image_url'])): ?>
            <img src="<?= esc($a['profile_image_url']) ?>" class="rounded-circle flex-shrink-0"
                 width="90" height="90" style="object-fit:cover">
        <?php else: ?>
            <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center flex-shrink-0"
                 style="width:90px;height:90px">
                <i class="bi bi-person-fill text-white" style="font-size:2.5rem"></i>
            </div>
        <?php endif; ?>

        <div class="flex-grow-1">
            <h5 class="mb-0 fw-bold"><?= esc($a['email'] ?? '') ?></h5>

            <?php if (! empty($a['programme']) || ! empty($a['graduation_year'])): ?>
                <div class="text-muted" style="font-size:.9rem">
                    <?= esc($a['programme'] ?? '') ?>
                    <?= ! empty($a['graduation_year']) ? ' · Class of ' . esc($a['graduation_year']) : '' ?>
                </div>
            <?php endif; ?>

            <?php if (! empty($a['bio'])): ?>
                <p class="mt-2 mb-0" style="font-size:.88rem; color:#444; max-width:600px"><?= esc($a['bio']) ?></p>
            <?php endif; ?>

            <div class="d-flex gap-2 mt-3 flex-wrap">
                <?php if (! empty($a['linkedin_url'])): ?>
                    <a href="<?= esc($a['linkedin_url']) ?>" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-linkedin me-1"></i> LinkedIn
                    </a>
                <?php endif; ?>
                <?php if (! empty($a['sector'])): ?>
                    <span class="badge bg-light text-dark border align-self-center"><?= esc($a['sector']) ?></span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Credential counts -->
        <div class="d-flex gap-4 text-center ms-auto">
            <div>
                <div class="fw-bold fs-4"><?= count($a['degrees'] ?? []) ?></div>
                <div class="text-muted" style="font-size:.75rem">Degrees</div>
            </div>
            <div>
                <div class="fw-bold fs-4"><?= count($a['certificates'] ?? []) ?></div>
                <div class="text-muted" style="font-size:.75rem">Certs</div>
            </div>
            <div>
                <div class="fw-bold fs-4"><?= count($a['licenses'] ?? []) ?></div>
                <div class="text-muted" style="font-size:.75rem">Licenses</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">

    <!-- Employment history -->
    <div class="col-lg-6">
        <div class="chart-card h-100">
            <div class="chart-title mb-3"><i class="bi bi-briefcase me-2 text-primary"></i>Employment History</div>
            <?php if (! empty($a['employment_history'])): ?>
                <?php foreach ($a['employment_history'] as $job): ?>
                    <div class="mb-3 pb-3 border-bottom last-no-border">
                        <div class="fw-semibold" style="font-size:.9rem"><?= esc($job['job_title'] ?? '') ?></div>
                        <div class="text-muted" style="font-size:.82rem"><?= esc($job['company_name'] ?? '') ?></div>
                        <div class="text-muted" style="font-size:.78rem">
                            <?= esc($job['start_date'] ?? '') ?> — <?= ! empty($job['end_date']) ? esc($job['end_date']) : 'Present' ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted mb-0" style="font-size:.85rem">No employment history recorded.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Degrees -->
    <div class="col-lg-6">
        <div class="chart-card h-100">
            <div class="chart-title mb-3"><i class="bi bi-mortarboard me-2 text-primary"></i>Degrees</div>
            <?php if (! empty($a['degrees'])): ?>
                <?php foreach ($a['degrees'] as $deg): ?>
                    <div class="mb-3 pb-3 border-bottom last-no-border">
                        <div class="fw-semibold" style="font-size:.9rem"><?= esc($deg['degree_name'] ?? '') ?></div>
                        <?php if (! empty($deg['institution_url'])): ?>
                            <a href="<?= esc($deg['institution_url']) ?>" target="_blank" rel="noopener" class="text-muted" style="font-size:.82rem">
                                <?= esc(parse_url($deg['institution_url'], PHP_URL_HOST) ?: $deg['institution_url']) ?>
                            </a>
                        <?php endif; ?>
                        <div class="text-muted" style="font-size:.78rem"><?= esc($deg['completion_date'] ?? '') ?></div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted mb-0" style="font-size:.85rem">No degrees recorded.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Certifications -->
    <div class="col-lg-6">
        <div class="chart-card h-100">
            <div class="chart-title mb-3"><i class="bi bi-patch-check me-2 text-success"></i>Certifications</div>
            <?php if (! empty($a['certificates'])): ?>
                <div class="d-flex flex-column gap-2">
                    <?php foreach ($a['certificates'] as $cert): ?>
                        <div class="border rounded p-2" style="font-size:.85rem">
                            <div class="fw-semibold"><?= esc($cert['certificate_name'] ?? '') ?></div>
                            <div class="text-muted"><?= esc($cert['issuer_name'] ?? '') ?> · <?= esc($cert['completion_date'] ?? '') ?></div>
                            <?php if (! empty($cert['certificate_url'])): ?>
                                <a href="<?= esc($cert['certificate_url']) ?>" target="_blank" rel="noopener" style="font-size:.78rem">View Course</a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-muted mb-0" style="font-size:.85rem">No certifications recorded.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Licenses -->
    <div class="col-lg-6">
        <div class="chart-card h-100">
            <div class="chart-title mb-3"><i class="bi bi-award me-2 text-warning"></i>Licenses</div>
            <?php if (! empty($a['licenses'])): ?>
                <div class="d-flex flex-column gap-2">
                    <?php foreach ($a['licenses'] as $lic): ?>
                        <div class="border rounded p-2" style="font-size:.85rem">
                            <div class="fw-semibold"><?= esc($lic['license_name'] ?? '') ?></div>
                            <div class="text-muted">
                                Issued: <?= esc($lic['completion_date'] ?? '') ?>
                                <?php if (! empty($lic['expiration_date'])): ?>
                                    · Expires: <?= esc($lic['expiration_date']) ?>
                                <?php endif; ?>
                            </div>
                            <?php if (! empty($lic['license_url'])): ?>
                                <a href="<?= esc($lic['license_url']) ?>" target="_blank" rel="noopener" style="font-size:.78rem">Verify</a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-muted mb-0" style="font-size:.85rem">No licenses recorded.</p>
            <?php endif; ?>
        </div>
    </div>

</div>

<?= $this->endSection() ?>
<?= $this->section('styles') ?>
<style>.last-no-border:last-child { border-bottom: none !important; }</style>
<?= $this->endSection() ?>
