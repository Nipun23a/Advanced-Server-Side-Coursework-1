<?= $this->extend('layouts/analytics') ?>
<?= $this->section('content') ?>

<?php
$programmes = $filterOptions['programmes'] ?? [];
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
        <label class="form-label mb-1" style="font-size:.8rem">Months</label>
        <select name="months" class="form-select form-select-sm">
            <?php foreach ([6,12,24,36] as $m): ?><option value="<?= $m ?>" <?= ($active['months'] ?? 24) == $m ? 'selected' : '' ?>><?= $m ?> months</option><?php endforeach; ?>
        </select>
    </div>
    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary btn-sm">Apply</button>
        <a href="/analytics/certification-trends" class="btn btn-outline-secondary btn-sm">Reset</a>
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="downloadChart('trendChart','cert_trends')"><i class="bi bi-download"></i> PNG</button>
    </div>
</form>

<div class="chart-card mb-3">
    <div class="chart-title mb-1">Certification Trends Over Time</div>
    <div class="chart-subtitle">Monthly certification completions — rising trend signals growing industry demand</div>
    <canvas id="trendChart" height="100"></canvas>
</div>

<div class="chart-card">
    <div class="chart-title mb-3">Monthly Breakdown</div>
    <div class="table-responsive">
        <table class="table table-sm table-hover mb-0">
            <thead class="table-light"><tr><th>Month</th><th class="text-end">Certifications</th><th class="text-end">vs Previous</th></tr></thead>
            <tbody id="trendTable"></tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
const d = <?= $chartData ?>;
const labels = d.labels || [];
const data   = d.data   || [];

new Chart(document.getElementById('trendChart'), {
    type: 'line',
    data: { labels, datasets: [{
        label: 'Certifications',
        data,
        borderColor: '#4A90D9',
        backgroundColor: 'rgba(74,144,217,0.12)',
        fill: true,
        tension: 0.4,
        pointRadius: 4,
        pointHoverRadius: 6,
        pointBackgroundColor: '#4A90D9',
    }]},
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: { mode: 'index', intersect: false }
        },
        scales: {
            y: { beginAtZero: true, title: { display: true, text: 'Certifications' } },
            x: { title: { display: true, text: 'Month' } }
        }
    }
});

const tbody = document.getElementById('trendTable');
labels.forEach((label, i) => {
    const prev  = i > 0 ? data[i-1] : null;
    const diff  = prev !== null ? data[i] - prev : null;
    const delta = diff === null ? '—'
        : diff > 0 ? `<span class="text-success">▲ ${diff}</span>`
        : diff < 0 ? `<span class="text-danger">▼ ${Math.abs(diff)}</span>`
        : '<span class="text-muted">—</span>';
    tbody.innerHTML += `<tr><td>${label}</td><td class="text-end fw-semibold">${data[i]}</td><td class="text-end">${delta}</td></tr>`;
});
</script>
<?= $this->endSection() ?>
