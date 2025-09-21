<?php

namespace App\Http\Controllers\DriverControllers;

use App\Http\Controllers\Controller;
use App\Services\DriverPackageService;
use App\Factories\Driver\UpdateStatusViewFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeliveryStatusController extends Controller
{
    protected DriverPackageService $packageService;

    public function __construct(DriverPackageService $packageService)
    {
        $this->packageService = $packageService;
    }

    /**
     * Show the status update page
     */
    public function index()
    {
        $factory = new UpdateStatusViewFactory($this->packageService);
        return $factory->render();
    }

    /**
     * Update package status
     */
    public function update(Request $request, string $packageId)
    {
        $request->validate([
            'status' => 'required|string|in:IN_TRANSIT,DELIVERED,FAILED'
        ]);

        $driverId = Auth::user()->user_id;
        $status = $request->input('status');

        // If status is DELIVERED, redirect to proof form
        if ($status === 'DELIVERED') {
            return redirect()->route('driver.proof.create', $packageId);
        }

        // For other statuses, update directly
        try {
            $success = $this->packageService->updatePackageStatus($packageId, $status, $driverId);

            if ($success) {
                return redirect()->route('driver.status.index')
                    ->with('success', "Package {$packageId} status updated to {$status}");
            } else {
                return back()->with('error', 'Failed to update package status');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}