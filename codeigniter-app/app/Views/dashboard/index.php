<?= $this->extend('layouts/analytics') ?>

<?= $this->section('content') ?>

<!-- ---- Summary Cards ---- -->
<div class="row g-3 mb-4">

    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#EBF3FF">
                    <i class="bi bi-people-fill text-primary"></i>
                </div>
                <div>
                    <div class="text-muted" style="font-size:.8rem">Total Alumni</div>
                    <div class="fw-bold fs-4"><?= number_format($summary['total_alumni'] ?? 0) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#EDFAF1">
                    <i class="bi bi-patch-check-fill text-success"></i>
                </div>
                <div>
                    <div class="text-muted" style="font-size:.8rem">Certifications</div>
                    <div class="fw-bold fs-4"><?= number_format($summary['total_certifications'] ?? 0) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#FFF8EC">
                    <i class="bi bi-award-fill text-warning"></i>
                </div>
                <div>
                    <div class="text-muted" style="font-size:.8rem">Licenses</div>
                    <div class="fw-bold fs-4"><?= number_format($summary['total_licenses'] ?? 0) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#F5EEFF">
                    <i class="bi bi-mortarboard-fill" style="color:#7c3aed"></i>
                </div>
                <div>
                    <div class="text-muted" style="font-size:.8rem">Top Programme</div>
                    <div class="fw-semibold" style="font-size:.95rem"><?= esc($summary['top_programme'] ?? 'N/A') ?></div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- ---- Mini Charts Row ---- -->
<div class="row g-3 mb-4">

    <!-- Skills Gap mini bar -->
    <div class="col-lg-12">
        <div class="chart-card">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <div class="chart-title">Curriculum Skills Gap</div>
                    <div class="chart-subtitle">Top certifications acquired after graduation</div>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-secondary btn-sm btn-download-chart"
                            onclick="downloadChart('dashSkillsChart','skills_gap')">
                        <i class="bi bi-download"></i>
                    </button>
                    <a href="/analytics/skills-gap" class="btn btn-outline-primary btn-sm">View Full</a>
                </div>
            </div>
            <canvas id="dashSkillsChart" height="120"></canvas>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-12">
        <div class="chart-card">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <div class="chart-title">Employment Sectors</div>
                    <div class="chart-subtitle">Current sector distribution</div>
                </div>
                <a href="/analytics/employment-sectors" class="btn btn-outline-primary btn-sm">View Full</a>
            </div>
            <canvas id="dashSectorsChart" height="150"></canvas>
        </div>
    </div>
</div>

<!-- ---- Cert Trend + Alumni of the Day ---- -->
<div class="row g-3">

    <!-- Certification Trend line -->
    <div class="col-lg-12">
        <div class="chart-card">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <div class="chart-title">Certification Trends (24 months)</div>
                    <div class="chart-subtitle">Monthly certifications completed by alumni</div>
                </div>
                <button class="btn btn-outline-secondary btn-sm btn-download-chart"
                        onclick="downloadChart('dashTrendChart','cert_trends')">
                    <i class="bi bi-download"></i>
                </button>
            </div>
            <canvas id="dashTrendChart" height="120"></canvas>
        </div>
    </div>
    </div>

</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
const SECTOR_COLOURS = [
    '#4A90D9','#36b37e','#ff5630','#ffab00',
    '#6554c0','#00b8d9','#ff991f','#57d9a3'
];

// ---- Skills Gap mini bar ----
fetch('/analytics/skills-gap-json?limit=6')
    .then(r => r.json())
    .then(({ data }) => {
        if (!data) return;
        const colours = (data.penetration || []).map(p =>
            p >= 70 ? '#dc3545' : p >= 40 ? '#fd7e14' : p >= 20 ? '#ffc107' : '#4A90D9'
        );
        new Chart(document.getElementById('dashSkillsChart'), {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [{ label: 'Alumni Count', data: data.data, backgroundColor: colours, borderRadius: 6 }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            afterLabel: ctx => {
                                const p = data.penetration?.[ctx.dataIndex];
                                return p != null ? `Penetration: ${p}%` : '';
                            }
                        }
                    }
                },
                scales: { y: { beginAtZero: true } }
            }
        });
    })
    .catch(() => {});

// ---- Employment Sectors mini doughnut ----
fetch('/analytics/employment-sectors-json')
    .then(r => r.json())
    .then(({ data }) => {
        if (!data) return;
        new Chart(document.getElementById('dashSectorsChart'), {
            type: 'doughnut',
            data: {
                labels: data.labels,
                datasets: [{ data: data.data, backgroundColor: SECTOR_COLOURS, borderWidth: 2 }]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom', labels: { font: { size: 10 } } } }
            }
        });
    })
    .catch(() => {});

// ---- Certification Trend line ----
fetch('/analytics/certification-trends-json')
    .then(r => r.json())
    .then(({ data }) => {
        if (!data) return;
        new Chart(document.getElementById('dashTrendChart'), {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Certifications',
                    data: data.data,
                    borderColor: '#4A90D9',
                    backgroundColor: 'rgba(74,144,217,0.1)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });
    })
    .catch(() => {});
</script>
<?= $this->endSection() ?>
