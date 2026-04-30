<?= $this->extend('layouts/analytics') ?>

<?= $this->section('content') ?>

<!-- ---- Summary stat cards ---- -->
<div class="row g-3 mb-4">

    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div>
                    <div class="text-muted" style="font-size:0.8rem;">Total Alumni</div>
                    <div class="fw-bold fs-4"><?= esc($summary['total_alumni'] ?? '—') ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-patch-check-fill"></i>
                </div>
                <div>
                    <div class="text-muted" style="font-size:0.8rem;">Certifications</div>
                    <div class="fw-bold fs-4"><?= esc($summary['total_certifications'] ?? '—') ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-award-fill"></i>
                </div>
                <div>
                    <div class="text-muted" style="font-size:0.8rem;">Licenses</div>
                    <div class="fw-bold fs-4"><?= esc($summary['total_licenses'] ?? '—') ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon bg-info bg-opacity-10 text-info">
                    <i class="bi bi-mortarboard-fill"></i>
                </div>
                <div>
                    <div class="text-muted" style="font-size:0.8rem;">Top Programme</div>
                    <div class="fw-semibold" style="font-size:0.95rem;">
                        <?= esc($summary['top_programme'] ?? '—') ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- ---- Second row: top job title + quick links ---- -->
<div class="row g-3 mb-4">

    <div class="col-md-6">
        <div class="chart-card h-100">
            <div class="chart-title">Top Job Title</div>
            <div class="chart-subtitle">Most common role held by alumni</div>
            <div class="d-flex align-items-center gap-2 mt-2">
                <i class="bi bi-briefcase-fill text-primary fs-4"></i>
                <span class="fs-5 fw-semibold"><?= esc($summary['top_job_title'] ?? '—') ?></span>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="chart-card h-100">
            <div class="chart-title">Quick Links</div>
            <div class="chart-subtitle">Jump to an analytics view</div>
            <div class="d-flex flex-wrap gap-2 mt-2">
                <a href="/analytics/skills-gap"           class="btn btn-sm btn-outline-primary">Skills Gap</a>
                <a href="/analytics/employment-sectors"   class="btn btn-sm btn-outline-primary">Sectors</a>
                <a href="/analytics/job-titles"           class="btn btn-sm btn-outline-primary">Job Titles</a>
                <a href="/analytics/certification-trends" class="btn btn-sm btn-outline-primary">Cert Trends</a>
                <a href="/alumni"                          class="btn btn-sm btn-outline-secondary">Browse Alumni</a>
                <a href="/export"                          class="btn btn-sm btn-outline-secondary">Export CSV</a>
            </div>
        </div>
    </div>

</div>

<!-- ---- Today's featured alumni ---- -->
<div class="row g-3">
    <div class="col-12">
        <div class="chart-card">
            <div class="chart-title"><i class="bi bi-star-fill text-warning me-1"></i> Alumni of the Day</div>
            <div class="chart-subtitle">Today's featured alumni selected by the bidding system</div>

            <?php if (! empty($featured)): ?>
                <div class="d-flex align-items-start gap-4 mt-3">

                    <?php if (! empty($featured['profile_image_url'])): ?>
                        <img src="<?= esc($featured['profile_image_url']) ?>"
                             alt="Profile"
                             class="rounded-circle"
                             style="width:80px;height:80px;object-fit:cover;">
                    <?php else: ?>
                        <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center"
                             style="width:80px;height:80px;flex-shrink:0;">
                            <i class="bi bi-person-fill text-white fs-2"></i>
                        </div>
                    <?php endif; ?>

                    <div class="flex-grow-1">
                        <h5 class="mb-1"><?= esc($featured['email'] ?? '') ?></h5>

                        <?php if (! empty($featured['bio'])): ?>
                            <p class="text-muted mb-2" style="font-size:0.9rem;">
                                <?= esc(mb_substr($featured['bio'], 0, 200)) ?>
                                <?= strlen($featured['bio']) > 200 ? '…' : '' ?>
                            </p>
                        <?php endif; ?>

                        <?php if (! empty($featured['linkedin_url'])): ?>
                            <a href="<?= esc($featured['linkedin_url']) ?>"
                               target="_blank" rel="noopener"
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-linkedin me-1"></i> LinkedIn
                            </a>
                        <?php endif; ?>
                    </div>

                    <!-- Mini credential counts -->
                    <div class="d-flex gap-3 text-center flex-shrink-0">
                        <div>
                            <div class="fw-bold fs-5"><?= count($featured['certificates'] ?? []) ?></div>
                            <div class="text-muted" style="font-size:0.75rem;">Certs</div>
                        </div>
                        <div>
                            <div class="fw-bold fs-5"><?= count($featured['licenses'] ?? []) ?></div>
                            <div class="text-muted" style="font-size:0.75rem;">Licenses</div>
                        </div>
                        <div>
                            <div class="fw-bold fs-5"><?= count($featured['degrees'] ?? []) ?></div>
                            <div class="text-muted" style="font-size:0.75rem;">Degrees</div>
                        </div>
                    </div>

                </div>
            <?php else: ?>
                <div class="text-muted mt-3">
                    <i class="bi bi-info-circle me-1"></i>
                    No alumni featured today yet — the selection runs automatically at midnight.
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<?= $this->endSection() ?>
