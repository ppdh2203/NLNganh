<!-- Thanh điều hướng phụ phía trên Header -->
<div class="top-bar">
    <div class="container">
        <div class="top-bar-links">
            <button type="button" class="top-role-btn active" id="student-header-btn">For Students</button>
            <button type="button" class="top-role-btn" id="provider-header-btn">For Scholarship Providers</button>
        </div>
    </div>
</div>

<header class="site-header">
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <!-- Tên website -->
            <a class="navbar-brand" href="/">
                <img src="/assets/images/logo.png" alt="Scholarship Logo" class="site-logo-icon">
                <span>Scholarship</span>
            </a>

            <!-- Nút menu khi màn hình nhỏ -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#main-navbar" aria-controls="main-navbar" aria-expanded="false" aria-label="Open menu">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="main-navbar">
                <!-- Menu sẽ được JavaScript thay đổi -->
                <ul class="navbar-nav mx-auto" id="main-menu"></ul>

                <!-- Khu vực tài khoản -->
                <div class="header-actions">
                    <a class="btn-login" href="/login">Log in</a>
                    <a class="btn-register" href="/register">Sign up</a>
                </div>
            </div>
        </div>
    </nav>
</header>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Lấy các phần tử cần thay đổi trên Header
        const mainMenu = document.getElementById('main-menu');
        const studentHeaderBtn = document.getElementById('student-header-btn');
        const providerHeaderBtn = document.getElementById('provider-header-btn');

        // Lấy khu vực How It Works nếu đang ở trang Home
        const howSteps = document.getElementById('how-steps');

        // Menu dành cho sinh viên
        const studentMenu = `
            <li class="nav-item"><a class="nav-link" href="/">Home</a></li>
            <li class="nav-item"><a class="nav-link" href="/scholarships">Scholarships</a></li>
            <li class="nav-item"><a class="nav-link" href="/about">About Us</a></li>
            <li class="nav-item"><a class="nav-link" href="/contact">Contact</a></li>
        `;

        // Menu dành cho nhà cung cấp học bổng
        const providerMenu = `
            <li class="nav-item"><a class="nav-link" href="/">Home</a></li>
            <li class="nav-item"><a class="nav-link" href="/scholarships">Scholarships</a></li>
            <li class="nav-item"><a class="nav-link" href="/how-it-works">How It Works</a></li>
            <li class="nav-item"><a class="nav-link" href="/about">About Us</a></li>
        `;

        // Các bước dành cho sinh viên
        const studentSteps = `
            <div class="how-step">
                <div class="how-step-icon">
                    <i class="bi bi-person-plus"></i>
                </div>
                <h3>Create an Account</h3>
                <p>Create your student account to get started.</p>
            </div>

            <div class="how-step">
                <div class="how-step-icon">
                    <i class="bi bi-search"></i>
                </div>
                <h3>Find Scholarships</h3>
                <p>Explore scholarship opportunities that suit you.</p>
            </div>

            <div class="how-step">
                <div class="how-step-icon">
                    <i class="bi bi-file-earmark-check"></i>
                </div>
                <h3>Submit Application</h3>
                <p>Complete and submit your scholarship application.</p>
            </div>
        `;

        // Các bước dành cho nhà cung cấp học bổng
        const providerSteps = `
            <div class="how-step">
                <div class="how-step-icon">
                    <i class="bi bi-building-add"></i>
                </div>
                <h3>Create an Account</h3>
                <p>Register your organization as a scholarship provider.</p>
            </div>

            <div class="how-step">
                <div class="how-step-icon">
                    <i class="bi bi-mortarboard"></i>
                </div>
                <h3>Create Scholarships</h3>
                <p>Create scholarship opportunities for students.</p>
            </div>

            <div class="how-step">
                <div class="how-step-icon">
                    <i class="bi bi-people"></i>
                </div>
                <h3>Review Applications</h3>
                <p>Review and evaluate submitted student applications.</p>
            </div>
        `;

        // Lấy giao diện đã chọn trước đó, nếu chưa có thì mặc định là Student
        const selectedRole = sessionStorage.getItem('publicRole') || 'student';

        // Hiển thị đúng giao diện đã chọn khi trang được tải
        if (selectedRole === 'provider') {
            mainMenu.innerHTML = providerMenu;

            if (howSteps) {
                howSteps.innerHTML = providerSteps;
            }

            providerHeaderBtn.classList.add('active');
            studentHeaderBtn.classList.remove('active');
        } else {
            mainMenu.innerHTML = studentMenu;

            if (howSteps) {
                howSteps.innerHTML = studentSteps;
            }

            studentHeaderBtn.classList.add('active');
            providerHeaderBtn.classList.remove('active');
        }

        // Khi chọn giao diện Student
        studentHeaderBtn.addEventListener('click', function () {
            // Ghi nhớ lựa chọn Student
            sessionStorage.setItem('publicRole', 'student');

            mainMenu.innerHTML = studentMenu;

            // Nếu đang ở Home thì đổi How It Works sang Student
            if (howSteps) {
                howSteps.innerHTML = studentSteps;
            }

            studentHeaderBtn.classList.add('active');
            providerHeaderBtn.classList.remove('active');
        });

        // Khi chọn giao diện Provider
        providerHeaderBtn.addEventListener('click', function () {
            // Ghi nhớ lựa chọn Provider
            sessionStorage.setItem('publicRole', 'provider');

            mainMenu.innerHTML = providerMenu;

            // Nếu đang ở Home thì đổi How It Works sang Provider
            if (howSteps) {
                howSteps.innerHTML = providerSteps;
            }

            providerHeaderBtn.classList.add('active');
            studentHeaderBtn.classList.remove('active');
        });
    });
</script>