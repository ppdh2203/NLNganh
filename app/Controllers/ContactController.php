<?php

declare(strict_types=1);

namespace App\Controllers;

class ContactController extends Controller
{
    public function index(): void
    {
       $this->view('contact');
    }
}