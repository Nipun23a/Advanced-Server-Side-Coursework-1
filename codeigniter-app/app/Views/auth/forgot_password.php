<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="auth-container">
    <div class="card auth-card">
        <div class="card-header text-center">
            <h4 class="mb-0">Forgot Password</h4>
            <p class="mb-0 mt-1 opacity-75">Enter your email to receive a reset link</p>
        </div>
        <div class="card-body">

            <!-- Validation Errors -->
            <?php if (session()->getFlashdata('validation')): ?>
                <?php $validation = session()->getFlashdata('validation'); ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($validation->getErrors() as $error): ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="/auth/forgot-password" method="post">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email"
                           class="form-control"
                           id="email"
                           name="email"
                           value="<?= old('email') ?>"
                           placeholder="your.name@eastminster.ac.uk"
                           required
                           autocomplete="email">
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">Send Reset Link</button>
                </div>
            </form>

            <div class="text-center mt-3">
                <a href="/auth/login" class="text-decoration-none">Back to Login</a>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
