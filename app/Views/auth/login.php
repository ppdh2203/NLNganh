<section class="login-section">
    <div class="login-container">

        <!-- Tiêu đề -->
        <div class="login-header">
            <h1>Welcome back</h1>
            <p>Log in to continue your scholarship journey.</p>
        </div>

        <form action="/login" method="POST" class="login-form">

            <div class="form-group">
                <label for="login-email">Email</label>
                <input type="email"
                       id="login-email"
                       name="email"
                       placeholder="Enter your email"
                       required>
            </div>

            <div class="form-group">
                <div class="login-password-label">
                    <label for="login-password">Password</label>
                    <a href="/forgot-password">Forgot password?</a>
                </div>

                <div class="password-field">
                    <input type="password"
                           id="login-password"
                           name="password"
                           placeholder="Enter your password"
                           required>

                    <button type="button"
                            class="password-toggle"
                            aria-label="Show password">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="login-submit">Log in</button>
        </form>

        <p class="login-register">
            Don't have an account?
            <a href="/register">Sign up</a>
        </p>

    </div>
</section>

<script>
    // Bật / tắt hiển thị mật khẩu
    const passwordToggle = document.querySelector('.password-toggle');
    const passwordInput = document.getElementById('login-password');

    passwordToggle.addEventListener('click', function () {
        const icon = passwordToggle.querySelector('i');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
            passwordToggle.setAttribute('aria-label', 'Hide password');
        } else {
            passwordInput.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
            passwordToggle.setAttribute('aria-label', 'Show password');
        }
    });
</script>