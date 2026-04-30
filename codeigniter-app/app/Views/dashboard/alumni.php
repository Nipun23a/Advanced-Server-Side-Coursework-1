<?= $this->extend('layouts/analytics') ?>

<?= $this->section('content') ?>

<?php
$hasProfile    = ! empty($profile);
$certCount     = count($certificates ?? []);
$licenseCount  = count($licenses ?? []);
$degreeCount   = count($degrees ?? []);
$bidBalance    = $balance['data']['balance']       ?? $balance['balance']       ?? null;
$monthlyUsed   = $bidStatus['data']['bids_this_month'] ?? $bidStatus['bids_this_month'] ?? 0;
$sponsorTotal  = $sponsorBalance['data']['total_earned'] ?? $sponsorBalance['total_earned'] ?? null;
$pendingList   = $pendingOffers['data']['offers']  ?? $pendingOffers['offers']  ?? [];
$bidList       = $recentBids['data']['bids']       ?? $recentBids['bids']       ?? [];

// Profile completeness (simple heuristic)
$completeness = 0;
if ($hasProfile) {
    if (! empty($profile['bio']))               $completeness += 30;
    if (! empty($profile['linkedin_url']))       $completeness += 20;
    if (! empty($profile['profile_image_url'])) $completeness += 10;
    if ($degreeCount > 0)                        $completeness += 10;
    if ($certCount > 0)                          $completeness += 15;
    if ($licenseCount > 0)                       $completeness += 15;
}
$completeness = min($completeness, 100);
?>

<!-- ---- Profile Header ---- -->
<div class="chart-card mb-4">
    <div class="d-flex align-items-center gap-4 flex-wrap">

        <!-- Avatar -->
        <?php if (! empty($profile['profile_image_url'])): ?>
            <img src="<?= esc($profile['profile_image_url']) ?>"
                 class="rounded-circle flex-shrink-0"
                 width="80" height="80" style="object-fit:cover">
        <?php else: ?>
            <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center flex-shrink-0"
                 style="width:80px;height:80px">
                <i class="bi bi-person-fill text-white" style="font-size:2rem"></i>
            </div>
        <?php endif; ?>

        <!-- Info -->
        <div class="flex-grow-1">
            <h5 class="mb-0 fw-semibold"><?= esc(session()->get('user_email')) ?></h5>

            <?php if (! empty($latestJob)): ?>
                <div class="text-muted" style="font-size:.9rem">
                    <?= esc($latestJob['job_title']) ?> &mdash; <?= esc($latestJob['company_name']) ?>
                </div>
            <?php endif; ?>

            <?php if (! empty($profile['bio'])): ?>
                <div class="text-muted mt-1" style="font-size:.82rem">
                    <?= esc(mb_substr($profile['bio'], 0, 140)) ?><?= mb_strlen($profile['bio'] ?? '') > 140 ? '…' : '' ?>
                </div>
            <?php endif; ?>

            <!-- Completeness bar -->
            <div class="mt-2" style="max-width:320px">
                <div class="d-flex justify-content-between mb-1">
                    <small class="text-muted">Profile completeness</small>
                    <small class="fw-semibold"><?= $completeness ?>%</small>
                </div>
                <div class="progress" style="height:6px">
                    <div class="progress-bar <?= $completeness < 50 ? 'bg-danger' : ($completeness < 80 ? 'bg-warning' : 'bg-success') ?>"
                         style="width:<?= $completeness ?>%"></div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="d-flex flex-column gap-2 ms-auto">
            <a href="/profile" class="btn btn-primary btn-sm">
                <i class="bi bi-pencil me-1"></i> Edit Profile
            </a>
            <?php if (! empty($profile['linkedin_url'])): ?>
                <a href="<?= esc($profile['linkedin_url']) ?>" target="_blank" rel="noopener"
                   class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-linkedin me-1"></i> LinkedIn
                </a>
            <?php endif; ?>
        </div>

    </div>
</div>

<!-- ---- Stat Cards ---- -->
<div class="row g-3 mb-4">

    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#EDFAF1">
                    <i class="bi bi-patch-check-fill text-success"></i>
                </div>
                <div>
                    <div class="text-muted" style="font-size:.8rem">Certifications</div>
                    <div class="fw-bold fs-4"><?= $certCount ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#FFF8EC">
                    <i class="bi bi-award-fill text-warning"></i>
                </div>
                <div>
                    <div class="text-muted" style="font-size:.8rem">Licenses</div>
                    <div class="fw-bold fs-4"><?= $licenseCount ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#EBF3FF">
                    <i class="bi bi-currency-pound text-primary"></i>
                </div>
                <div>
                    <div class="text-muted" style="font-size:.8rem">Bid Balance</div>
                    <div class="fw-bold fs-4">
                        <?= $bidBalance !== null ? '£' . number_format((float) $bidBalance, 2) : '—' ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#F5EEFF">
                    <i class="bi bi-gift-fill" style="color:#7c3aed"></i>
                </div>
                <div>
                    <div class="text-muted" style="font-size:.8rem">Sponsorship Earned</div>
                    <div class="fw-bold fs-4">
                        <?= $sponsorTotal !== null ? '£' . number_format((float) $sponsorTotal, 2) : '—' ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- ---- Main Content Row ---- -->
