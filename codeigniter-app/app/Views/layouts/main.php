<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= $title ?? 'Alumni Influencers Platform' ?></title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .auth-container {
            max-width: 480px;
            margin: 60px auto;
        }
        .auth-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.08);
        }
        .auth-card .card-header {
            background-color: #1B2A4A;
            color: #ffffff;
            border-radius: 12px 12px 0 0;
            padding: 20px 30px;
        }
        .auth-card .card-body {
            padding: 30px;
        }
        .btn-primary {
            background-color: #1B2A4A;
            border-color: #1B2A4A;
        }
        .btn-primary:hover {
            background-color: #2C3E6B;
            border-color: #2C3E6B;
        }
        .password-requirements {
            font-size: 0.85rem;
            color: #6c757d;
        }
        .password-requirements li.met {
            color: #198754;
        }
    </style>
</head>
<body>

<!-- Navigation Bar -->
<?php if (session()->get('is_logged_in')): ?>
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #1B2A4A;">
        <div class="container">
            <a class="navbar-brand" href="/dashboard">Alumni Influencers</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/dashboard">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/profile">Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/bidding">Bidding</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/sponsorship/offers">Sponsorships</a>
                    </li>
                    <?php if (session()->get('user_role') === 'developer' || session()->get('user_role') === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/developer/api-keys">API Keys</a>
                        </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <span class="nav-link text-light"><?= esc(session()->get('user_email')) ?></span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/auth/logout">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
<?php endif; ?>

<!-- Flash Messages -->
<div class="container mt-3">
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= esc(session()->getFlashdata('success')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= esc(session()->getFlashdata('error')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('info')): ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <?= esc(session()->getFlashdata('info')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
</div>

<!-- Main Content -->
<main>
    <?= $this->renderSection('content') ?>
</main>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Page-specific scripts -->
<?= $this->renderSection('scripts') ?>
</body>
</html>
