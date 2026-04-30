<?= $this->extend('layouts/analytics') ?>

<?= $this->section('content') ?>

    <div class="container mt-4">
        <h3>API Documentation</h3>

        <iframe
            src="http://localhost:3000/api-docs"
            width="100%"
            height="800px"
            style="border: none;">
        </iframe>
    </div>

<?= $this->endSection() ?>