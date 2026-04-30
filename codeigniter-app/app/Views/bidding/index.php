<?= $this->extend('layouts/analytics') ?>

<?= $this->section('content') ?>

<div class="container mt-4 mb-5">
    <h2 class="mb-3">Bidding</h2>

    <div class="card mb-4">
        <div class="card-body">
            <h4 class="card-title">Place Bid</h4>
            <form method="post" action="/bidding/place">
                <?= csrf_field() ?>
                <div class="row g-2">
                    <div class="col-md-4">
                        <label class="form-label">Bid Date</label>
                        <input class="form-control" type="date" name="bid_date" value="<?= esc($tomorrow) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Bid Amount</label>
                        <input class="form-control" type="number" step="0.01" min="0.01" name="bid_amount">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button class="btn btn-primary" type="submit">Place Bid</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-body">
                    <h5>Available Balance</h5>
                    <p><?= esc($balance['data']['available_balance'] ?? 'N/A') ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-body">
                    <h5>Monthly Limit</h5>
                    <p>Featured Count: <?= esc($monthlyLimit['data']['featured_count'] ?? 'N/A') ?></p>
                    <p>Max Allowed: <?= esc($monthlyLimit['data']['max_allowed'] ?? 'N/A') ?></p>
                    <p>Remaining: <?= esc($monthlyLimit['data']['remaining'] ?? 'N/A') ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-body">
                    <h5>Current Status</h5>
                    <?php if (!empty($bidStatus['success'])): ?>
                        <p>Bid Date: <?= esc($bidStatus['data']['bid_date'] ?? '') ?></p>
                        <p>Your Bid: <?= esc($bidStatus['data']['your_bid_amount'] ?? '') ?></p>
                        <p>Winning: <?= !empty($bidStatus['data']['is_winning']) ? 'Yes' : 'No' ?></p>
                    <?php else: ?>
                        <p><?= esc($bidStatus['error']['message'] ?? 'No active bid found.') ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h4 class="card-title">Bid History</h4>
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Cancelled</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach (($bidHistory['data']['bids'] ?? []) as $bid): ?>
                    <tr>
                        <td><?= esc($bid['id']) ?></td>
                        <td><?= esc($bid['bid_amount']) ?></td>
                        <td><?= esc($bid['bid_status']) ?></td>
                        <td><?= esc($bid['bid_date']) ?></td>
                        <td><?= !empty($bid['is_cancelled']) ? 'Yes' : 'No' ?></td>
                        <td>
                            <?php if (($bid['bid_status'] ?? '') === 'active' && empty($bid['is_cancelled'])): ?>
                                <form method="post" action="/bidding/update/<?= $bid['id'] ?>" class="d-inline">
                                    <?= csrf_field() ?>
                                    <input type="number" step="0.01" min="0.01" name="bid_amount" placeholder="New amount">
                                    <button class="btn btn-sm btn-outline-primary" type="submit">Update</button>
                                </form>
                                <form method="post" action="/bidding/cancel/<?= $bid['id'] ?>" class="d-inline">
                                    <?= csrf_field() ?>
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Cancel</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($bidHistory['data']['bids'] ?? [])): ?>
                    <tr><td colspan="6">No bids found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
