<?= $this->extend('layouts/analytics') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-end mb-3">
    <button class="btn btn-outline-secondary btn-sm" onclick="downloadChart('outcomeChart','graduation_outcomes')"><i class="bi bi-download"></i> PNG</button>
</div>

<div class="chart-card mb-3">
    <div class="chart-title mb-1">Graduation Outcomes Over Time</div>
    <div class="chart-subtitle">Total alumni, employment, and certifications by graduation year</div>
    <canvas id="outcomeChart" height="120"></canvas>
</div>

<div class="chart-card">
    <div class="chart-title mb-3">Year-by-Year Breakdown</div>
    <div class="table-responsive">
        <table class="table table-sm table-bordered mb-0">
            <thead class="table-light" id="outcomeHead"></thead>
            <tbody id="outcomeBody"></tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
const d        = <?= $chartData ?>;
const labels   = d.labels   || [];
const colours  = ['#4A90D9', '#36b37e', '#ffab00', '#6554c0'];
const datasets = (d.datasets || []).map((ds, i) => ({
    ...ds,
    backgroundColor: colours[i] || '#4A90D9',
    borderRadius: 4,
}));

new Chart(document.getElementById('outcomeChart'), {
    type: 'bar',
    data: { labels, datasets },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom', labels: { padding: 14, font: { size: 12 } } } },
        scales: {
            x: { stacked: true, title: { display: true, text: 'Graduation Year' } },
            y: { stacked: true, beginAtZero: true, title: { display: true, text: 'Alumni Count' } }
        }
    }
});

// Table
const thead = document.getElementById('outcomeHead');
const tbody = document.getElementById('outcomeBody');
let hrow = '<tr><th>Year</th>';
datasets.forEach(ds => { hrow += `<th class="text-end">${ds.label}</th>`; });
hrow += '</tr>';
thead.innerHTML = hrow;

labels.forEach((year, i) => {
    let row = `<td class="fw-semibold">${year}</td>`;
    datasets.forEach(ds => { row += `<td class="text-end">${ds.data[i] ?? 0}</td>`; });
    tbody.innerHTML += `<tr>${row}</tr>`;
});
</script>
<?= $this->endSection() ?>
