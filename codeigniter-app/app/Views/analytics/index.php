<?= $this->extend('layouts/analytics') ?>
<?= $this->section('content') ?>

<?php
$all          = $chartData ?? [];
$programmes   = $filterOptions['programmes']      ?? [];
$years        = $filterOptions['graduation_years'] ?? [];
$active       = $activeFilters ?? [];
?>

<!-- Filter bar -->
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
        <a href="/analytics" class="btn btn-outline-secondary btn-sm">Reset</a>
    </div>
</form>

<!-- Row 1 -->
<div class="row g-3 mb-3">
    <div class="col-lg-6">
        <div class="chart-card h-100">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div><div class="chart-title">Curriculum Skills Gap</div><div class="chart-subtitle">Top certifications acquired after graduation</div></div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-secondary btn-sm btn-download-chart" onclick="downloadChart('c1','skills_gap')"><i class="bi bi-download"></i></button>
                    <a href="/analytics/skills-gap" class="btn btn-outline-primary btn-sm">Full</a>
                </div>
            </div>
            <canvas id="c1" height="160"></canvas>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="chart-card h-100">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div><div class="chart-title">Employment by Sector</div><div class="chart-subtitle">Current industry distribution</div></div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-secondary btn-sm btn-download-chart" onclick="downloadChart('c2','employment_sectors')"><i class="bi bi-download"></i></button>
                    <a href="/analytics/employment-sectors" class="btn btn-outline-primary btn-sm">Full</a>
                </div>
            </div>
            <canvas id="c2" height="160"></canvas>
        </div>
    </div>
</div>

<!-- Row 2 -->
<div class="row g-3 mb-3">
    <div class="col-lg-6">
        <div class="chart-card h-100">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div><div class="chart-title">Top Job Titles</div><div class="chart-subtitle">Most common roles held by alumni</div></div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-secondary btn-sm btn-download-chart" onclick="downloadChart('c3','job_titles')"><i class="bi bi-download"></i></button>
                    <a href="/analytics/job-titles" class="btn btn-outline-primary btn-sm">Full</a>
                </div>
            </div>
            <canvas id="c3" height="160"></canvas>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="chart-card h-100">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div><div class="chart-title">Top Employers</div><div class="chart-subtitle">Companies hiring the most alumni</div></div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-secondary btn-sm btn-download-chart" onclick="downloadChart('c4','top_employers')"><i class="bi bi-download"></i></button>
                    <a href="/analytics/top-employers" class="btn btn-outline-primary btn-sm">Full</a>
                </div>
            </div>
            <canvas id="c4" height="160"></canvas>
        </div>
    </div>
</div>

<!-- Row 3 -->
<div class="row g-3 mb-3">
    <div class="col-lg-6">
        <div class="chart-card h-100">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div><div class="chart-title">Certification Trends</div><div class="chart-subtitle">Monthly completions over 24 months</div></div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-secondary btn-sm btn-download-chart" onclick="downloadChart('c5','cert_trends')"><i class="bi bi-download"></i></button>
                    <a href="/analytics/certification-trends" class="btn btn-outline-primary btn-sm">Full</a>
                </div>
            </div>
            <canvas id="c5" height="160"></canvas>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="chart-card h-100">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div><div class="chart-title">License Distribution</div><div class="chart-subtitle">Types of professional licenses held</div></div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-secondary btn-sm btn-download-chart" onclick="downloadChart('c6','license_dist')"><i class="bi bi-download"></i></button>
                    <a href="/analytics/license-distribution" class="btn btn-outline-primary btn-sm">Full</a>
                </div>
            </div>
            <canvas id="c6" height="160"></canvas>
        </div>
    </div>
</div>

<!-- Row 4 -->
<div class="row g-3">
    <div class="col-lg-6">
        <div class="chart-card h-100">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div><div class="chart-title">Career Pathways</div><div class="chart-subtitle">Degree programme to career outcomes</div></div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-secondary btn-sm btn-download-chart" onclick="downloadChart('c7','career_pathways')"><i class="bi bi-download"></i></button>
                    <a href="/analytics/career-pathways" class="btn btn-outline-primary btn-sm">Full</a>
                </div>
            </div>
            <canvas id="c7" height="160"></canvas>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="chart-card h-100">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div><div class="chart-title">Graduation Outcomes</div><div class="chart-subtitle">Employment & certification by graduation year</div></div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-secondary btn-sm btn-download-chart" onclick="downloadChart('c8','grad_outcomes')"><i class="bi bi-download"></i></button>
                    <a href="/analytics/graduation-outcomes" class="btn btn-outline-primary btn-sm">Full</a>
                </div>
            </div>
            <canvas id="c8" height="160"></canvas>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
