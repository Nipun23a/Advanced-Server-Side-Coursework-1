<?= $this->extend('layouts/analytics') ?>

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

        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Generate New API Key</h5>
                <p class="text-muted mb-3">
                    Select the client type first. Permissions are assigned automatically from this choice.
                </p>

                <div class="row g-3 align-items-end">
                    <div class="col-md-6">
                        <label for="clientType" class="form-label">Client type</label>
                        <select id="clientType" class="form-select">
                            <option value="analytics_dashboard">Analytics Dashboard</option>
                            <option value="ar_app">AR App</option>
                            <option value="third_party">Third Party</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <div id="clientTypeHelp" class="small text-muted"></div>
                    </div>
                </div>

                <button class="btn btn-primary mt-3" onclick="createKey()">Generate New Key</button>
            </div>
        </div>

        <ul id="keyList" class="list-group"></ul>
    </div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>

    <script>
        let secretConfigured = false;
        const clientTypeMeta = {
            analytics_dashboard: {
                label: 'Analytics Dashboard',
                permissions: ['read:alumni', 'read:analytics', 'read:export'],
            },
            ar_app: {
                label: 'AR App',
                permissions: ['read:alumni_of_day'],
            },
            third_party: {
                label: 'Third Party',
                permissions: ['read:alumni_of_day'],
            },
        };

        function getClientTypeMeta(clientType) {
            return clientTypeMeta[clientType] || {
                label: clientType || 'Unscoped',
                permissions: [],
            };
        }

        function renderPermissionBadges(permissions) {
            if (!permissions || permissions.length === 0) {
                return '<span class="badge bg-secondary">No permissions</span>';
            }

            return permissions
                .map(permission => `<span class="badge bg-light text-dark border me-1">${permission}</span>`)
                .join('');
        }

        function updateClientTypeHelp() {
            const select = document.getElementById('clientType');
            const help = document.getElementById('clientTypeHelp');
            const meta = getClientTypeMeta(select.value);

            help.innerHTML = `
                <strong>${meta.label}</strong><br>
                Permissions: ${meta.permissions.join(', ')}
            `;
        }

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

                const clientType = key.client_type || 'unscoped';
                const meta = getClientTypeMeta(key.client_type);
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
                    <div class="mt-2">
                        <span class="badge bg-primary-subtle text-primary-emphasis border">${meta.label}</span>
                    </div>
                    <div class="small text-muted mt-1">Stored scope: ${clientType}</div>
                    <div class="mt-2">${renderPermissionBadges(key.permissions)}</div>
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

            const clientType = document.getElementById('clientType').value;
            const res = await fetch('/api/developer/api-keys', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ client_type: clientType }),
            });
            const data = await res.json();

            if (data.success && data.data) {
                const meta = getClientTypeMeta(data.data.client_type || clientType);
                const permissions = (data.data.permissions || meta.permissions).join(', ');

                alert(
                    "Your API Key:\n" +
                    data.data.key +
                    "\n\nClient Type: " + meta.label +
                    "\nStored Scope: " + (data.data.client_type || clientType) +
                    "\nPermissions: " + permissions +
                    "\n\nSave it securely. You won't see it again."
                );
            } else {
                alert(data.message || "Failed to generate key");
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
            updateClientTypeHelp();
            document.getElementById('clientType').addEventListener('change', updateClientTypeHelp);

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