<div class="row g-3 mb-4">

    <!-- Recent Bids -->
    <div class="col-lg-7">
        <div class="chart-card h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <div class="chart-title">Recent Bids</div>
                    <div class="chart-subtitle">Your last 5 feature-slot bids</div>
                </div>
                <a href="/bidding" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-plus me-1"></i> Place Bid
                </a>
            </div>

            <?php if (! empty($bidList)): ?>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bidList as $bid): ?>
                                <tr>
                                    <td><?= esc($bid['bid_date'] ?? $bid['created_at'] ?? '—') ?></td>
                                    <td>£<?= number_format((float)($bid['bid_amount'] ?? 0), 2) ?></td>
                                    <td>
                                        <?php
                                        $s = strtolower($bid['status'] ?? 'unknown');
                                        $badge = match($s) {
                                            'won'      => 'success',
                                            'active','pending' => 'primary',
                                            'cancelled' => 'secondary',
                                            'outbid'   => 'warning',
                                            default    => 'secondary',
                                        };
                                        ?>
                                        <span class="badge bg-<?= $badge ?>"><?= esc(ucfirst($s)) ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center text-muted py-4">
                    <i class="bi bi-currency-pound fs-2 d-block mb-2 opacity-25"></i>
                    No bids placed yet.
                    <a href="/bidding" class="d-block mt-2">Place your first bid</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Pending Sponsorship Offers -->
    <div class="col-lg-5">
        <div class="chart-card h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <div class="chart-title">Sponsorship Offers</div>
                    <div class="chart-subtitle">Awaiting your response</div>
                </div>
                <a href="/sponsorship/offers" class="btn btn-outline-primary btn-sm">View All</a>
            </div>

            <?php if (! $hasProfile): ?>
                <div class="alert alert-warning mb-0" style="font-size:.85rem">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    Complete your profile to receive sponsorship offers.
                </div>
            <?php elseif (! empty($pendingList)): ?>
                <div class="d-flex flex-column gap-2">
                    <?php foreach (array_slice($pendingList, 0, 4) as $offer): ?>
                        <div class="border rounded p-3" style="font-size:.88rem">
                            <div class="fw-semibold"><?= esc($offer['sponsor_name'] ?? 'Sponsor') ?></div>
                            <div class="text-muted"><?= esc($offer['description'] ?? '') ?></div>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <span class="text-success fw-semibold">
                                    £<?= number_format((float)($offer['amount'] ?? 0), 2) ?>
                                </span>
                                <a href="/sponsorship/offers" class="btn btn-sm btn-outline-success">Respond</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php if (count($pendingList) > 4): ?>
                        <a href="/sponsorship/offers" class="text-center text-muted" style="font-size:.82rem">
                            + <?= count($pendingList) - 4 ?> more offers
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="text-center text-muted py-4">
                    <i class="bi bi-gift fs-2 d-block mb-2 opacity-25"></i>
                    No pending offers right now.
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- ---- Credentials Summary ---- -->
<div class="row g-3">

    <!-- Degrees -->
    <div class="col-md-4">
        <div class="chart-card h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="chart-title"><i class="bi bi-mortarboard me-1 text-primary"></i> Degrees</div>
                <a href="/profile#degrees" class="btn btn-outline-secondary btn-sm" style="font-size:.75rem">Add</a>
            </div>
            <?php if (! empty($degrees)): ?>
                <ul class="list-unstyled mb-0">
                    <?php foreach (array_slice($degrees, 0, 3) as $d): ?>
                        <li class="mb-2 pb-2 border-bottom last-no-border">
                            <div class="fw-semibold" style="font-size:.87rem"><?= esc($d['degree_name']) ?></div>
                            <div class="text-muted" style="font-size:.78rem"><?= esc($d['completion_date']) ?></div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="text-muted mb-0" style="font-size:.85rem">No degrees added yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Certificates -->
    <div class="col-md-4">
        <div class="chart-card h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="chart-title"><i class="bi bi-patch-check me-1 text-success"></i> Certificates</div>
                <a href="/profile#certificates" class="btn btn-outline-secondary btn-sm" style="font-size:.75rem">Add</a>
            </div>
            <?php if (! empty($certificates)): ?>
                <ul class="list-unstyled mb-0">
                    <?php foreach (array_slice($certificates, 0, 3) as $c): ?>
                        <li class="mb-2 pb-2 border-bottom last-no-border">
                            <div class="fw-semibold" style="font-size:.87rem"><?= esc($c['certificate_name']) ?></div>
                            <div class="text-muted" style="font-size:.78rem"><?= esc($c['issuer_name']) ?> &bull; <?= esc($c['completion_date']) ?></div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="text-muted mb-0" style="font-size:.85rem">No certificates added yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Licenses -->
    <div class="col-md-4">
        <div class="chart-card h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="chart-title"><i class="bi bi-award me-1 text-warning"></i> Licenses</div>
                <a href="/profile#licenses" class="btn btn-outline-secondary btn-sm" style="font-size:.75rem">Add</a>
            </div>
            <?php if (! empty($licenses)): ?>
                <ul class="list-unstyled mb-0">
                    <?php foreach (array_slice($licenses, 0, 3) as $l): ?>
                        <li class="mb-2 pb-2 border-bottom last-no-border">
                            <div class="fw-semibold" style="font-size:.87rem"><?= esc($l['license_name']) ?></div>
                            <div class="text-muted" style="font-size:.78rem">
                                <?= esc($l['completion_date']) ?>
                                <?php if (! empty($l['expiration_date'])): ?>
                                    &rarr; <?= esc($l['expiration_date']) ?>
                                <?php endif; ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="text-muted mb-0" style="font-size:.85rem">No licenses added yet.</p>
            <?php endif; ?>
        </div>
    </div>

</div>

<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
.last-no-border:last-child { border-bottom: none !important; }
</style>
<?= $this->endSection() ?>
