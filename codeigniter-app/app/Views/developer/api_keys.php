<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

    <div class="container mt-4">
        <h3>API Keys</h3>

        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">External API Secret</h5>
                <p class="text-muted mb-3">
                    Save an existing secret or generate a new one. The web app will use this stored value instead of a local <code>.env</code> secret.
                </p>

                <div id="secretStatus" class="alert alert-secondary mb-3">Checking secret status...</div>

                <div class="mb-3">
                    <label for="externalSecret" class="form-label">Enter external API secret</label>
                    <input
                        type="text"
                        id="externalSecret"
                        class="form-control"
                        placeholder="Paste the external API secret here"
                    >
                </div>

                <div class="d-flex gap-2 flex-wrap">
                    <button class="btn btn-outline-primary" onclick="saveSecret()">Save Secret</button>
                    <button class="btn btn-outline-dark" onclick="generateSecret()">Generate External API Secret</button>
                </div>
            </div>
        </div>

        <button class="btn btn-primary mb-3" onclick="createKey()">Generate New Key</button>

        <ul id="keyList" class="list-group"></ul>
    </div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>

    <script>
        let secretConfigured = false;

        function updateSecretStatus(data) {
            const status = document.getElementById('secretStatus');
            secretConfigured = Boolean(data?.configured);

            if (secretConfigured) {
                status.className = 'alert alert-success mb-3';
                status.textContent = `External API secret is configured${data?.updated_at ? ` (updated ${data.updated_at})` : ''}.`;
                return;
            }

            status.className = 'alert alert-warning mb-3';
            status.textContent = 'External API secret is not configured yet.';
        }

        async function loadSecretStatus() {
            const res = await fetch('/api/developer/internal-secret');
            const data = await res.json();

            if (!data.success) {
                throw new Error(data.message || 'Failed to load secret status.');
            }

            updateSecretStatus(data.data);
        }

        async function saveSecret() {
            const secret = document.getElementById('externalSecret').value.trim();

            if (!secret) {
                alert('Enter the external API secret first.');
                return;
            }

            const res = await fetch('/api/developer/internal-secret', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ secret }),
            });
            const data = await res.json();

            if (!data.success) {
                alert(data.message || 'Failed to save the external API secret.');
                return;
            }

            document.getElementById('externalSecret').value = '';
            alert('External API secret saved successfully.');
            updateSecretStatus(data.data);
            loadKeys();
        }

        async function generateSecret() {
            const res = await fetch('/api/developer/internal-secret/generate', { method: 'POST' });
            const data = await res.json();

            if (!data.success || !data.data?.secret) {
                alert(data.message || 'Failed to generate the external API secret.');
                return;
            }

            updateSecretStatus(data.data);
            alert(
                "Your external API secret:\n" +
                data.data.secret +
                "\n\nSave it securely. You won't see it again."
            );
            loadKeys();
        }

        async function loadKeys() {
            const res = await fetch('/api/developer/api-keys');
            const data = await res.json();

            const list = document.getElementById('keyList');
            list.innerHTML = '';

            if (!data.success) {
                list.innerHTML = `<li class="list-group-item text-danger">${data.message}</li>`;
                return;
            }

            const keys = data.data?.keys || [];

            keys.forEach(key => {
                const statusBadge = key.is_active
                    ? `<span class="badge bg-success ms-2">Active</span>`
                    : `<span class="badge bg-danger ms-2">Revoked</span>`;

                const revokeButton = key.is_active
                    ? `<button class="btn btn-danger btn-sm ms-2" onclick="deleteKey(${key.id})">Revoke</button>`
                    : '';

                const usageButton = `
                <button class="btn btn-info btn-sm ms-2" onclick="viewUsage(${key.id})">
                    Usage
                </button>
            `;

                list.innerHTML += `
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                    <strong>ID: ${key.id}</strong>
                    ${statusBadge}
                </div>
                <div>
                    ${usageButton}
                    ${revokeButton}
                </div>
            </li>`;
            });
        }

        async function createKey() {
            if (!secretConfigured) {
                const shouldGenerateSecret = confirm(
                    'The external API secret is not configured yet. Generate one now before creating an API key?'
                );

                if (!shouldGenerateSecret) {
                    return;
                }

                await generateSecret();

                if (!secretConfigured) {
                    return;
                }
            }

            const res = await fetch('/api/developer/api-keys', { method: 'POST' });
            const data = await res.json();

            if (data.success && data.data) {
                alert("Your API Key:\n" + data.data.key + "\n\nSave it securely. You won't see it again.");
            } else {
                alert("Failed to generate key");
            }

            loadKeys();
        }

        async function deleteKey(id) {
            await fetch(`/api/developer/api-keys/${id}`, { method: 'DELETE' });
            loadKeys();
        }
        function viewUsage(id) {
            window.location.href = `/developer/usage?key_id=${id}`;
        }

        async function init() {
            try {
                await loadSecretStatus();
            } catch (error) {
                const status = document.getElementById('secretStatus');
                status.className = 'alert alert-danger mb-3';
                status.textContent = error.message;
            }

            loadKeys();
        }

        init();
    </script>

<?= $this->endSection() ?>
