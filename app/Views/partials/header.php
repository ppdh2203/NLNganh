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
    // Lấy các phần tử cần thay đổi trên Header
    const mainMenu = document.getElementById('main-menu');
    const studentHeaderBtn = document.getElementById('student-header-btn');
    const providerHeaderBtn = document.getElementById('provider-header-btn');

    // Menu dành cho người xem là sinh viên
    const studentMenu = `
        <li class="nav-item"><a class="nav-link" href="/">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="/scholarships">Scholarships</a></li>
        <li class="nav-item"><a class="nav-link" href="/about">About Us</a></li>
        <li class="nav-item"><a class="nav-link" href="/contact">Contact</a></li>
    `;

    // Menu dành cho người xem là nhà cung cấp học bổng
    const providerMenu = `
        <li class="nav-item"><a class="nav-link" href="/">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="/scholarships">Scholarships</a></li>
        <li class="nav-item"><a class="nav-link" href="/how-it-works">How It Works</a></li>
        <li class="nav-item"><a class="nav-link" href="/about">About Us</a></li>
    `;

    // Mặc định hiển thị giao diện dành cho sinh viên
    mainMenu.innerHTML = studentMenu;

    studentHeaderBtn.addEventListener('click', function () {
        mainMenu.innerHTML = studentMenu;

        // Đổi trạng thái nút đang được chọn
        studentHeaderBtn.classList.add('active');
        providerHeaderBtn.classList.remove('active');
    });

    providerHeaderBtn.addEventListener('click', function () {
        mainMenu.innerHTML = providerMenu;

        // Đổi trạng thái nút đang được chọn
        providerHeaderBtn.classList.add('active');
        studentHeaderBtn.classList.remove('active');
    });
</script>