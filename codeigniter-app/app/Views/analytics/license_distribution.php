<?= $this->extend('layouts/analytics') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-end mb-3">
    <button class="btn btn-outline-secondary btn-sm" onclick="downloadChart('licenseChart','license_distribution')"><i class="bi bi-download"></i> PNG</button>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="chart-card">
            <div class="chart-title mb-1">Professional License Distribution</div>
            <div class="chart-subtitle">Types of licenses held by alumni</div>
            <canvas id="licenseChart" height="320"></canvas>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="chart-card h-100">
            <div class="chart-title mb-3">License Breakdown</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>License Type</th><th class="text-end">Count</th><th class="text-end">Share</th></tr></thead>
                    <tbody id="licenseTable"></tbody>
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
const total  = data.reduce((s, v) => s + v, 0);

new Chart(document.getElementById('licenseChart'), {
    type: 'polarArea',
    data: { labels, datasets: [{ data, backgroundColor: COLOURS.map(c => c + 'cc'), borderColor: COLOURS, borderWidth: 1 }] },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom', labels: { padding: 14, font: { size: 12 } } },
            tooltip: { callbacks: { label: ctx => ` ${ctx.label}: ${ctx.parsed.r} (${total ? ((ctx.parsed.r/total)*100).toFixed(1) : 0}%)` } }
        }
    }
});

const tbody = document.getElementById('licenseTable');
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
