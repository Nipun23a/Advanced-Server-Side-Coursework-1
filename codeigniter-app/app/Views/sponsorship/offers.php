<?= $this->extend('layouts/analytics') ?>

<?= $this->section('content') ?>

<div class="container mt-4 mb-5">
    <h2 class="mb-3">Sponsorship Offers</h2>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-body">
                    <h5>Balance</h5>
                    <p>Available Balance: <?= esc($balance['data']['available_balance'] ?? 'N/A') ?></p>
                    <p>Total Accepted: <?= esc($balance['data']['total_accepted'] ?? 'N/A') ?></p>
                    <p>Total Paid: <?= esc($balance['data']['total_paid'] ?? 'N/A') ?></p>
                    <p>Total Paid Amount: <?= esc($balance['data']['total_paid_amount'] ?? 'N/A') ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-body">
                    <h5>Summary</h5>
                    <p>Pending: <?= esc($summary['data']['summary']['pending'] ?? 0) ?></p>
                    <p>Accepted: <?= esc($summary['data']['summary']['accepted'] ?? 0) ?></p>
                    <p>Declined: <?= esc($summary['data']['summary']['declined'] ?? 0) ?></p>
                    <p>Paid: <?= esc($summary['data']['summary']['paid'] ?? 0) ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h4 class="card-title">Offers</h4>
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Sponsor</th>
                        <th>Credential</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach (($offers['data']['offers'] ?? []) as $offer): ?>
                    <tr>
                        <td><?= esc($offer['id']) ?></td>
                        <td><?= esc($offer['sponsor_name'] ?? '') ?></td>
                        <td><?= esc($offer['credential_name'] ?? '') ?></td>
                        <td><?= esc($offer['sponsorable_type'] ?? '') ?></td>
                        <td><?= esc($offer['offer_amount'] ?? '') ?></td>
                        <td><?= esc($offer['status'] ?? '') ?></td>
                        <td>
                            <?php if (($offer['status'] ?? '') === 'pending'): ?>
                                <form method="post" action="/sponsorship/offers/respond/<?= $offer['id'] ?>" class="d-inline">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="accept">
                                    <button class="btn btn-sm btn-outline-success" type="submit">Accept</button>
                                </form>
                                <form method="post" action="/sponsorship/offers/respond/<?= $offer['id'] ?>" class="d-inline">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="decline">
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Decline</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($offers['data']['offers'] ?? [])): ?>
                    <tr><td colspan="7">No sponsorship offers found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
