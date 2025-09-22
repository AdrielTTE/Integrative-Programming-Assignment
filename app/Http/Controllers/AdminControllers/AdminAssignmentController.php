<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Delivery;
use App\Models\DeliveryDriver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminAssignmentController extends Controller
{
    /**
     * Display a list of all packages that are 'PENDING' and need assignment.
     */
    public function index()
    {
        // Fetches all packages with the status 'PENDING' that don't have a delivery record yet.
        $packages = Package::with('user') // Changed from 'customer' to 'user'
            ->where('package_status', 'processing')
            ->whereDoesntHave('delivery')
            ->orderBy('created_at', 'asc')
            ->paginate(15);

        // Fetches all drivers who are currently 'AVAILABLE' to populate the dropdowns.
        $drivers = DeliveryDriver::where('driver_status', 'AVAILABLE')->get();

        return view('admin.assignments.index', compact('packages', 'drivers'));
    }

    /**
     * Assign a selected driver to a specific package.
     */
    public function assign(Request $request, string $packageId)
    {
        // 1. Basic validation to ensure a driver was selected.
        $request->validate([
            'driver_id' => 'required|string|exists:deliverydriver,driver_id',
        ]);

        // 2. Use a Database Transaction to ensure all steps succeed or none do.
        try {
            DB::transaction(function () use ($request, $packageId) {
                $package = Package::findOrFail($packageId);
                $driver = DeliveryDriver::findOrFail($request->input('driver_id'));

                // Create a new Delivery record to link the package and driver.
                Delivery::create([
                    'delivery_id' => 'DV' . strtoupper(substr(uniqid(), 7, 6)), // A unique ID
                    'package_id' => $package->package_id,
                    'driver_id' => $driver->driver_id,
                    'vehicle_id' => 'WQQ 7865', // Hardcoded placeholder as requested
                    'delivery_status' => 'SCHEDULED',
                    'pickup_time' => Carbon::now(),
                    'estimated_delivery_time' => Carbon::now()->addDays(2),
                ]);

                // Update the package status from 'PENDING' to 'PROCESSING'.
                $package->update(['package_status' => 'PROCESSING']);

                // Update the driver's status to 'BUSY'.  $driver->update(['driver_status' => 'BUSY']);
            });

        } catch (\Exception $e) {
            // If anything goes wrong, redirect back with a clear error message.
            return redirect()->route('admin.assignments.index')->with('error', 'Failed to assign package: ' . $e->getMessage());
        }

        // 3. If everything is successful, redirect back with a success message.
        return redirect()->route('admin.assignments.index')->with('success', "Package {$packageId} has been assigned successfully!");
    }
}