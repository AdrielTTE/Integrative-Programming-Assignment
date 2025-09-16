<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;

class AdminPackageController extends Controller
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.api.base_url', 'http://localhost:8001/api');
    }

    /**
     * Show the form for creating a new package.
     */
    public function create()
    {
        $customers = Http::get("{$this->baseUrl}/customer")->throw()->json();
        return view('admin.packages.create', compact('customers'));
    }

    /**
     * Store a newly created package in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|string',
            'package_contents' => 'required|string|max:255',
            'package_weight' => 'required|numeric|min:0.1',
            'sender_address' => 'required|string|max:255',
            'recipient_address' => 'required|string|max:255',
            'priority' => 'required|string|in:standard,express,urgent',
        ]);

        try {
            // The API will handle generating the ID and tracking number
            Http::post("{$this->baseUrl}/package", $validated)->throw();
            return redirect()->route('admin.packages.assign')->with('success', 'Package created successfully and is ready for assignment.');
        } catch (RequestException $e) {
            // Return detailed error from API response
            return back()->with('error', 'Failed to create package: ' . $e->response->body())->withInput();
        }
    }

    /**
     * Show the form for assigning a driver to a package.
     */
    public function showAssignForm(string $packageId)
    {
        $package = Http::get("{$this->baseUrl}/package/getByPackageID/{$packageId}")->throw()->json();
        $driversResponse = Http::get("{$this->baseUrl}/deliveryDriver/getBatch/1/100/AVAILABLE")->throw()->json();
        $vehiclesResponse = Http::get("{$this->baseUrl}/vehicle")->throw()->json();

        // Filter for available vehicles
        $availableVehicles = collect($vehiclesResponse)->where('vehicle_status', 'AVAILABLE')->all();

        return view('admin.packages.assign-driver', [
            'package' => $package,
            'drivers' => $driversResponse,
            'vehicles' => $availableVehicles
        ]);
    }

    /**
     * Assign a driver to the specified package.
     */
    public function assignDriver(Request $request, string $packageId)
    {
        $validated = $request->validate([
            'driver_id' => 'required|string',
            'vehicle_id' => 'required|string', // Added vehicle validation
            'pickup_time' => 'required|date',
            'delivery_time' => 'required|date|after:pickup_time',
        ]);

        try {
            $deliveryId = 'DV' . str_pad(rand(10, 99999), 6, '0', STR_PAD_LEFT);

            $deliveryData = [
                'delivery_id' => $deliveryId,
                'package_id' => $packageId,
                'driver_id' => $validated['driver_id'],
                'vehicle_id' => $validated['vehicle_id'], // Added vehicle_id
                'delivery_status' => 'SCHEDULED',
                'pickup_time' => $validated['pickup_time'],
                'estimated_delivery_time' => $validated['delivery_time'],
            ];

            Http::post("{$this->baseUrl}/delivery", $deliveryData)->throw();

            Http::put("{$this->baseUrl}/package/{$packageId}", [
                'package_status' => 'PROCESSING'
            ])->throw();

            return redirect()->route('admin.search')->with('success', "Driver assigned successfully to package {$packageId}.");

        } catch (RequestException $e) {
            return back()->with('error', 'Failed to assign driver: ' . $e->response->body())->withInput();
        }
    }
}
