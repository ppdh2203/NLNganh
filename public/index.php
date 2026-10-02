<?php

// Nạp Composer autoload để PHP tự tìm các class trong project
require_once __DIR__ . '/../vendor/autoload.php';

use Bramus\Router\Router;

$router = new Router();

require_once 