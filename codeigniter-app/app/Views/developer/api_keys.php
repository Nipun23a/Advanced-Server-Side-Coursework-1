<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

    <div class="container mt-4">
        <h3>API Keys</h3>

        <button class="btn btn-primary mb-3" onclick="createKey()">Generate New Key</button>

        <ul id="keyList" class="list-group"></ul>
    </div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>

    <script>
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

        loadKeys();
    </script>

<?= $this->endSection() ?>