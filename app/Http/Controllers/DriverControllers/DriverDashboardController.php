<?php

namespace App\Http\Controllers\DriverControllers;

use App\Http\Controllers\Controller;
use App\Services\DriverServices\DriverDashboardService;
use Illuminate\Http\Request; 
use Illuminate\Support\Facades\Auth; 

class DriverDashboardController extends Controller
{
    protected DriverDashboardService $dashboardService;

    public function __construct(DriverDashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }


    public function dashboard()
    {
        $stats = $this->dashboardService->getDeliveryStats();
        $recentPackages = $this->dashboardService->getRecentPackages(5);
        $failedPackages = $this->dashboardService->getPackageCountByStatus('FAILED');
        $driver = $this->dashboardService->getDriverDetails();

        return view('DriverViews.dashboard', [
            'totalAssigned' => $stats['total_assigned'] ?? 0,
            'scheduled' => $stats['scheduled'] ?? 0,
            'inTransit' => $stats['in_transit'] ?? 0,
            'delivered' => $stats['delivered'] ?? 0,
            'failed' => $stats['failed'] ?? 0,
            'recentPackages' => $recentPackages,
            'driver' => $driver, // Pass the driver's details to the view
        ], compact('failedPackages'));
    }


    public function updateStatus(Request $request)
    {
        $currentStatus = $request->input('current_status');

        $newStatus = ($currentStatus === 'AVAILABLE') ? 'BUSY' : 'AVAILABLE';

        $this->dashboardService->updateDriverStatus($newStatus);

        return redirect()->route('driver.dashboard')->with('success', 'Your status has been updated to ' . $newStatus);
    }
}
