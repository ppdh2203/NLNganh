<?php

declare(strict_types=1);

namespace App\Controllers;

class LoginController extends Controller
{
    public function showLogin(): void
    {
       $this->view('auth/login');
    }
}