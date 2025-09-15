<?php



namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use App\Services\AdminServices\DashboardService;
use App\Services\Api\DeliveryDriverService;
use App\Services\Api\PackageService;
use App\Services\Api\DeliveryService;
use Illuminate\Http\Request;

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

    public function dashboard(Request $request){


         // Use session to persist filter (default 'all')
    $displayData = $request->input('displayData', session('displayData', 'packages'));
    session(['displayData' => $displayData]);

    // Keep these as internal defaults
    $page = $request->input('page', 1);
    $pageSize = $request->input('pageSize', 10);
    $driverStatus = $request->input('driverStatus', 'all');

        $totalPackages = $this->dashboardService->getTotalPackages();
        $totalAvailableDrivers = $this->dashboardService->getDriverCountByStatus("AVAILABLE");
        $totalDeliveries = $this->dashboardService->getTotalDeliveries();
        $totalCompletedDeliveries = $this->dashboardService->getDeliveryCountByStatus("DELIVERED");
        $totalInTransitDeliveries = $this->dashboardService->getDeliveryCountByStatus("IN_TRANSIT");
        $totalFailedDeliveries = $this->dashboardService->getDeliveryCountByStatus("FAILED");
        $totalPickedUpDeliveries = $this->dashboardService->getDeliveryCountByStatus("PICKED_UP");
        $totalScheduledDeliveries = $this->dashboardService->getDeliveryCountByStatus("SCHEDULED");
        $recentPackages = $this->dashboardService->recentPackages(5);
        $driverList = $this->dashboardService->getDrivers($page, $pageSize, $driverStatus);

        switch($displayData){
            case "packages":
                $dataForGraph = $this->dashboardService->getPackageCountByStatus("all");
                break;

            case "deliveries":
                $dataForGraph = $this->dashboardService->getDeliveryCountByStatus("all");
                break;

            case "vehicles":
                $dataForGraph = $this->dashboardService->getVehicleCountByStatus("all");
                break;

            default:
                $dataForGraph = $this->dashboardService->getCustomerCountByStatus("all");
                break;

        }





        return view('AdminViews.Dashboard.dashboard', compact('totalPackages',    'totalAvailableDrivers', 'totalDeliveries', 'totalCompletedDeliveries', 'totalInTransitDeliveries', 'totalFailedDeliveries', 'recentPackages', 'driverList', 'dataForGraph', 'displayData', 'totalPickedUpDeliveries', 'totalScheduledDeliveries'));
    }
}
