<?= $this->extend('layouts/analytics') ?>
<?= $this->section('content') ?>

<?php
$programmes = $filterOptions['programmes']      ?? [];
$years      = $filterOptions['graduation_years'] ?? [];
$sectors    = $filterOptions['sectors']          ?? [];
?>

<div class="row g-3">

    <!-- CSV Export -->
    <div class="col-lg-6">
        <div class="chart-card h-100">
            <div class="chart-title mb-1"><i class="bi bi-file-earmark-spreadsheet me-2 text-success"></i>Export Alumni CSV</div>
            <div class="chart-subtitle mb-3">Download filtered alumni data as a spreadsheet</div>

            <form id="csvForm" method="get" action="/export/csv">
                <div class="mb-3">
                    <label class="form-label" style="font-size:.85rem">Programme</label>
                    <select name="programme" class="form-select form-select-sm">
                        <option value="">All Programmes</option>
                        <?php foreach ($programmes as $p): ?><option value="<?= esc($p) ?>"><?= esc($p) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" style="font-size:.85rem">Graduation Year</label>
                    <select name="graduationYear" class="form-select form-select-sm">
                        <option value="">All Years</option>
                        <?php foreach ($years as $y): ?><option value="<?= esc($y) ?>"><?= esc($y) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" style="font-size:.85rem">Sector</label>
                    <select name="sector" class="form-select form-select-sm">
                        <option value="">All Sectors</option>
                        <?php foreach ($sectors as $s): ?><option value="<?= esc($s) ?>"><?= esc($s) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <p class="text-muted" style="font-size:.8rem">
                    Fields exported: Email, Programme, Graduation Year, Current Employer, Current Job Title, LinkedIn URL
                </p>
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-download me-2"></i> Download CSV
                </button>
            </form>
        </div>
    </div>

    <!-- PDF Report -->
    <div class="col-lg-6">
        <div class="chart-card h-100">
            <div class="chart-title mb-1"><i class="bi bi-file-earmark-pdf me-2 text-danger"></i>Generate Analytics Report</div>
            <div class="chart-subtitle mb-3">Create a PDF report with all charts and summary data</div>

            <form id="pdfForm" method="get" action="/export/pdf">
                <div class="mb-3">
                    <label class="form-label" style="font-size:.85rem">Programme</label>
                    <select name="programme" class="form-select form-select-sm">
                        <option value="">All Programmes</option>
                        <?php foreach ($programmes as $p): ?><option value="<?= esc($p) ?>"><?= esc($p) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" style="font-size:.85rem">Graduation Year</label>
                    <select name="graduationYear" class="form-select form-select-sm">
                        <option value="">All Years</option>
                        <?php foreach ($years as $y): ?><option value="<?= esc($y) ?>"><?= esc($y) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <p class="text-muted" style="font-size:.8rem">
                    Includes: summary statistics, all 8 analytics charts, data tables.
                    Charts are rendered client-side and captured to PDF.
                </p>
                <button type="submit" class="btn btn-danger">
                    <i class="bi bi-file-pdf me-2"></i> Generate PDF Report
                </button>
            </form>
        </div>
    </div>

    <!-- Chart Downloads -->
    <div class="col-12">
        <div class="chart-card">
            <div class="chart-title mb-1"><i class="bi bi-images me-2 text-primary"></i>Download Individual Charts</div>
            <div class="chart-subtitle mb-3">Download any analytics chart as a PNG image</div>
            <div class="d-flex flex-wrap gap-2">
                <?php
                $chartLinks = [
                    'Skills Gap'            => '/analytics/skills-gap',
                    'Employment Sectors'    => '/analytics/employment-sectors',
                    'Job Titles'            => '/analytics/job-titles',
                    'Top Employers'         => '/analytics/top-employers',
                    'Certification Trends'  => '/analytics/certification-trends',
                    'License Distribution'  => '/analytics/license-distribution',
                    'Career Pathways'       => '/analytics/career-pathways',
                    'Graduation Outcomes'   => '/analytics/graduation-outcomes',
                ];
                foreach ($chartLinks as $name => $href): ?>
                    <a href="<?= $href ?>" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-bar-chart me-1"></i> <?= $name ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

</div>

<?= $this->endSection() ?>
