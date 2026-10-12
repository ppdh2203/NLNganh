<?php

declare(strict_types=1);

namespace App\Controllers;
use App\Models\Scholarship;


class HomeController extends Controller
{
    public function index(): void
    {
        $scholarships = Scholarship::getPublished(3);
        $this->view('home', [
            'scholarships' => $scholarships
        ]);
    }
}