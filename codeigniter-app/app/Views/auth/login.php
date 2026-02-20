<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="auth-container">
    <div class="card auth-card">
        <div class="card-header text-center">
            <h4 class="mb-0">Welcome Back</h4>
            <p class="mb-0 mt-1 opacity-75">Log in to Alumni Influencers</p>
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

            <!-- Login Form -->
            <form action="/auth/login" method="post">
                <?= csrf_field() ?>

                <!-- Email Field -->
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

                <!-- Password Field -->
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <input type="password"
                               class="form-control"
                               id="password"
                               name="password"
                               required
                               autocomplete="current-password">
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- Forgot Password Link -->
                <div class="mb-3 text-end">
                    <a href="/auth/forgot-password" class="text-decoration-none">Forgot your password?</a>
                </div>

                <!-- Submit Button -->
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">Log In</button>
                </div>
            </form>

            <!-- Resend Verification (shown only when login fails due to unverified email) -->
            <?php if (session()->getFlashdata('show_resend')): ?>
                <div class="mt-3 p-3 bg-light rounded">
                    <p class="mb-2">Didn't receive the verification email?</p>
                    <form action="/auth/resend-verification" method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="email" value="<?= old('email') ?>">
                        <button type="submit" class="btn btn-outline-primary btn-sm">Resend Verification Email</button>
                    </form>
                </div>
            <?php endif; ?>

            <!-- Register Link -->
            <div class="text-center mt-3">
                <p class="mb-0">Don't have an account? <a href="/auth/register">Register here</a></p>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Toggle password visibility
    document.getElementById('togglePassword').addEventListener('click', function() {
        const input = document.getElementById('password');
        const icon = this.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('bi-eye', 'bi-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('bi-eye-slash', 'bi-eye');
        }
    });
</script>
<?= $this->endSection() ?>
