<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

    <div class="auth-container">
        <div class="card auth-card">
            <div class="card-header text-center">
                <h4 class="mb-0">Check Your Email</h4>
            </div>
            <div class="card-body text-center">
                <div class="mb-4">
                    <i class="bi bi-envelope-check" style="font-size: 4rem; color: #1B2A4A;"></i>
                </div>

                <h5>Verification Email Sent</h5>
                <p class="text-muted">
                    We've sent a verification link to your email address.
                    Please click the link in the email to verify your account.
                </p>
                <p class="text-muted">
                    The verification link will expire in <strong>24 hours</strong>.
                </p>

                <hr>

                <p class="mb-2">Didn't receive the email?</p>
                <form action="/auth/resend-verification" method="post" class="d-inline">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <input type="email"
                               class="form-control"
                               name="email"
                               placeholder="Enter your email address"
                               required>
                    </div>
                    <button type="submit" class="btn btn-outline-primary">Resend Verification Email</button>
                </form>

                <div class="mt-3">
                    <a href="/auth/login" class="text-decoration-none">Back to Login</a>
                </div>
            </div>
        </div>
    </div>

<?= $this->endSection() ?>