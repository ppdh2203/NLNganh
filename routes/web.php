<?php

$router->get('/', '\App\Controllers\HomeController@index');

$router->get('/register', '\App\Controllers\RegisterController@showRegister');

$router->get('/login', '\App\Controllers\LoginController@showLogin');

$router->get('/about', '\App\Controllers\AboutController@index');

$router->get('/contact', '\App\Controllers\ContactController@index');

$router->get('/how-it-works', '\App\Controllers\HowItWorksController@index');


// Xử lý khi người dùng truy cập đường dẫn không tồn tại
$router->set404(function () {
    $controller = new \App\Controllers\Controller();
    $controller->sendNotFound();
});