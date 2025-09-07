<?php

namespace App\Http\Controllers;


use App\Services\AdminService;

class AdminDashboardController extends Controller{
protected $adminService;


public function __construct()
{
    $this->adminService = new AdminService();
}

    public function dashboard(){

        $totalPackages = $this->adminService->getTotalPackages();
        return view('Dashboard.dashboard');
    }
}
