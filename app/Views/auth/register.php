<section class="register-section">
    <div class="register-container">

        <h1>Đăng ký tài khoản</h1>

        <!-- Khu vực chứa 2 form -->
        <div class="register-forms">

            <!-- Form đăng ký Sinh viên -->
            <form id="student-form" action="/register" method="POST">
                <input type="hidden" name="role" value="student">

                <div>
                    <label for="student-full-name">Họ và tên</label>
                    <input type="text" id="student-full-name" name="full_name" placeholder="Nhập họ và tên của bạn" required>
                </div>

                <div>
                    <label for="student-email">Email</label>
                    <input type="email" id="student-email" name="email" placeholder="Nhập email của bạn" required>
                </div>

                <div>
                    <label for="student-school">Trường</label>
                    <input type="text" id="student-school" name="school" placeholder="Nhập trường của bạn" required>
                </div>

                <div>
                    <label for="student-major">Ngành học</label>
                    <input type="text" id="student-major" name="major" placeholder="Nhập ngành của bạn" required>
                </div>

                <div>
                    <label for="student-cohort">Khóa</label>
                    <input type="number" id="student-cohort" name="cohort" placeholder="Ví dụ: 49" required>
                </div>

                <div>
                    <label for="student-address">Địa chỉ</label>
                    <input type="text" id="student-address" name="address" placeholder="Nhập địa chỉ của bạn" required>
                </div>

                <div>
                    <label for="student-phone">Số điện thoại</label>
                    <input type="tel" id="student-phone" name="phone" placeholder="Nhập số điện thoại của bạn" required>
                </div>

                <div>
                    <label for="student-password">Mật khẩu</label>
                    <input type="password" id="student-password" name="password" placeholder="Nhập mật khẩu của bạn" required>
                </div>

                <div>
                    <label for="student-password-confirmation">Nhập lại mật khẩu</label>
                    <input type="password" id="student-password-confirmation" name="password_confirmation" placeholder="Nhập lại mật khẩu của bạn" required>
                </div>

                <button type="submit">Đăng ký</button>
            </form>

            <!-- Form đăng ký Nhà cung cấp -->
            <form id="provider-form" action="/register" method="POST" enctype="multipart/form-data" hidden>
                <input type="hidden" name="role" value="provider">

                <div>
                    <label for="provider-name">Tên nhà cung cấp</label>
                    <input type="text" id="provider-name" name="provider_name" required>
                </div>

                <div>
                    <label for="organization">Tổ chức</label>
                    <input type="text" id="organization" name="organization">
                </div>

                <div>
                    <label for="provider-email">Email đăng nhập</label>
                    <input type="email" id="provider-email" name="email" required>
                </div>

                <div>
                    <label for="contact-email">Email liên hệ</label>
                    <input type="email" id="contact-email" name="contact_email">
                </div>

                <div>
                    <label for="provider-phone">Số điện thoại</label>
                    <input type="tel" id="provider-phone" name="phone">
                </div>

                <div>
                    <label for="verification-document">Giấy tờ chứng minh tổ chức</label>
                    <input type="file"
                        id="verification-document"
                        name="verification_document"
                        accept=".pdf,.jpg,.jpeg,.png"
                        required>
                    <small>Chấp nhận PDF, JPG, JPEG hoặc PNG.</small>
                </div>

                <div>
                    <label for="provider-password">Mật khẩu</label>
                    <input type="password" id="provider-password" name="password" required>
                </div>

                <div>
                    <label for="provider-password-confirmation">Nhập lại mật khẩu</label>
                    <input type="password" id="provider-password-confirmation" name="password_confirmation" required>
                </div>

                <button type="submit">Đăng ký</button>
            </form>

        </div>

        <div class="register-switch">
            <button type="button" id="student-switch">Bạn là sinh viên?</button>
            <button type="button" id="provider-switch">Bạn là nhà cung cấp?</button>
        </div>
        <p>Đã có tài khoản? <a href="/login">Đăng nhập</a></p>
    </div>
</section>

<script>
    // JavaScript riêng của trang đăng ký nằm ở đây
    const studentForm = document.getElementById('student-form');
    const providerForm = document.getElementById('provider-form');
    const studentSwitch = document.getElementById('student-switch');
    const providerSwitch = document.getElementById('provider-switch');

    studentSwitch.addEventListener('click', function () {
        studentForm.hidden = false;
        providerForm.hidden = true;
    });

    providerSwitch.addEventListener('click', function () {
        studentForm.hidden = true;
        providerForm.hidden = false;
    });
</script>