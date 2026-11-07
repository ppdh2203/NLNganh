<?php
$router->get('/', '\App\Controllers\HomeController@index');
// Xem tài liệu PDF của hồ sơ đã nộp
$router->get(
    '/my-applications/(\d+)/documents/(\d+)',
    '\App\Controllers\ApplicationController@viewDocument'
);

$router->get('/register', '\App\Controllers\RegisterController@showRegister');
$router->post('/register', '\App\Controllers\RegisterController@register');


$router->get('/login', '\App\Controllers\LoginController@showLogin');
$router->post('/login', '\App\Controllers\LoginController@login');
$router->post('/logout', '\App\Controllers\LoginController@logout');

$router->get('/about', '\App\Controllers\AboutController@index');

$router->get('/contact', '\App\Controllers\ContactController@index');

$router->get('/how-it-works', '\App\Controllers\HowItWorksController@index');


// Danh sách học bổng

// ================= SCHOLARSHIPS =================

// Danh sách học bổng
$router->get(
    '/scholarships',
    '\App\Controllers\ScholarshipController@index'
);

// Hiển thị form nộp hồ sơ
$router->get(
    '/scholarships/(\d+)/apply',
    '\App\Controllers\ApplicationController@create'
);

// Xử lý nộp hồ sơ
$router->post(
    '/scholarships/(\d+)/apply',
    '\App\Controllers\ApplicationController@store'
);

// Chi tiết học bổng
$router->get(
    '/scholarships/(\d+)',
    '\App\Controllers\ScholarshipController@show'
);


$router->get('/admin', '\App\Controllers\AdminController@index');
$router->post('/admin/accounts/toggle', '\App\Controllers\AdminController@toggleAccountStatus');
$router->get(
    '/admin/providers/{id:\d+}/document',
    '\App\Controllers\AdminController@viewProviderDocument'
);
$router->post(
    '/admin/providers/review',
    '\App\Controllers\AdminController@reviewProvider'
);
$router->post(
    '/admin/scholarships/review',
    '\App\Controllers\AdminController@reviewScholarship'
);




// Xử lý khi người dùng truy cập đường dẫn không tồn tại
$router->set404(function () {
    header('Content-Type: text/plain; charset=utf-8');

    echo "ROUTE KHONG KHOP\n";
    echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? '') . "\n";
    echo "REQUEST_METHOD: " . ($_SERVER['REQUEST_METHOD'] ?? '') . "\n";
    echo "SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? '') . "\n";
    exit;
});


// ================= PROVIDER =================

// Trang tổng quan
$router->get(
    '/provider',
    '\App\Controllers\ProviderController@index'
);

// Danh sách học bổng của Provider
$router->get(
    '/provider/scholarships',
    '\App\Controllers\ProviderController@scholarships'
);

// Trang tạo học bổng
$router->get(
    '/provider/scholarships/create',
    '\App\Controllers\ProviderController@createScholarship'
);

// Xử lý đăng học bổng
$router->post(
    '/provider/scholarships/create',
    '\App\Controllers\ProviderController@storeScholarship'
);


$router->get(
    '/provider/scholarships/(\d+)/edit',
    '\App\Controllers\ProviderController@editScholarship'
);

$router->post(
    '/provider/scholarships/(\d+)/edit',
    '\App\Controllers\ProviderController@updateScholarship'
);

$router->get(
    '/provider/scholarships/(\d+)',
    '\App\Controllers\ProviderController@showScholarship'
);

// Xem thông tin nhà cung cấp
$router->get(
    '/provider/profile',
    '\App\Controllers\ProviderController@profile'
);

// Cập nhật thông tin nhà cung cấp
$router->post(
    '/provider/profile',
    '\App\Controllers\ProviderController@updateProfile'
);

// Trang cài đặt tài khoản Provider
$router->get(
    '/provider/settings',
    '\App\Controllers\ProviderController@settings'
);

// Xử lý đổi mật khẩu Provider
$router->post(
    '/provider/settings',
    '\App\Controllers\ProviderController@updatePassword'
);

// Student
//STudent

// ================= STUDENT =================

// Xem thông tin cá nhân
$router->get(
    '/profile',
    '\App\Controllers\StudentController@profile'
);

// Cập nhật thông tin cá nhân
$router->post(
    '/profile',
    '\App\Controllers\StudentController@updateProfile'
);


$router->get(
    '/saved-scholarships',
    '\App\Controllers\SavedScholarshipController@index'
);

$router->post(
    '/scholarships/{id:\d+}/save',
    '\App\Controllers\SavedScholarshipController@save'
);

$router->post(
    '/scholarships/{id:\d+}/unsave',
    '\App\Controllers\SavedScholarshipController@remove'
);



$router->get(
    '/settings',
    '\App\Controllers\StudentController@settings'
);

$router->post(
    '/settings',
    '\App\Controllers\StudentController@updatePassword'
);

// Hiển thị form nộp hồ sơ
$router->get(
    '/scholarships/{id:\d+}/apply',
    '\App\Controllers\ApplicationController@create'
);

// Xử lý nộp hồ sơ
$router->post(
    '/scholarships/{id:\d+}/apply',
    '\App\Controllers\ApplicationController@store'
);

// Danh sách hồ sơ đã nộp
$router->get(
    '/my-applications',
    '\App\Controllers\ApplicationController@index'
);

// Chi tiết hồ sơ đã nộp
$router->get(
    '/my-applications/{id:\d+}',
    '\App\Controllers\ApplicationController@show'
);


