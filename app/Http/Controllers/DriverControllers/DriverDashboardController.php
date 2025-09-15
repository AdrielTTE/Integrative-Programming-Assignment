<?php

namespace App\Http\Controllers\DriverControllers;

use App\Http\Controllers\Controller;


class DriverDashboardController extends Controller
{
    public function dashboard()
    {
        return view('DriverViews.dashboard');
    }
}