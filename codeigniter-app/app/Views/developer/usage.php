<?= $this->extend('layouts/analytics') ?>

<?= $this->section('content') ?>

<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 id="usageTitle" class="mb-0">API Key Usage</h3>
            <p class="text-muted mb-0" style="font-size:.875rem">Usage statistics, endpoint breakdown, and recent access logs</p>
        </div>
        <a href="/developer/api-keys" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to API Keys
        </a>
    </div>

    <div id="usageSummary" class="alert alert-info">
        <i class="bi bi-hourglass-split me-1"></i> Loading usage data&hellip;
    </div>

    <div id="usageContent"></div>

</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
const clientTypeMeta = {
    analytics_dashboard: { label: 'Analytics Dashboard', colour: 'primary' },
    ar_app:              { label: 'AR App',               colour: 'success' },
    third_party:         { label: 'Third Party',          colour: 'warning' },
};

function clientLabel(ct) {
    return clientTypeMeta[ct]?.label ?? (ct || 'Unscoped');
}
function clientColour(ct) {
    return clientTypeMeta[ct]?.colour ?? 'secondary';
}

function renderPermissions(perms) {
    if (!perms?.length) return '<span class="badge bg-secondary">No permissions</span>';
    return perms.map(p => `<span class="badge bg-light text-dark border me-1">${p}</span>`).join('');
}

function renderEndpointBreakdown(breakdown) {
    if (!breakdown?.length) return '<p class="text-muted">No endpoint data recorded yet.</p>';
    return `
        <div class="table-responsive">
          <table class="table table-sm table-bordered mb-0">
            <thead class="table-light">
              <tr><th>Endpoint</th><th>Method</th><th class="text-end">Requests</th></tr>
            </thead>
            <tbody>
              ${breakdown.map(r => `
                <tr>
                  <td><code>${r.endpoint}</code></td>
                  <td><span class="badge bg-secondary">${r.http_method ?? r.method ?? '—'}</span></td>
                  <td class="text-end fw-semibold">${r.count ?? r.total ?? 0}</td>
                </tr>`).join('')}
            </tbody>
          </table>
        </div>`;
}

function renderRecentRequests(requests) {
    if (!requests?.length) return '<p class="text-muted">No recent requests recorded yet.</p>';
    return `
        <div class="table-responsive">
          <table class="table table-sm table-hover mb-0">
            <thead class="table-light">
              <tr><th>Timestamp</th><th>Method</th><th>Endpoint</th><th>IP</th></tr>
            </thead>
            <tbody>
              ${requests.map(r => `
                <tr>
                  <td class="text-nowrap" style="font-size:.8rem">${r.access_at ?? r.created_at ?? '—'}</td>
                  <td><span class="badge bg-secondary">${r.http_method ?? r.method ?? '—'}</span></td>
                  <td><code style="font-size:.8rem">${r.endpoint}</code></td>
                  <td class="text-muted" style="font-size:.8rem">${r.source_ip ?? r.ip ?? '—'}</td>
                </tr>`).join('')}
            </tbody>
          </table>
        </div>`;
}

function renderKeyCard(key, statistics) {
    const totalRequests   = statistics.total_requests   ?? 0;
    const endpointBreakdown = statistics.endpoint_breakdown ?? statistics.endpoints ?? [];
    const recentRequests  = statistics.recent_requests  ?? statistics.recent ?? [];

    return `
      <div class="card mb-4 shadow-sm">
        <div class="card-header d-flex align-items-center gap-2">
          <strong>API Key #${key.id}</strong>
          <span class="badge bg-${key.is_active ? 'success' : 'danger'}">${key.is_active ? 'Active' : 'Revoked'}</span>
          <span class="badge bg-${clientColour(key.client_type)}">${clientLabel(key.client_type)}</span>
          <span class="ms-auto text-muted" style="font-size:.8rem">Scope: ${key.client_type ?? 'unscoped'}</span>
        </div>
        <div class="card-body">

          <!-- Permissions -->
          <div class="mb-3">
            <div class="fw-semibold mb-1" style="font-size:.85rem">Permissions</div>
            ${renderPermissions(key.permissions)}
          </div>

          <!-- Stats row -->
          <div class="row g-3 mb-3">
            <div class="col-sm-4">
              <div class="border rounded p-3 text-center">
                <div class="fs-4 fw-bold text-primary">${totalRequests.toLocaleString()}</div>
                <div class="text-muted" style="font-size:.8rem">Total Requests</div>
              </div>
            </div>
            <div class="col-sm-4">
              <div class="border rounded p-3 text-center">
                <div class="fs-4 fw-bold text-success">${endpointBreakdown.length}</div>
                <div class="text-muted" style="font-size:.8rem">Unique Endpoints</div>
              </div>
            </div>
            <div class="col-sm-4">
              <div class="border rounded p-3 text-center">
                <div class="fs-4 fw-bold text-warning">${recentRequests.length}</div>
                <div class="text-muted" style="font-size:.8rem">Recent Logged</div>
              </div>
            </div>
          </div>

          <!-- Endpoint breakdown -->
          <div class="mb-3">
            <div class="fw-semibold mb-2" style="font-size:.85rem">
              <i class="bi bi-bar-chart-steps me-1"></i> Endpoint Breakdown
            </div>
            ${renderEndpointBreakdown(endpointBreakdown)}
          </div>

          <!-- Recent access log -->
          <div>
            <div class="fw-semibold mb-2" style="font-size:.85rem">
              <i class="bi bi-clock-history me-1"></i> Recent Access Log
            </div>
            ${renderRecentRequests(recentRequests)}
          </div>

        </div>
      </div>`;
}

async function loadUsage() {
    const summary = document.getElementById('usageSummary');
    const content = document.getElementById('usageContent');

    const urlParams    = new URLSearchParams(window.location.search);
    const selectedKeyId = urlParams.get('key_id');

    if (selectedKeyId) {
        document.getElementById('usageTitle').textContent = `Usage — API Key #${selectedKeyId}`;
    }

    try {
        const keysRes  = await fetch('/api/developer/api-keys');
        const keysData = await keysRes.json();

        if (!keysData.success) {
            summary.className   = 'alert alert-danger';
            summary.textContent = keysData.message || 'Failed to load API keys.';
            return;
        }

        const allKeys = keysData.data?.keys ?? [];
        const keys    = selectedKeyId
            ? allKeys.filter(k => String(k.id) === String(selectedKeyId))
            : allKeys;

        if (keys.length === 0) {
            summary.className   = 'alert alert-warning';
            summary.textContent = 'No API keys found.';
            return;
        }

        const results = await Promise.all(
            keys.map(async key => {
                try {
                    const res  = await fetch(`/api/developer/api-keys/${key.id}/stats`);
                    const data = await res.json();
                    return { key, statistics: data.data?.statistics ?? {} };
                } catch {
                    return { key, statistics: {} };
                }
            })
        );

        const totalReqs = results.reduce((s, r) => s + (r.statistics.total_requests ?? 0), 0);

        summary.className   = 'alert alert-success';
        summary.innerHTML   = `
            <i class="bi bi-check-circle me-1"></i>
            Showing usage for <strong>${keys.length}</strong> key(s) —
            <strong>${totalReqs.toLocaleString()}</strong> total requests recorded.`;

        content.innerHTML = results.map(({ key, statistics }) => renderKeyCard(key, statistics)).join('');

    } catch (err) {
        summary.className   = 'alert alert-danger';
        summary.textContent = err.message;
    }
}

loadUsage();
</script>
<?= $this->endSection() ?>
