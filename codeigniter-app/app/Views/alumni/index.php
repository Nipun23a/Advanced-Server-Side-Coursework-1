<?= $this->extend('layouts/analytics') ?>
<?= $this->section('content') ?>

<?php
$programmes = $filterOptions['programmes']      ?? [];
$years      = $filterOptions['graduation_years'] ?? [];
$sectors    = $filterOptions['sectors']          ?? [];
$active     = $activeFilters ?? [];
$alumniList = $alumni ?? [];
$totalCount = $total  ?? 0;
$page       = (int) ($active['page']  ?? 1);
$limit      = (int) ($active['limit'] ?? 20);
$totalPages = $limit > 0 ? (int) ceil($totalCount / $limit) : 1;
?>

<!-- Filter bar -->
<form method="get" class="filter-bar d-flex flex-wrap gap-3 align-items-end mb-4">
    <div>
        <label class="form-label mb-1" style="font-size:.8rem">Programme</label>
        <select name="programme" class="form-select form-select-sm" style="min-width:170px">
            <option value="">All Programmes</option>
            <?php foreach ($programmes as $p): ?><option value="<?= esc($p) ?>" <?= ($active['programme'] ?? '') === $p ? 'selected' : '' ?>><?= esc($p) ?></option><?php endforeach; ?>
        </select>
    </div>
    <div>
        <label class="form-label mb-1" style="font-size:.8rem">Graduation Year</label>
        <select name="graduationYear" class="form-select form-select-sm">
            <option value="">All Years</option>
            <?php foreach ($years as $y): ?><option value="<?= esc($y) ?>" <?= ($active['graduationYear'] ?? '') == $y ? 'selected' : '' ?>><?= esc($y) ?></option><?php endforeach; ?>
        </select>
    </div>
    <div>
        <label class="form-label mb-1" style="font-size:.8rem">Sector</label>
        <select name="sector" class="form-select form-select-sm" style="min-width:150px">
            <option value="">All Sectors</option>
            <?php foreach ($sectors as $s): ?><option value="<?= esc($s) ?>" <?= ($active['sector'] ?? '') === $s ? 'selected' : '' ?>><?= esc($s) ?></option><?php endforeach; ?>
        </select>
    </div>
    <div>
        <label class="form-label mb-1" style="font-size:.8rem">Per page</label>
        <select name="limit" class="form-select form-select-sm">
            <?php foreach ([10,20,50] as $l): ?><option value="<?= $l ?>" <?= $limit == $l ? 'selected' : '' ?>><?= $l ?></option><?php endforeach; ?>
        </select>
    </div>
    <input type="hidden" name="page" value="1">
    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
        <a href="/alumni" class="btn btn-outline-secondary btn-sm">Reset</a>
    </div>
</form>

<!-- Result count -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <span class="text-muted" style="font-size:.875rem">
        Showing <strong><?= count($alumniList) ?></strong> of <strong><?= number_format($totalCount) ?></strong> alumni
        <?php if (! empty($active['programme']) || ! empty($active['graduationYear']) || ! empty($active['sector'])): ?>
            <span class="badge bg-primary ms-2">Filtered</span>
        <?php endif; ?>
    </span>
    <a href="/export/csv?<?= http_build_query(array_filter($active)) ?>" class="btn btn-outline-success btn-sm">
        <i class="bi bi-download me-1"></i> Export CSV
    </a>
</div>

<!-- Alumni cards grid -->
<?php if (empty($alumniList)): ?>
    <div class="chart-card text-center py-5 text-muted">
        <i class="bi bi-people fs-1 d-block mb-3 opacity-25"></i>
        <p class="mb-0">No alumni found matching the selected filters.</p>
    </div>
<?php else: ?>
    <div class="row g-3 mb-4">
        <?php foreach ($alumniList as $a): ?>
            <div class="col-sm-6 col-xl-4">
                <div class="card h-100 border-0 shadow-sm" style="border-radius:12px">
                    <div class="card-body d-flex gap-3">
                        <!-- Avatar -->
                        <?php if (! empty($a['profile_image_url'])): ?>
                            <img src="<?= esc($a['profile_image_url']) ?>" class="rounded-circle flex-shrink-0"
                                 width="52" height="52" style="object-fit:cover">
                        <?php else: ?>
                            <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center flex-shrink-0"
                                 style="width:52px;height:52px">
                                <i class="bi bi-person-fill text-white fs-5"></i>
                            </div>
                        <?php endif; ?>

                        <div class="flex-grow-1 overflow-hidden">
                            <div class="fw-semibold text-truncate" style="font-size:.9rem">
                                <?= esc($a['email'] ?? '') ?>
                            </div>
                            <?php if (! empty($a['programme'])): ?>
                                <div class="text-muted" style="font-size:.78rem"><?= esc($a['programme']) ?> <?= ! empty($a['graduation_year']) ? '· ' . esc($a['graduation_year']) : '' ?></div>
                            <?php endif; ?>
                            <?php if (! empty($a['current_role'])): ?>
                                <div class="text-truncate" style="font-size:.82rem; color:#444">
                                    <?= esc($a['current_role']) ?>
                                    <?= ! empty($a['current_employer']) ? ' @ ' . esc($a['current_employer']) : '' ?>
                                </div>
                            <?php endif; ?>
                            <?php if (! empty($a['sector'])): ?>
                                <span class="badge bg-light text-dark border mt-1" style="font-size:.72rem"><?= esc($a['sector']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-top-0 pt-0 pb-2 px-3 d-flex gap-2">
                        <a href="/alumni/<?= (int)($a['id'] ?? 0) ?>" class="btn btn-outline-primary btn-sm flex-grow-1">View Profile</a>
                        <?php if (! empty($a['linkedin_url'])): ?>
                            <a href="<?= esc($a['linkedin_url']) ?>" target="_blank" rel="noopener" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-linkedin"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <nav>
            <ul class="pagination pagination-sm justify-content-center">
                <?php
                $baseQuery = array_merge(array_filter($active), ['limit' => $limit]);
                $prev = $page - 1;
                $next = $page + 1;
                ?>
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="/alumni?<?= esc(http_build_query(array_merge($baseQuery, ['page' => $prev]))) ?>">‹ Prev</a>
                </li>
                <?php for ($p = max(1, $page - 2); $p <= min($totalPages, $page + 2); $p++): ?>
                    <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                        <a class="page-link" href="/alumni?<?= esc(http_build_query(array_merge($baseQuery, ['page' => $p]))) ?>"><?= $p ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                    <a class="page-link" href="/alumni?<?= esc(http_build_query(array_merge($baseQuery, ['page' => $next]))) ?>">Next ›</a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
<?php endif; ?>

<?= $this->endSection() ?>
