<section class="register-section">
    <div class="register-container">
        <!-- Tiêu đề -->
        <div class="register-header">
            <h1>Create your account</h1>
            <p>Start your scholarship journey today.</p>
        </div>

        <div class="register-forms">
            <!-- =========================
                 FORM STUDENT
            ========================== -->
            <form id="student-form" class="register-form" action="/register" method="POST">
                <input type="hidden" name="role" value="student">

                <div class="form-group">
                        <label for="student-full-name">Full name</label>
                        <input type="text" id="student-full-name" name="full_name" placeholder="Enter your full name" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="student-address">Address</label>
                        <input type="text" id="student-address" name="address" placeholder="Enter your address" required>
                    </div>

                    <div class="form-group">
                        <label for="student-email">Email</label>
                        <input type="email" id="student-email" name="email" placeholder="Enter your email" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="student-school">School</label>
                        <input type="text" id="student-school" name="school" placeholder="Enter your school" required>
                    </div>

                    <div class="form-group">
                        <label for="student-major">Major</label>
                        <input type="text" id="student-major" name="major" placeholder="Enter your major" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="student-cohort">Cohort</label>
                        <input type="number" id="student-cohort" name="cohort" placeholder="Example: 49" required>
                    </div>

                    <div class="form-group">
                        <label for="student-phone">Phone number</label>
                        <input type="tel" id="student-phone" name="phone" placeholder="Enter your phone number" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="student-password">Password</label>
                        <div class="password-field">
                            <input type="password" id="student-password" name="password" placeholder="Enter your password" required>
                            <button type="button" class="password-toggle" aria-label="Show password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="student-password-confirmation">Confirm password</label>
                        <div class="password-field">
                        <input type="password" id="student-password-confirmation" name="password_confirmation" placeholder="Enter your password again" required>
                        <button type="button" class="password-toggle" aria-label="Show password">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>

                    </div>
                </div>

                <button type="submit" class="register-submit">Create account</button>
            </form>

            <!-- =========================
                 FORM PROVIDER
            ========================== -->
            <form id="provider-form" class="register-form" action="/register" method="POST" enctype="multipart/form-data" hidden>
                <input type="hidden" name="role" value="provider">

                <div class="form-row">
                    <div class="form-group">
                        <label for="provider-name">Provider name</label>
                        <input type="text" id="provider-name" name="provider_name" placeholder="Enter provider name" required>
                    </div>

                    <div class="form-group">
                        <label for="organization">Organization</label>
                        <input type="text" id="organization" name="organization" placeholder="Enter organization name">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="provider-email">Login email</label>
                        <input type="email" id="provider-email" name="email" placeholder="Enter your email" required>
                    </div>

                    <div class="form-group">
                        <label for="contact-email">Contact email</label>
                        <input type="email" id="contact-email" name="contact_email" placeholder="Enter contact email">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="provider-phone">Phone number</label>
                        <input type="tel" id="provider-phone" name="phone" placeholder="Enter phone number">
                    </div>

                    <div class="form-group">
                        <label for="verification-document">Verification document</label>
                        <input type="file"
                               id="verification-document"
                               name="verification_document"
                               accept=".pdf,.jpg,.jpeg,.png"
                               required>
                        <small>PDF, JPG, JPEG or PNG.</small>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="provider-password">Password</label>
                        <input type="password" id="provider-password" name="password" placeholder="Enter your password" required>
                    </div>

                    <div class="form-group">
                        <label for="provider-password-confirmation">Confirm password</label>
                        <input type="password" id="provider-password-confirmation" name="password_confirmation" placeholder="Enter your password again" required>
                    </div>
                </div>

                <button type="submit" class="register-submit">Create account</button>
            </form>
        </div>

        <!-- Chuyển Student / Provider -->
        <div class="register-switch">
            <button type="button" class="register-switch-btn active" id="student-switch">
                <i class="bi bi-mortarboard"></i>
                Student
            </button>

            <button type="button" class="register-switch-btn" id="provider-switch">
                <i class="bi bi-building"></i>
                Scholarship Provider
            </button>
        </div>

        <p class="register-login">
            Already have an account?
            <a href="/login">Log in</a>
        </p>
    </div>
</section>

<script>
    // Lấy form và nút chuyển loại tài khoản
    const studentForm = document.getElementById('student-form');
    const providerForm = document.getElementById('provider-form');
    const studentSwitch = document.getElementById('student-switch');
    const providerSwitch = document.getElementById('provider-switch');

    // Lấy role được truyền từ URL: /register?role=student hoặc provider
    const params = new URLSearchParams(window.location.search);
    const role = params.get('role');

    // Nếu chọn Provider từ Home thì mở sẵn form Provider
    if (role === 'provider') {
        studentForm.hidden = true;
        providerForm.hidden = false;

        studentSwitch.classList.remove('active');
        providerSwitch.classList.add('active');
    }

    // Hiển thị form Student
    studentSwitch.addEventListener('click', function () {
        studentForm.hidden = false;
        providerForm.hidden = true;

        studentSwitch.classList.add('active');
        providerSwitch.classList.remove('active');
    });

    // Hiển thị form Provider
    providerSwitch.addEventListener('click', function () {
        studentForm.hidden = true;
        providerForm.hidden = false;

        providerSwitch.classList.add('active');
        studentSwitch.classList.remove('active');
    });

    // Bật / tắt hiển thị mật khẩu
    const passwordToggles = document.querySelectorAll('.password-toggle');

    passwordToggles.forEach(function (button) {
        button.addEventListener('click', function () {
            const input = button.parentElement.querySelector('input');
            const icon = button.querySelector('i');

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
                button.setAttribute('aria-label', 'Hide password');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
                button.setAttribute('aria-label', 'Show password');
            }
        });
    });
</script>
