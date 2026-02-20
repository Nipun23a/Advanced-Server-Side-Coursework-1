<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

    <div class="auth-container">
        <div class="card auth-card">
            <div class="card-header text-center">
                <h4 class="mb-0">Reset Your Password</h4>
                <p class="mb-0 mt-1 opacity-75">Enter your new password below</p>
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

                <form action="/auth/reset-password" method="post">
                    <?= csrf_field() ?>

                    <!-- Hidden token field -->
                    <input type="hidden" name="token" value="<?= esc($token) ?>">

                    <!-- New Password -->
                    <div class="mb-3">
                        <label for="password" class="form-label">New Password</label>
                        <div class="input-group">
                            <input type="password"
                                   class="form-control"
                                   id="password"
                                   name="password"
                                   required
                                   minlength="8"
                                   autocomplete="new-password">
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <ul class="password-requirements mt-2 list-unstyled" id="passwordRequirements">
                            <li id="req-length"><i class="bi bi-x-circle"></i> At least 8 characters</li>
                            <li id="req-upper"><i class="bi bi-x-circle"></i> At least one uppercase letter</li>
                            <li id="req-lower"><i class="bi bi-x-circle"></i> At least one lowercase letter</li>
                            <li id="req-number"><i class="bi bi-x-circle"></i> At least one digit</li>
                            <li id="req-special"><i class="bi bi-x-circle"></i> At least one special character</li>
                        </ul>
                    </div>

                    <!-- Confirm Password -->
                    <div class="mb-4">
                        <label for="password_confirm" class="form-label">Confirm New Password</label>
                        <input type="password"
                               class="form-control"
                               id="password_confirm"
                               name="password_confirm"
                               required
                               autocomplete="new-password">
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">Reset Password</button>
                    </div>
                </form>

                <div class="text-center mt-3">
                    <a href="/auth/login" class="text-decoration-none">Back to Login</a>
                </div>
            </div>
        </div>
    </div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
    <script>
        // Real-time password strength validation (same as registration)
        const passwordInput = document.getElementById('password');
        const requirements = {
            'req-length':  /.{8,}/,
            'req-upper':   /[A-Z]/,
            'req-lower':   /[a-z]/,
            'req-number':  /[0-9]/,
            'req-special': /[!@#$%^&*()_+\-=\[\]{};':\"\\|,.<>\/?]/
        };

        passwordInput.addEventListener('input', function() {
            const val = this.value;
            for (const [id, regex] of Object.entries(requirements)) {
                const el = document.getElementById(id);
                if (regex.test(val)) {
                    el.classList.add('met');
                    el.innerHTML = '<i class="bi bi-check-circle-fill"></i> ' + el.textContent;
                } else {
                    el.classList.remove('met');
                    el.innerHTML = '<i class="bi bi-x-circle"></i> ' + el.textContent;
                }
            }
        });

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