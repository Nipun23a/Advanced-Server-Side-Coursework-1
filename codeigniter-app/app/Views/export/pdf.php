<?= $this->extend('layouts/analytics') ?>
<?= $this->section('content') ?>

<?php
$all      = json_decode($chartData ?? '{}', true) ?? [];
$summary  = $summary ?? [];
$active   = $activeFilters ?? [];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <p class="text-muted mb-0" style="font-size:.875rem">
            <?= ! empty($active['programme']) ? 'Programme: <strong>' . esc($active['programme']) . '</strong>' : 'All Programmes' ?>
            <?= ! empty($active['graduationYear']) ? ' · Year: <strong>' . esc($active['graduationYear']) . '</strong>' : '' ?>
        </p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-danger" onclick="generatePdf()"><i class="bi bi-file-pdf me-1"></i> Download PDF</button>
        <a href="/export" class="btn btn-outline-secondary">Back</a>
    </div>
</div>

<!-- Report content — captured for PDF -->
<div id="reportContent">

    <!-- Summary stats -->
    <div class="row g-3 mb-4">
        <div class="col-sm-3">
            <div class="card stat-card h-100"><div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#EBF3FF"><i class="bi bi-people-fill text-primary"></i></div>
                <div><div class="text-muted" style="font-size:.8rem">Total Alumni</div><div class="fw-bold fs-4"><?= number_format($summary['total_alumni'] ?? 0) ?></div></div>
            </div></div>
        </div>
        <div class="col-sm-3">
            <div class="card stat-card h-100"><div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#EDFAF1"><i class="bi bi-patch-check-fill text-success"></i></div>
                <div><div class="text-muted" style="font-size:.8rem">Certifications</div><div class="fw-bold fs-4"><?= number_format($summary['total_certifications'] ?? 0) ?></div></div>
            </div></div>
        </div>
        <div class="col-sm-3">
            <div class="card stat-card h-100"><div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#FFF8EC"><i class="bi bi-award-fill text-warning"></i></div>
                <div><div class="text-muted" style="font-size:.8rem">Licenses</div><div class="fw-bold fs-4"><?= number_format($summary['total_licenses'] ?? 0) ?></div></div>
            </div></div>
        </div>
        <div class="col-sm-3">
            <div class="card stat-card h-100"><div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#F5EEFF"><i class="bi bi-mortarboard-fill" style="color:#7c3aed"></i></div>
                <div><div class="text-muted" style="font-size:.8rem">Top Programme</div><div class="fw-semibold" style="font-size:.9rem"><?= esc($summary['top_programme'] ?? 'N/A') ?></div></div>
            </div></div>
        </div>
    </div>

    <!-- Charts 2-per-row -->
    <div class="row g-3 mb-3">
        <div class="col-lg-6"><div class="chart-card"><div class="chart-title mb-2">Skills Gap</div><canvas id="p1" height="200"></canvas></div></div>
        <div class="col-lg-6"><div class="chart-card"><div class="chart-title mb-2">Employment Sectors</div><canvas id="p2" height="200"></canvas></div></div>
    </div>
    <div class="row g-3 mb-3">
        <div class="col-lg-6"><div class="chart-card"><div class="chart-title mb-2">Top Job Titles</div><canvas id="p3" height="200"></canvas></div></div>
        <div class="col-lg-6"><div class="chart-card"><div class="chart-title mb-2">Top Employers</div><canvas id="p4" height="200"></canvas></div></div>
    </div>
    <div class="row g-3 mb-3">
        <div class="col-lg-6"><div class="chart-card"><div class="chart-title mb-2">Certification Trends</div><canvas id="p5" height="200"></canvas></div></div>
        <div class="col-lg-6"><div class="chart-card"><div class="chart-title mb-2">License Distribution</div><canvas id="p6" height="200"></canvas></div></div>
    </div>
    <div class="row g-3">
        <div class="col-lg-6"><div class="chart-card"><div class="chart-title mb-2">Career Pathways</div><canvas id="p7" height="200"></canvas></div></div>
        <div class="col-lg-6"><div class="chart-card"><div class="chart-title mb-2">Graduation Outcomes</div><canvas id="p8" height="200"></canvas></div></div>
    </div>

</div><!-- /#reportContent -->

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
const COLOURS = ['#4A90D9','#36b37e','#ff5630','#ffab00','#6554c0','#00b8d9','#ff991f','#57d9a3'];
const ALL = <?= $chartData ?>;

