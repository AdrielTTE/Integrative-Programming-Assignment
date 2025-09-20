<?php

namespace App\Http\Controllers\DriverControllers;

use App\Http\Controllers\Controller;
use App\Factories\Driver\UpdateStatusViewFactory;
use App\Services\DriverPackageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DeliveryStatusController extends Controller
{
    protected DriverPackageService $packageService;

    public function __construct(DriverPackageService $packageService)
    {
        $this->packageService = $packageService;
    }

    /**
     * Display packages using Factory Method pattern
     */
    public function index()
    {
        // Create factory instance
        $factory = new UpdateStatusViewFactory($this->packageService);
        
        // Use factory to render view
        return $factory->render();
    }

    /**
     * Update package status
     */
    public function update(Request $request, string $packageId)
    {
        $validated = $request->validate([
            'status' => 'required|in:PICKED_UP,IN_TRANSIT,DELIVERED,FAILED'
        ]);

        $driverId = Auth::user()->user_id;

        // Security check - verify driver owns package
        $authorized = DB::table('delivery')
            ->where('package_id', $packageId)
            ->where('driver_id', $driverId)
            ->exists();

        if (!$authorized) {
            return redirect()->route('driver.status.index')
                ->with('error', 'You are not authorized to update this package.');
        }

        // Update using service
        $success = $this->packageService->updatePackageStatus(
            $packageId,
            $validated['status'],
            $driverId
        );

        if ($success) {
            return redirect()->route('driver.status.index')
                ->with('success', 'Package status updated successfully!');
        }

        return redirect()->route('driver.status.index')
            ->with('error', 'Failed to update package status.');
    }
}