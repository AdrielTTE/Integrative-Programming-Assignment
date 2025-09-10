<?php



namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use App\Services\AdminServices\DashboardService;
use App\Services\Api\DeliveryDriverService;
use App\Services\Api\PackageService;
use App\Services\Api\DeliveryService;

class DashboardController extends Controller{
protected DashboardService $dashboardService;
protected PackageService $packageService;
protected DeliveryDriverService $deliveryDriverService;

public function __construct(PackageService $packageService, DeliveryDriverService $deliveryDriverService, DeliveryService $deliveryService)
{
    $this->dashboardService = new DashboardService(
        $packageService,
        $deliveryDriverService,
        $deliveryService


    );
}

    public function dashboard(){

        $totalPackages = $this->dashboardService->getTotalPackages();
        $totalAvailableDrivers = $this->dashboardService->getDriverCountByStatus("AVAILABLE");
        $totalDeliveries = $this->dashboardService->getTotalDeliveries();
        $totalCompletedDeliveries = $this->dashboardService->getDeliveryCountByStatus("DELIVERED");
        $totalInTransitDeliveries = $this->dashboardService->getDeliveryCountByStatus("IN_TRANSIT");
        $totalFailedDeliveries = $this->dashboardService->getDeliveryCountByStatus("FAILED");
        $recentPackages = $this->dashboardService->recentPackages(5);
        return view('AdminViews.Dashboard.dashboard', compact('totalPackages',    'totalAvailableDrivers', 'totalDeliveries', 'totalCompletedDeliveries', 'totalInTransitDeliveries', 'totalFailedDeliveries', 'recentPackages'));
    }
}
