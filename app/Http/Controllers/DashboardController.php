<?php
// app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the main dashboard view.
     */
    public function index()
    {
        return view('dashboard');
    }
}
