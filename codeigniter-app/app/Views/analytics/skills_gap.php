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
            <?php foreach ($programmes as $p): ?>
                <option value="<?= esc($p) ?>" <?= ($active['programme'] ?? '') === $p ? 'selected' : '' ?>><?= esc($p) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label class="form-label mb-1" style="font-size:.8rem">Graduation Year</label>
        <select name="graduationYear" class="form-select form-select-sm">
            <option value="">All Years</option>
            <?php foreach ($years as $y): ?>
                <option value="<?= esc($y) ?>" <?= ($active['graduationYear'] ?? '') == $y ? 'selected' : '' ?>><?= esc($y) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary btn-sm">Apply</button>
        <a href="/analytics/skills-gap" class="btn btn-outline-secondary btn-sm">Reset</a>
    </div>
</form>

<!-- Legend -->
<div class="d-flex gap-3 mb-3 flex-wrap">
    <span class="badge gap-critical px-3 py-2"><i class="bi bi-exclamation-triangle me-1"></i> Critical ≥70%</span>
    <span class="badge gap-significant px-3 py-2"><i class="bi bi-exclamation-circle me-1"></i> Significant ≥40%</span>
    <span class="badge gap-emerging px-3 py-2"><i class="bi bi-info-circle me-1"></i> Emerging ≥20%</span>
    <span class="badge bg-primary px-3 py-2">Low &lt;20%</span>
    <div class="ms-auto d-flex gap-2">
        <button class="btn btn-outline-secondary btn-sm" onclick="toggleView('bar')" id="btnBar">Bar Chart</button>
        <button class="btn btn-outline-secondary btn-sm" onclick="toggleView('radar')" id="btnRadar">Radar Chart</button>
        <button class="btn btn-outline-secondary btn-sm btn-download-chart" onclick="downloadActive()"><i class="bi bi-download"></i> PNG</button>
    </div>
</div>

<!-- Charts -->
<div class="row g-3 mb-4">
    <div class="col-12" id="barView">
        <div class="chart-card">
            <canvas id="barChart" height="80"></canvas>
        </div>
    </div>
    <div class="col-12 d-none" id="radarView">
        <div class="chart-card">
            <canvas id="radarChart" height="80"></canvas>
        </div>
    </div>
</div>

<!-- Data table -->
<div class="chart-card">
    <div class="chart-title mb-3">Skills Gap Detail Table</div>
    <div class="table-responsive">
        <table class="table table-sm table-hover mb-0" id="gapTable">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Certification / Skill</th>
                    <th class="text-end">Alumni Count</th>
                    <th class="text-end">Penetration</th>
                    <th>Severity</th>
                </tr>
            </thead>
            <tbody id="gapTableBody"></tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
const chartData = <?= $chartData ?>;
const labels      = chartData.labels      || [];
const counts      = chartData.data        || [];
const penetration = chartData.penetration || [];

function gapColour(p) { return p >= 70 ? '#dc3545' : p >= 40 ? '#fd7e14' : p >= 20 ? '#ffc107' : '#4A90D9'; }
function gapBadge(p)  {
    if (p >= 70) return '<span class="badge gap-critical">Critical</span>';
    if (p >= 40) return '<span class="badge gap-significant">Significant</span>';
    if (p >= 20) return '<span class="badge gap-emerging">Emerging</span>';
    return '<span class="badge bg-primary">Low</span>';
}

// Bar chart
const barChart = new Chart(document.getElementById('barChart'), {
    type: 'bar',
    data: {
        labels,
        datasets: [{
            label: 'Alumni Count',
            data: counts,
            backgroundColor: penetration.map(gapColour),
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    afterLabel: ctx => {
                        const p = penetration[ctx.dataIndex];
                        return p != null ? `Penetration: ${p}%` : '';
                    }
                }
            }
        },
        scales: { y: { beginAtZero: true, title: { display: true, text: 'Alumni Count' } } }
    }
});

// Radar chart
const radarChart = new Chart(document.getElementById('radarChart'), {
    type: 'radar',
    data: {
        labels,
        datasets: [{
            label: 'Alumni Count',
            data: counts,
            borderColor: '#4A90D9',
            backgroundColor: 'rgba(74,144,217,0.2)',
            pointBackgroundColor: penetration.map(gapColour),
            pointRadius: 5,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { r: { beginAtZero: true } }
    }
});

// Table
const tbody = document.getElementById('gapTableBody');
labels.forEach((label, i) => {
    const p = penetration[i] ?? 0;
    tbody.innerHTML += `<tr>
        <td class="text-muted">${i+1}</td>
        <td>${label}</td>
        <td class="text-end fw-semibold">${counts[i] ?? 0}</td>
        <td class="text-end">${p}%</td>
        <td>${gapBadge(p)}</td>
    </tr>`;
});

// Toggle
let activeView = 'bar';
function toggleView(v) {
    activeView = v;
    document.getElementById('barView').classList.toggle('d-none', v !== 'bar');
    document.getElementById('radarView').classList.toggle('d-none', v !== 'radar');
    document.getElementById('btnBar').classList.toggle('btn-primary', v === 'bar');
    document.getElementById('btnBar').classList.toggle('btn-outline-secondary', v !== 'bar');
    document.getElementById('btnRadar').classList.toggle('btn-primary', v === 'radar');
    document.getElementById('btnRadar').classList.toggle('btn-outline-secondary', v !== 'radar');
}
function downloadActive() {
    const id = activeView === 'bar' ? 'barChart' : 'radarChart';
    downloadChart(id, 'skills_gap');
}
toggleView('bar');
</script>
<?= $this->endSection() ?>
