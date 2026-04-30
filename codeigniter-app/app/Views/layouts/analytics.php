<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Analytics Dashboard') ?> — Alumni Influencers</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <!-- jsPDF + html2canvas for PDF export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <style>
        :root {
            --sidebar-width: 260px;
            --primary:       #1B2A4A;
            --primary-light: #2C3E6B;
            --accent:        #4A90D9;
        }

        body { background: #f0f2f5; font-family: 'Segoe UI', sans-serif; }

        /* ---- Sidebar ---- */
        #sidebar {
            width: var(--sidebar-width);
            min-height: 100vh;
            background: var(--primary);
            position: fixed;
            top: 0; left: 0;
            z-index: 100;
            transition: all 0.3s;
        }
        #sidebar .sidebar-brand {
            padding: 20px;
            color: #fff;
            font-size: 1.1rem;
            font-weight: 700;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        #sidebar .nav-link {
            color: rgba(255,255,255,0.75);
            padding: 10px 20px;
            font-size: 0.9rem;
            border-radius: 0;
            transition: all 0.2s;
        }
        #sidebar .nav-link:hover,
        #sidebar .nav-link.active {
            color: #fff;
            background: rgba(255,255,255,0.1);
            padding-left: 26px;
        }
        #sidebar .nav-link i { width: 20px; margin-right: 8px; }
        #sidebar .nav-section {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: rgba(255,255,255,0.4);
            padding: 16px 20px 6px;
        }

        /* ---- Main content ---- */
        #main-content { margin-left: var(--sidebar-width); min-height: 100vh; }

        /* ---- Top bar ---- */
        #topbar {
            background: #fff;
            border-bottom: 1px solid #e0e0e0;
            padding: 14px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        /* ---- Stat cards ---- */
        .stat-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            transition: transform 0.2s;
        }
        .stat-card:hover { transform: translateY(-2px); }
        .stat-card .stat-icon {
            width: 48px; height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
        }

        /* ---- Chart cards ---- */
        .chart-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            padding: 20px;
            margin-bottom: 24px;
        }
        .chart-card .chart-title {
            font-size: 0.95rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 4px;
        }
        .chart-card .chart-subtitle {
            font-size: 0.8rem;
            color: #888;
            margin-bottom: 16px;
        }

        /* ---- Filter bar ---- */
        .filter-bar {
            background: #fff;
            border-radius: 10px;
            padding: 14px 20px;
            margin-bottom: 20px;
            box-shadow: 0 1px 6px rgba(0,0,0,0.05);
        }

        .btn-download-chart { font-size: 0.75rem; padding: 3px 10px; }

        .chart-loading {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 250px;
            color: #aaa;
        }

        /* ---- Skills-gap badges ---- */
        .gap-critical     { background: #dc3545; color: #fff; }
        .gap-significant  { background: #fd7e14; color: #fff; }
        .gap-emerging     { background: #ffc107; color: #000; }
    </style>

    <?= $this->renderSection('styles') ?>
</head>
<body>

<!-- ================================================================ -->
<!-- SIDEBAR                                                           -->
<!-- ================================================================ -->
<nav id="sidebar">
    <div class="sidebar-brand">
        <i class="bi bi-mortarboard-fill me-2"></i>
        Analytics Dashboard
    </div>

    <ul class="nav flex-column mt-2">

        <li class="nav-item">
            <a href="/dashboard"
               class="nav-link <?= uri_string() === 'dashboard' ? 'active' : '' ?>">
                <i class="bi bi-grid-1x2"></i> Dashboard
            </a>
        </li>

        <div class="nav-section">Analytics</div>

        <li class="nav-item">
            <a href="/analytics"
               class="nav-link <?= uri_string() === 'analytics' ? 'active' : '' ?>">
                <i class="bi bi-bar-chart-line"></i> Overview
            </a>
        </li>
        <li class="nav-item">
            <a href="/analytics/skills-gap"
               class="nav-link <?= uri_string() === 'analytics/skills-gap' ? 'active' : '' ?>">
                <i class="bi bi-diagram-3"></i> Skills Gap
            </a>
        </li>
        <li class="nav-item">
            <a href="/analytics/employment-sectors"
               class="nav-link <?= uri_string() === 'analytics/employment-sectors' ? 'active' : '' ?>">
                <i class="bi bi-pie-chart"></i> Employment Sectors
            </a>
        </li>
        <li class="nav-item">
            <a href="/analytics/job-titles"
               class="nav-link <?= uri_string() === 'analytics/job-titles' ? 'active' : '' ?>">
                <i class="bi bi-person-badge"></i> Job Titles
            </a>
        </li>
        <li class="nav-item">
            <a href="/analytics/top-employers"
               class="nav-link <?= uri_string() === 'analytics/top-employers' ? 'active' : '' ?>">
                <i class="bi bi-building"></i> Top Employers
            </a>
        </li>
        <li class="nav-item">
            <a href="/analytics/certification-trends"
               class="nav-link <?= uri_string() === 'analytics/certification-trends' ? 'active' : '' ?>">
                <i class="bi bi-graph-up-arrow"></i> Cert Trends
            </a>
        </li>
        <li class="nav-item">
            <a href="/analytics/license-distribution"
               class="nav-link <?= uri_string() === 'analytics/license-distribution' ? 'active' : '' ?>">
                <i class="bi bi-award"></i> Licenses
            </a>
        </li>
        <li class="nav-item">
            <a href="/analytics/career-pathways"
               class="nav-link <?= uri_string() === 'analytics/career-pathways' ? 'active' : '' ?>">
                <i class="bi bi-signpost-split"></i> Career Pathways
            </a>
        </li>
        <li class="nav-item">
            <a href="/analytics/graduation-outcomes"
               class="nav-link <?= uri_string() === 'analytics/graduation-outcomes' ? 'active' : '' ?>">
                <i class="bi bi-calendar-check"></i> Outcomes
            </a>
        </li>

        <div class="nav-section">Data</div>

        <li class="nav-item">
            <a href="/alumni"
               class="nav-link <?= str_starts_with(uri_string(), 'alumni') ? 'active' : '' ?>">
                <i class="bi bi-people"></i> Browse Alumni
            </a>
        </li>
        <li class="nav-item">
            <a href="/export"
               class="nav-link <?= str_starts_with(uri_string(), 'export') ? 'active' : '' ?>">
                <i class="bi bi-download"></i> Export
            </a>
        </li>

        <?php if (session()->get('user_role') === 'developer' || session()->get('user_role') === 'admin'): ?>
            <div class="nav-section">Developer</div>
            <li class="nav-item">
                <a href="/developer/api-keys"
                   class="nav-link <?= uri_string() === 'developer/api-keys' ? 'active' : '' ?>">
                    <i class="bi bi-key"></i> API Keys
                </a>
            </li>
            <li class="nav-item">
                <a href="/developer/api-docs"
                   class="nav-link <?= uri_string() === 'developer/api-docs' ? 'active' : '' ?>">
                    <i class="bi bi-file-earmark-text"></i> API Docs
                </a>
            </li>
        <?php endif; ?>

        <div class="nav-section">Account</div>

        <li class="nav-item">
            <a href="/profile"
               class="nav-link <?= str_starts_with(uri_string(), 'profile') ? 'active' : '' ?>">
                <i class="bi bi-person-circle"></i> My Profile
            </a>
        </li>
        <li class="nav-item">
            <a href="/bidding"
               class="nav-link <?= str_starts_with(uri_string(), 'bidding') ? 'active' : '' ?>">
                <i class="bi bi-currency-pound"></i> Bidding
            </a>
        </li>
        <li class="nav-item">
            <a href="/sponsorship/offers"
               class="nav-link <?= str_starts_with(uri_string(), 'sponsorship') ? 'active' : '' ?>">
                <i class="bi bi-gift"></i> Sponsorships
            </a>
        </li>
        <li class="nav-item">
            <a href="/auth/logout" class="nav-link">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        </li>

    </ul>
</nav>

<!-- ================================================================ -->
<!-- MAIN CONTENT                                                      -->
<!-- ================================================================ -->
<div id="main-content">

    <!-- Top Bar -->
    <div id="topbar">
        <div>
            <span class="text-muted" style="font-size:0.85rem;">University of Eastminster</span>
            <h5 class="mb-0 fw-semibold"><?= esc($title ?? 'Dashboard') ?></h5>
        </div>
        <div class="d-flex align-items-center gap-3">
            <span class="text-muted" style="font-size:0.85rem;">
                <i class="bi bi-person-circle me-1"></i>
                <?= esc(session()->get('user_email') ?? '') ?>
            </span>
        </div>
    </div>

    <!-- Flash Messages -->
    <div class="px-4 pt-3">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= esc(session()->getFlashdata('success')) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= esc(session()->getFlashdata('error')) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('info')): ?>
            <div class="alert alert-info alert-dismissible fade show">
                <?= esc(session()->getFlashdata('info')) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
    </div>

    <!-- Page Content -->
    <div class="p-4">
        <?= $this->renderSection('content') ?>
    </div>

</div><!-- /#main-content -->

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Download chart as PNG helper (available to all analytics views) -->
<script>
function downloadChart(canvasId, filename) {
    const canvas = document.getElementById(canvasId);
    const link   = document.createElement('a');
    link.download = filename + '.png';
    link.href     = canvas.toDataURL('image/png');
    link.click();
}
</script>

<?= $this->renderSection('scripts') ?>
</body>
</html>
