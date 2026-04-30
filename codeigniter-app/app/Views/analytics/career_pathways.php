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
    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary btn-sm">Apply</button>
        <a href="/analytics/career-pathways" class="btn btn-outline-secondary btn-sm">Reset</a>
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="downloadChart('pathChart','career_pathways')"><i class="bi bi-download"></i> PNG</button>
    </div>
</form>

<div class="chart-card mb-3">
    <div class="chart-title mb-1">Career Pathways</div>
    <div class="chart-subtitle">Job outcomes by degree programme — which careers each course leads to</div>
    <canvas id="pathChart" height="120"></canvas>
</div>

<div class="chart-card">
    <div class="chart-title mb-3">Pathway Detail</div>
    <div class="table-responsive">
        <table class="table table-sm table-bordered mb-0" id="pathTable">
            <thead class="table-light" id="pathHead"></thead>
            <tbody id="pathBody"></tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
const COLOURS = ['#4A90D9','#36b37e','#ff5630','#ffab00','#6554c0','#00b8d9','#ff991f','#57d9a3'];
const d        = <?= $chartData ?>;
const labels   = d.labels   || [];
const datasets = (d.datasets || []).map((ds, i) => ({
    ...ds,
    backgroundColor: COLOURS[i % COLOURS.length],
    borderRadius: 4,
}));

new Chart(document.getElementById('pathChart'), {
    type: 'bar',
    data: { labels, datasets },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom', labels: { padding: 14, font: { size: 11 } } } },
        scales: { x: { title: { display: true, text: 'Job Title' } }, y: { beginAtZero: true, title: { display: true, text: 'Alumni Count' } } }
    }
});

// Table: columns = programmes (datasets), rows = job titles (labels)
const thead = document.getElementById('pathHead');
const tbody = document.getElementById('pathBody');
let hrow = '<tr><th>Job Title</th>';
datasets.forEach(ds => { hrow += `<th class="text-end">${ds.label}</th>`; });
hrow += '</tr>';
thead.innerHTML = hrow;

labels.forEach((label, i) => {
    let row = `<td>${label}</td>`;
    datasets.forEach(ds => { row += `<td class="text-end">${ds.data[i] ?? 0}</td>`; });
    tbody.innerHTML += `<tr>${row}</tr>`;
});
</script>
<?= $this->endSection() ?>