const COLOURS = ['#4A90D9','#36b37e','#ff5630','#ffab00','#6554c0','#00b8d9','#ff991f','#57d9a3','#b3bac5','#403294','#0052cc','#00875a'];
const ALL = <?= json_encode($chartData ?? []) ?>;

function gapColour(p) { return p >= 70 ? '#dc3545' : p >= 40 ? '#fd7e14' : p >= 20 ? '#ffc107' : '#4A90D9'; }

// 1 Skills gap bar
(function(){
    const d = ALL.skills_gap || ALL['skills-gap'] || {};
    if (!d.labels) return;
    new Chart(document.getElementById('c1'), { type:'bar', data:{ labels:d.labels, datasets:[{ label:'Alumni', data:d.data, backgroundColor:(d.penetration||[]).map(gapColour), borderRadius:4 }] }, options:{ responsive:true, plugins:{ legend:{display:false} }, scales:{y:{beginAtZero:true}} } });
})();

// 2 Employment sectors doughnut
(function(){
    const d = ALL.employment_sectors || ALL['employment-sectors'] || {};
    if (!d.labels) return;
    new Chart(document.getElementById('c2'), { type:'doughnut', data:{ labels:d.labels, datasets:[{ data:d.data, backgroundColor:COLOURS, borderWidth:2 }] }, options:{ responsive:true, plugins:{ legend:{position:'right', labels:{font:{size:10}}} } } });
})();

// 3 Job titles horizontal bar
(function(){
    const d = ALL.job_titles || ALL['job-titles'] || {};
    if (!d.labels) return;
    new Chart(document.getElementById('c3'), { type:'bar', data:{ labels:d.labels, datasets:[{ label:'Alumni', data:d.data, backgroundColor:'#4A90D9', borderRadius:4 }] }, options:{ indexAxis:'y', responsive:true, plugins:{ legend:{display:false} }, scales:{x:{beginAtZero:true}} } });
})();

// 4 Top employers bar
(function(){
    const d = ALL.top_employers || ALL['top-employers'] || {};
    if (!d.labels) return;
    new Chart(document.getElementById('c4'), { type:'bar', data:{ labels:d.labels, datasets:[{ label:'Alumni', data:d.data, backgroundColor:'#36b37e', borderRadius:4 }] }, options:{ responsive:true, plugins:{ legend:{display:false} }, scales:{y:{beginAtZero:true}} } });
})();

// 5 Certification trends line
(function(){
    const d = ALL.certification_trends || ALL['certification-trends'] || {};
    if (!d.labels) return;
    new Chart(document.getElementById('c5'), { type:'line', data:{ labels:d.labels, datasets:[{ label:'Certifications', data:d.data, borderColor:'#4A90D9', backgroundColor:'rgba(74,144,217,0.1)', fill:true, tension:0.4, pointRadius:2 }] }, options:{ responsive:true, plugins:{ legend:{display:false} }, scales:{y:{beginAtZero:true}} } });
})();

// 6 License distribution polar area
(function(){
    const d = ALL.license_distribution || ALL['license-distribution'] || {};
    if (!d.labels) return;
    new Chart(document.getElementById('c6'), { type:'polarArea', data:{ labels:d.labels, datasets:[{ data:d.data, backgroundColor:COLOURS.map(c=>c+'99') }] }, options:{ responsive:true, plugins:{ legend:{position:'right', labels:{font:{size:10}}} } } });
})();

// 7 Career pathways grouped bar
(function(){
    const d = ALL.career_pathways || ALL['career-pathways'] || {};
    if (!d.labels) return;
    const datasets = (d.datasets||[]).map((ds,i)=>({ ...ds, backgroundColor:COLOURS[i%COLOURS.length], borderRadius:3 }));
    new Chart(document.getElementById('c7'), { type:'bar', data:{ labels:d.labels, datasets }, options:{ responsive:true, plugins:{ legend:{position:'bottom', labels:{font:{size:9}}} }, scales:{x:{stacked:false}, y:{beginAtZero:true}} } });
})();

// 8 Graduation outcomes stacked bar
(function(){
    const d = ALL.graduation_outcomes || ALL['graduation-outcomes'] || {};
    if (!d.labels) return;
    const colours = ['#4A90D9','#36b37e','#ffab00'];
    const datasets = (d.datasets||[]).map((ds,i)=>({ ...ds, backgroundColor:colours[i]||COLOURS[i], borderRadius:3 }));
    new Chart(document.getElementById('c8'), { type:'bar', data:{ labels:d.labels, datasets }, options:{ responsive:true, plugins:{ legend:{position:'bottom', labels:{font:{size:9}}} }, scales:{ x:{stacked:true}, y:{stacked:true, beginAtZero:true} } } });
})();
</script>
<?= $this->endSection() ?>
