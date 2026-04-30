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
                    <select name="programme" id="csv_programme" class="form-select form-select-sm">
                        <option value="">All Programmes</option>
                        <?php foreach ($programmes as $p): ?><option value="<?= esc($p) ?>"><?= esc($p) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" style="font-size:.85rem">Graduation Year</label>
                    <select name="graduationYear" id="csv_year" class="form-select form-select-sm">
                        <option value="">All Years</option>
                        <?php foreach ($years as $y): ?><option value="<?= esc($y) ?>"><?= esc($y) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" style="font-size:.85rem">Sector</label>
                    <select name="sector" id="csv_sector" class="form-select form-select-sm">
                        <option value="">All Sectors</option>
                        <?php foreach ($sectors as $s): ?><option value="<?= esc($s) ?>"><?= esc($s) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <p class="text-muted" style="font-size:.8rem">
                    Fields exported: Email, Programme, Graduation Year, Current Employer, Current Job Title, LinkedIn URL
                </p>
                <div class="d-flex gap-2 flex-wrap">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-download me-2"></i> Download CSV
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="savePreset('csv')">
                        <i class="bi bi-bookmark me-1"></i> Save Preset
                    </button>
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="loadPreset('csv')" id="csvLoadBtn" style="display:none">
                        <i class="bi bi-bookmark-fill me-1"></i> Load Preset
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="clearPreset('csv')" id="csvClearBtn" style="display:none">
                        <i class="bi bi-x me-1"></i> Clear
                    </button>
                </div>
                <div id="csvPresetBadge" class="mt-2" style="display:none">
                    <span class="badge bg-primary" style="font-size:.75rem"><i class="bi bi-bookmark-fill me-1"></i>Preset saved</span>
                </div>
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
                    <select name="programme" id="pdf_programme" class="form-select form-select-sm">
                        <option value="">All Programmes</option>
                        <?php foreach ($programmes as $p): ?><option value="<?= esc($p) ?>"><?= esc($p) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" style="font-size:.85rem">Graduation Year</label>
                    <select name="graduationYear" id="pdf_year" class="form-select form-select-sm">
                        <option value="">All Years</option>
                        <?php foreach ($years as $y): ?><option value="<?= esc($y) ?>"><?= esc($y) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <p class="text-muted" style="font-size:.8rem">
                    Includes: summary statistics, all 8 analytics charts, data tables.
                    Charts are rendered client-side and captured to PDF.
                </p>
                <div class="d-flex gap-2 flex-wrap">
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-file-pdf me-2"></i> Generate PDF Report
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="savePreset('pdf')">
                        <i class="bi bi-bookmark me-1"></i> Save Preset
                    </button>
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="loadPreset('pdf')" id="pdfLoadBtn" style="display:none">
                        <i class="bi bi-bookmark-fill me-1"></i> Load Preset
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="clearPreset('pdf')" id="pdfClearBtn" style="display:none">
                        <i class="bi bi-x me-1"></i> Clear
                    </button>
                </div>
                <div id="pdfPresetBadge" class="mt-2" style="display:none">
                    <span class="badge bg-primary" style="font-size:.75rem"><i class="bi bi-bookmark-fill me-1"></i>Preset saved</span>
                </div>
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
<?= $this->section('scripts') ?>
<script>
const PRESET_KEY = 'export_filter_preset';

function getFields(type) {
    if (type === 'csv') return { programme: 'csv_programme', year: 'csv_year', sector: 'csv_sector' };
    return { programme: 'pdf_programme', year: 'pdf_year' };
}

function savePreset(type) {
    const fields = getFields(type);
    const preset = {};
    for (const [key, id] of Object.entries(fields)) {
        preset[key] = document.getElementById(id).value;
    }
    const all = JSON.parse(localStorage.getItem(PRESET_KEY) || '{}');
    all[type] = preset;
    localStorage.setItem(PRESET_KEY, JSON.stringify(all));
    updatePresetUI(type, true);
}

function loadPreset(type) {
    const all = JSON.parse(localStorage.getItem(PRESET_KEY) || '{}');
    const preset = all[type];
    if (!preset) return;
    const fields = getFields(type);
    for (const [key, id] of Object.entries(fields)) {
        const el = document.getElementById(id);
        if (el && preset[key] !== undefined) el.value = preset[key];
    }
}

function clearPreset(type) {
    const all = JSON.parse(localStorage.getItem(PRESET_KEY) || '{}');
    delete all[type];
    localStorage.setItem(PRESET_KEY, JSON.stringify(all));
    updatePresetUI(type, false);
    const fields = getFields(type);
    for (const id of Object.values(fields)) {
        const el = document.getElementById(id);
        if (el) el.value = '';
    }
}

function updatePresetUI(type, exists) {
    document.getElementById(type + 'LoadBtn').style.display  = exists ? '' : 'none';
    document.getElementById(type + 'ClearBtn').style.display = exists ? '' : 'none';
    document.getElementById(type + 'PresetBadge').style.display = exists ? '' : 'none';
}

// On load: show controls for any saved presets and auto-restore
(function () {
    const all = JSON.parse(localStorage.getItem(PRESET_KEY) || '{}');
    ['csv', 'pdf'].forEach(type => {
        if (all[type]) {
            updatePresetUI(type, true);
            loadPreset(type);
        }
    });
})();
</script>
<?= $this->endSection() ?>
