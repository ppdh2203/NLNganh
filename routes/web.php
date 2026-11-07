<?php

$router->get('/', '\App\Controllers\HomeController@index');

$router->get('/register', '\App\Controllers\RegisterController@showRegister');
$router->post('/register', '\App\Controllers\RegisterController@register');


$router->get('/login', '\App\Controllers\LoginController@showLogin');
$router->post('/login', '\App\Controllers\LoginController@login');
$router->post('/logout', '\App\Controllers\LoginController@logout');

$router->get('/about', '\App\Controllers\AboutController@index');

$router->get('/contact', '\App\Controllers\ContactController@index');

$router->get('/how-it-works', '\App\Controllers\HowItWorksController@index');


// Danh sách học bổng
$router->get('/scholarships', '\App\Controllers\ScholarshipController@index');
// Chi tiết học bổng
$router->get('/scholarships/{id:\d+}', '\App\Controllers\ScholarshipController@show');


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
    $controller = new \App\Controllers\Controller();
    $controller->sendNotFound();
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
