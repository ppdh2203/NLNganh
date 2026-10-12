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

// Xử lý khi người dùng truy cập đường dẫn không tồn tại
$router->set404(function () {
    $controller = new \App\Controllers\Controller();
    $controller->sendNotFound();
});