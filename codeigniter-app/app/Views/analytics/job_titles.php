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
        <a href="/analytics/job-titles" class="btn btn-outline-secondary btn-sm">Reset</a>
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="downloadChart('titleChart','job_titles')"><i class="bi bi-download"></i> PNG</button>
    </div>
</form>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="chart-card">
            <div class="chart-title mb-1">Most Common Job Titles</div>
            <div class="chart-subtitle">Top roles currently held by alumni</div>
            <canvas id="titleChart" height="350"></canvas>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="chart-card h-100">
            <div class="chart-title mb-3">Rankings</div>
            <ol class="mb-0 ps-3" id="titleList" style="font-size:.88rem"></ol>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
const d = <?= $chartData ?>;
const labels = d.labels || [];
const data   = d.data   || [];

new Chart(document.getElementById('titleChart'), {
    type: 'bar',
    data: { labels, datasets: [{ label: 'Alumni', data, backgroundColor: '#4A90D9', borderRadius: 5 }] },
    options: {
        indexAxis: 'y',
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { x: { beginAtZero: true, title: { display: true, text: 'Number of Alumni' } } }
    }
});

const list = document.getElementById('titleList');
labels.forEach((label, i) => {
    list.innerHTML += `<li class="mb-2"><span class="fw-semibold">${label}</span> <span class="text-muted">(${data[i]})</span></li>`;
});
</script>
<?= $this->endSection() ?>
