<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

    <div class="container mt-4">
        <h3 id="usageTitle">API Usage</h3>

        <div id="usageSummary" class="alert alert-info">Loading...</div>

        <ul id="usageList" class="list-group"></ul>
    </div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>

    <script>
        async function loadUsage() {
            const summary = document.getElementById('usageSummary');
            const container = document.getElementById('usageList');

            const urlParams = new URLSearchParams(window.location.search);
            const selectedKeyId = urlParams.get('key_id');

            if (selectedKeyId) {
                document.getElementById('usageTitle').textContent = `Usage for API Key #${selectedKeyId}`;
            }

            try {
                const keysResponse = await fetch('/api/developer/api-keys');
                const keysPayload = await keysResponse.json();

                if (!keysPayload.success) {
                    summary.className = 'alert alert-danger';
                    summary.textContent = keysPayload.message;
                    return;
                }

                const allKeys = keysPayload.data?.keys || [];

                const keys = selectedKeyId
                    ? allKeys.filter(k => k.id == selectedKeyId)
                    : allKeys;

                if (keys.length === 0) {
                    summary.className = 'alert alert-warning';
                    summary.textContent = 'No API keys found.';
                    return;
                }

                const statsPayloads = await Promise.all(
                    keys.map(async (key) => {
                        const res = await fetch(`/api/developer/api-keys/${key.id}/stats`);
                        const stats = await res.json();
                        return { key, stats };
                    })
                );

                summary.className = 'alert alert-success';
                summary.textContent = `Loaded usage for ${keys.length} key(s).`;

                container.innerHTML = '';

                statsPayloads.forEach(({ key, stats }) => {
                    if (!stats.success) return;

                    const statistics = stats.data?.statistics || {};

                    container.innerHTML += `
                <li class="list-group-item">
                    <h5>API Key #${key.id}</h5>
                    <p><strong>Total Requests:</strong> ${statistics.total_requests || 0}</p>
                </li>`;
                });

            } catch (error) {
                summary.className = 'alert alert-danger';
                summary.textContent = error.message;
            }
        }

        loadUsage();
    </script>

<?= $this->endSection() ?>