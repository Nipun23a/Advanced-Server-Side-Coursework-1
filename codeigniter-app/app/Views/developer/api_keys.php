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

            console.log(data); // debug

            const list = document.getElementById('keyList');
            list.innerHTML = '';

            if (!data.success) {
                list.innerHTML = `<li class="list-group-item text-danger">${data.message}</li>`;
                return;
            }
            const keys = data.data.keys;

            keys.forEach(key => {
                list.innerHTML += `
        <li class="list-group-item d-flex justify-content-between">
            <span>ID: ${key.id}</span>
            <button class="btn btn-danger btn-sm" onclick="deleteKey(${key.id})">Revoke</button>
        </li>`;
            });
        }

        async function createKey() {
            const res = await fetch('/api/developer/api-keys', { method: 'POST' });
            const data = await res.json();

            alert("Your API Key:\n" + data.data.key + "\n\nPlease save it securely. You won't be able to see it again.");
            loadKeys();
        }

        async function deleteKey(id) {
            await fetch(`/api/developer/api-keys/${id}`, { method: 'DELETE' });
            loadKeys();
        }

        loadKeys();

    </script>

<?= $this->endSection() ?>