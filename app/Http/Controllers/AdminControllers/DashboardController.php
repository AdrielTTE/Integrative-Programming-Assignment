<?php



namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use App\Services\AdminServices\DashboardService;

class DashboardController extends Controller{
protected $adminService;


public function __construct()
{
    $this->adminService = new DashboardService();
}

    public function dashboard(){

        $totalPackages = $this->adminService->getTotalPackages();
        return view('AdminViews.Dashboard.dashboard');
    }
}
