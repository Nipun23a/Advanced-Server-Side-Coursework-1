<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="container mt-4">
    <h3>API Usage</h3>

    <p>This page will show API usage statistics.</p>

    <ul id="usageList" class="list-group"></ul>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>

<script>
    async function loadUsage() {
        const res = await fetch('/api/developer/api-keys');
        const data = await res.json();

        const list = document.getElementById('usageList');
        list.innerHTML = '';

        data.data.forEach(key => {
            list.innerHTML += `
            <li class="list-group-item">
                API Key ID: ${key.id}
            </li>`;
        });
    }

    loadUsage();
</script>

<?= $this->endSection() ?>
