<?php

namespace App\Http\Controllers\DriverControllers;

use App\Http\Controllers\Controller;
use App\Services\DriverServices\DriverDashboardService;
use Illuminate\Http\Request; // <-- IMPORT THE REQUEST CLASS
use Illuminate\Support\Facades\Auth; // <-- IMPORT THE AUTH FACADE

class DriverDashboardController extends Controller
{
    protected DriverDashboardService $dashboardService;

    public function __construct(DriverDashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Show the driver's dashboard with their stats and details.
     */
    public function dashboard()
    {
        $stats = $this->dashboardService->getDeliveryStats();
        $recentPackages = $this->dashboardService->getRecentPackages(5);
        
        // --- NEW: Fetch the driver's own details ---
        $driver = $this->dashboardService->getDriverDetails();

        return view('DriverViews.dashboard', [
            'totalAssigned' => $stats['total_assigned'] ?? 0,
            'scheduled' => $stats['scheduled'] ?? 0,
            'inTransit' => $stats['in_transit'] ?? 0,
            'delivered' => $stats['delivered'] ?? 0,
            'failed' => $stats['failed'] ?? 0,
            'recentPackages' => $recentPackages,
            'driver' => $driver, // Pass the driver's details to the view
        ]);
    }

    // --- NEW METHOD TO ADD ---
    /**
     * Handle the request to toggle the driver's status.
     */
    public function updateStatus(Request $request)
    {
        $currentStatus = $request->input('current_status');
        
        // Determine what the new status should be.
        $newStatus = ($currentStatus === 'AVAILABLE') ? 'BUSY' : 'AVAILABLE';
        
        // Use the service to update the status in the database.
        $this->dashboardService->updateDriverStatus($newStatus);
        
        return redirect()->route('driver.dashboard')->with('success', 'Your status has been updated to ' . $newStatus);
    }
}