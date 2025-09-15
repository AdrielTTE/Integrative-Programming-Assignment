<?php

namespace App\Http\Controllers\DriverControllers;

use App\Http\Controllers\Controller;


class AssignedPackageController extends Controller
{
    public function assignedPackages()
    {
        return view('DriverViews.assignedPackages');
    }
}