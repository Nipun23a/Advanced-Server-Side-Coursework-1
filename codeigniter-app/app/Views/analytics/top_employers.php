<?= $this->extend('layouts/analytics') ?>
<?= $this->section('content') ?>

<?php
$programmes = $filterOptions['programmes']      ?? [];
$years      = $filterOptions['graduation_years'] ?? [];
$active     = $activeFilters ?? [];
?>

<form method="get" class="filter-bar d-flex flex-wrap gap-3 align-items-end mb-4">
    <div>
        <label class="form-label mb-1" style="font-size:.8rem">Programme</label>
        <select name="programme" class="form-select form-select-sm" style="min-width:180px">
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
        <label class="form-label mb-1" style="font-size:.8rem">Show top</label>
        <select name="limit" class="form-select form-select-sm">
            <?php foreach ([5,10,15,20] as $l): ?><option value="<?= $l ?>" <?= ($active['limit'] ?? 10) == $l ? 'selected' : '' ?>><?= $l ?></option><?php endforeach; ?>
        </select>
    </div>
    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary btn-sm">Apply</button>
        <a href="/analytics/top-employers" class="btn btn-outline-secondary btn-sm">Reset</a>
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="downloadChart('empChart','top_employers')"><i class="bi bi-download"></i> PNG</button>
    </div>
</form>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="chart-card">
            <div class="chart-title mb-1">Top Employers</div>
            <div class="chart-subtitle">Companies employing the most alumni</div>
            <canvas id="empChart" height="300"></canvas>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="chart-card h-100">
            <div class="chart-title mb-3">Employer Ranking</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>#</th><th>Company</th><th class="text-end">Alumni</th></tr></thead>
                    <tbody id="empTable"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
const d = <?= $chartData ?>;
const labels = d.labels || [];
const data   = d.data   || [];
const colours = labels.map((_, i) => `hsl(${210 + i*8},70%,${55 - i*2}%)`);

new Chart(document.getElementById('empChart'), {
    type: 'bar',
    data: { labels, datasets: [{ label: 'Alumni', data, backgroundColor: colours, borderRadius: 5 }] },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, title: { display: true, text: 'Number of Alumni' } } }
    }
});

const tbody = document.getElementById('empTable');
labels.forEach((label, i) => {
    tbody.innerHTML += `<tr><td class="text-muted">${i+1}</td><td>${label}</td><td class="text-end fw-semibold">${data[i]}</td></tr>`;
});
</script>
<?= $this->endSection() ?>
