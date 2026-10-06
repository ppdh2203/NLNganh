<?php

declare(strict_types=1);

namespace App\Controllers;

class HowItWorksController extends Controller
{
    public function index(): void
    {
        $this->view('how-it-works');
    }
}