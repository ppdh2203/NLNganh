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