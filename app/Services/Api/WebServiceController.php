<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PackageService;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WebServiceController extends Controller
{
    protected PackageService $packageService;

    public function __construct(PackageService $packageService)
    {
        $this->packageService = $packageService;
    }

    public function createPackageExternal(Request $request)
    {
        try {
            $validated = $request->validate([
                'api_key' => 'required|string',
                'external_id' => 'required|string',
                'user_id' => 'required|string|exists:user,user_id',
                'sender_address' => 'required|string',
                'recipient_address' => 'required|string',
                'package_weight' => 'nullable|numeric',
                'package_dimensions' => 'nullable|string',
                'priority' => 'nullable|in:standard,express,urgent'
            ]);

            if (!$this->verifyApiKey($validated['api_key'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid API key'
                ], Response::HTTP_UNAUTHORIZED);
            }

            $packageData = array_merge($validated, [
                'notes' => "External ID: {$validated['external_id']}"
            ]);
            unset($packageData['api_key'], $packageData['external_id']);

            $package = $this->packageService->createPackage($packageData);

            return response()->json([
                'success' => true,
                'data' => [
                    'package_id' => $package->package_id,
                    'tracking_number' => $package->tracking_number,
                    'status' => $package->package_status,
                    'estimated_delivery' => $package->estimated_delivery,
                    'api_tracking_url' => url("/api/v1/packages/track/{$package->tracking_number}")
                ],
                'message' => 'Package created successfully'
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating package',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function updatePackageStatusExternal(Request $request, $packageId)
    {
        try {
            $validated = $request->validate([
                'api_key' => 'required|string',
                'action' => 'required|in:process,cancel,assign,deliver',
                'driver_id' => 'nullable|string|exists:user,user_id',
                'proof_data' => 'nullable|array'
            ]);

            if (!$this->verifyApiKey($validated['api_key'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid API key'
                ], Response::HTTP_UNAUTHORIZED);
            }

            $package = Package::find($packageId);
            if (!$package) {
                return response()->json([
                    'success' => false,
                    'message' => 'Package not found'
                ], Response::HTTP_NOT_FOUND);
            }

            switch ($validated['action']) {
                case 'process':
                    $updatedPackage = $this->packageService->processPackage($packageId);
                    break;
                case 'cancel':
                    $updatedPackage = $this->packageService->cancelPackage($packageId);
                    break;
                case 'assign':
                    if (!$validated['driver_id']) {
                        throw new \Exception('Driver ID is required for assignment');
                    }
                    $updatedPackage = $this->packageService->assignPackage($packageId, $validated['driver_id']);
                    break;
                case 'deliver':
                    $updatedPackage = $this->packageService->deliverPackage($packageId, $validated['proof_data'] ?? []);
                    break;
            }

            return response()->json([
                'success' => true,
                'data' => $updatedPackage->getFormattedDetails(),
                'message' => "Package {$validated['action']}ed successfully"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Error {$validated['action']}ing package",
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function getPackageStatusExternal($trackingNumber, Request $request)
    {
        try {
            $request->validate(['api_key' => 'required|string']);

            if (!$this->verifyApiKey($request->api_key)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid API key'
                ], Response::HTTP_UNAUTHORIZED);
            }

            $package = Package::where('tracking_number', $trackingNumber)->first();
            if (!$package) {
                return response()->json([
                    'success' => false,
                    'message' => 'Package not found'
                ], Response::HTTP_NOT_FOUND);
            }

            $state = $package->getState();

            return response()->json([
                'success' => true,
                'data' => [
                    'tracking_number' => $package->tracking_number,
                    'status' => $state->getStatusName(),
                    'location' => $state->getCurrentLocation(),
                    'last_updated' => $package->updated_at,
                    'estimated_delivery' => $package->estimated_delivery,
                    'actual_delivery' => $package->actual_delivery,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving package status',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function verifyApiKey(string $apiKey): bool
    {
        return !empty($apiKey) && $apiKey === config('app.external_api_key');
    }
}