function key(k) { return ALL[k] || ALL[k.replace(/-/g,'_')] || {}; }
function gapColour(p) { return p>=70?'#dc3545':p>=40?'#fd7e14':p>=20?'#ffc107':'#4A90D9'; }

// 1 Skills gap
(function(){ const d=key('skills_gap'); if(!d.labels) return;
new Chart(document.getElementById('p1'),{type:'bar',data:{labels:d.labels,datasets:[{label:'Alumni',data:d.data,backgroundColor:(d.penetration||[]).map(gapColour),borderRadius:4}]},options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true}}}}); })();

// 2 Sectors
(function(){ const d=key('employment_sectors'); if(!d.labels) return;
new Chart(document.getElementById('p2'),{type:'doughnut',data:{labels:d.labels,datasets:[{data:d.data,backgroundColor:COLOURS,borderWidth:2}]},options:{responsive:true,plugins:{legend:{position:'bottom',labels:{font:{size:9}}}}}}); })();

// 3 Job titles
(function(){ const d=key('job_titles'); if(!d.labels) return;
new Chart(document.getElementById('p3'),{type:'bar',data:{labels:d.labels,datasets:[{label:'Alumni',data:d.data,backgroundColor:'#4A90D9',borderRadius:4}]},options:{indexAxis:'y',responsive:true,plugins:{legend:{display:false}},scales:{x:{beginAtZero:true}}}}); })();

// 4 Top employers
(function(){ const d=key('top_employers'); if(!d.labels) return;
new Chart(document.getElementById('p4'),{type:'bar',data:{labels:d.labels,datasets:[{label:'Alumni',data:d.data,backgroundColor:'#36b37e',borderRadius:4}]},options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true}}}}); })();

// 5 Cert trends
(function(){ const d=key('certification_trends'); if(!d.labels) return;
new Chart(document.getElementById('p5'),{type:'line',data:{labels:d.labels,datasets:[{label:'Certifications',data:d.data,borderColor:'#4A90D9',backgroundColor:'rgba(74,144,217,0.1)',fill:true,tension:0.4,pointRadius:2}]},options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true}}}}); })();

// 6 License distribution
(function(){ const d=key('license_distribution'); if(!d.labels) return;
new Chart(document.getElementById('p6'),{type:'polarArea',data:{labels:d.labels,datasets:[{data:d.data,backgroundColor:COLOURS.map(c=>c+'99')}]},options:{responsive:true,plugins:{legend:{position:'bottom',labels:{font:{size:9}}}}}}); })();

// 7 Career pathways
(function(){ const d=key('career_pathways'); if(!d.labels) return;
const ds=(d.datasets||[]).map((x,i)=>({...x,backgroundColor:COLOURS[i%COLOURS.length],borderRadius:3}));
new Chart(document.getElementById('p7'),{type:'bar',data:{labels:d.labels,datasets:ds},options:{responsive:true,plugins:{legend:{position:'bottom',labels:{font:{size:9}}}},scales:{y:{beginAtZero:true}}}}); })();

// 8 Graduation outcomes
(function(){ const d=key('graduation_outcomes'); if(!d.labels) return;
const colours=['#4A90D9','#36b37e','#ffab00'];
const ds=(d.datasets||[]).map((x,i)=>({...x,backgroundColor:colours[i]||COLOURS[i],borderRadius:3}));
new Chart(document.getElementById('p8'),{type:'bar',data:{labels:d.labels,datasets:ds},options:{responsive:true,plugins:{legend:{position:'bottom',labels:{font:{size:9}}}},scales:{x:{stacked:true},y:{stacked:true,beginAtZero:true}}}}); })();

async function generatePdf() {
    const btn = document.querySelector('button[onclick="generatePdf()"]');
    btn.disabled = true;
    btn.textContent = 'Generating…';

    await new Promise(r => setTimeout(r, 800)); // let charts render

    const { jsPDF } = window.jspdf;
    const pdf = new jsPDF('p', 'mm', 'a4');
    const canvas = await html2canvas(document.getElementById('reportContent'), { scale: 1.5, useCORS: true });
    const imgData = canvas.toDataURL('image/png');
    const pageW = pdf.internal.pageSize.getWidth();
    const pageH = (canvas.height * pageW) / canvas.width;

    pdf.addImage(imgData, 'PNG', 0, 0, pageW, pageH);

    const date    = new Date().toISOString().slice(0,10);
    const prog    = '<?= esc($active['programme'] ?? 'all') ?>';
    pdf.save(`alumni_report_${prog}_${date}.pdf`);

    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-file-pdf me-1"></i> Download PDF';
}
</script>
<?= $this->endSection() ?>
