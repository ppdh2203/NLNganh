<?php

$router->get('/', '\App\Controllers\HomeController@index');

$router->get('/register', '\App\Controllers\RegisterController@showRegister');

$router->get('/login', '\App\Controllers\LoginController@showLogin');