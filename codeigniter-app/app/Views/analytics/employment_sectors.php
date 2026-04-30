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
    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary btn-sm">Apply</button>
        <a href="/analytics/employment-sectors" class="btn btn-outline-secondary btn-sm">Reset</a>
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="downloadChart('sectorChart','employment_sectors')"><i class="bi bi-download"></i> PNG</button>
    </div>
</form>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="chart-card">
            <div class="chart-title mb-1">Employment by Industry Sector</div>
            <div class="chart-subtitle">Distribution of alumni across industry sectors</div>
            <canvas id="sectorChart" height="300"></canvas>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="chart-card h-100">
            <div class="chart-title mb-3">Sector Breakdown</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>Sector</th><th class="text-end">Alumni</th><th class="text-end">Share</th></tr></thead>
                    <tbody id="sectorTable"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
const COLOURS = ['#4A90D9','#36b37e','#ff5630','#ffab00','#6554c0','#00b8d9','#ff991f','#57d9a3','#b3bac5','#403294'];
const d = <?= $chartData ?>;
const labels = d.labels || [];
const data   = d.data   || [];
const total  = data.reduce((s,v) => s+v, 0);

new Chart(document.getElementById('sectorChart'), {
    type: 'doughnut',
    data: { labels, datasets: [{ data, backgroundColor: COLOURS, borderWidth: 2, hoverOffset: 8 }] },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom', labels: { padding: 16, font: { size: 12 } } },
            tooltip: { callbacks: { label: ctx => ` ${ctx.label}: ${ctx.parsed} (${total ? ((ctx.parsed/total)*100).toFixed(1) : 0}%)` } }
        }
    }
});

const tbody = document.getElementById('sectorTable');
labels.forEach((label, i) => {
    const pct = total ? ((data[i]/total)*100).toFixed(1) : 0;
    tbody.innerHTML += `<tr>
        <td><span class="d-inline-block rounded me-2" style="width:12px;height:12px;background:${COLOURS[i%COLOURS.length]}"></span>${label}</td>
        <td class="text-end fw-semibold">${data[i]}</td>
        <td class="text-end text-muted">${pct}%</td>
    </tr>`;
});
</script>
<?= $this->endSection() ?>
