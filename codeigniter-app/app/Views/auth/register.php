<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

    <div class="auth-container">
        <div class="card auth-card">
            <div class="card-header text-center">
                <h4 class="mb-0">Create Your Account</h4>
                <p class="mb-0 mt-1 opacity-75">Join the Alumni Influencers Platform</p>
            </div>
            <div class="card-body">

                <!-- Validation Errors Display -->
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

                <!-- Registration Form -->
                <!-- CSRF token is automatically included by CodeIgniter's form helper -->
                <form action="/auth/register" method="post">
                    <?= csrf_field() ?>

                    <!-- Email Field -->
                    <div class="mb-3">
                        <label for="email" class="form-label">University Email Address</label>
                        <input type="email"
                               class="form-control"
                               id="email"
                               name="email"
                               value="<?= old('email') ?>"
                               placeholder="your.name@eastminster.ac.uk"
                               required
                               autocomplete="email">
                        <div class="form-text">
                            Only University of Eastminster email addresses are accepted.
                        </div>
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
                                   minlength="8"
                                   autocomplete="new-password">
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <!-- Password Requirements Checklist -->
                        <ul class="password-requirements mt-2 list-unstyled" id="passwordRequirements">
                            <li id="req-length"><i class="bi bi-x-circle"></i> At least 8 characters</li>
                            <li id="req-upper"><i class="bi bi-x-circle"></i> At least one uppercase letter</li>
                            <li id="req-lower"><i class="bi bi-x-circle"></i> At least one lowercase letter</li>
                            <li id="req-number"><i class="bi bi-x-circle"></i> At least one digit</li>
                            <li id="req-special"><i class="bi bi-x-circle"></i> At least one special character</li>
                        </ul>
                    </div>

                    <!-- Confirm Password Field -->
                    <div class="mb-4">
                        <label for="password_confirm" class="form-label">Confirm Password</label>
                        <input type="password"
                               class="form-control"
                               id="password_confirm"
                               name="password_confirm"
                               required
                               autocomplete="new-password">
                    </div>

                    <!-- Submit Button -->
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">Register</button>
                    </div>
                </form>

                <!-- Login Link -->
                <div class="text-center mt-3">
                    <p class="mb-0">Already have an account? <a href="/auth/login">Log in here</a></p>
                </div>
            </div>
        </div>
    </div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
    <script>
        // Real-time password strength validation feedback
        // Updates the checklist as the user types to show which requirements are met
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