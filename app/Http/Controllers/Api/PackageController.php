<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PackageService;
use App\Services\Api\PackageService as ApiPackageService;
use App\Http\Requests\CreatePackageRequest;
use App\Http\Requests\UpdatePackageRequest;
use App\Http\Requests\BulkUpdatePackageRequest;
use App\Http\Requests\SearchPackageRequest;
use App\Models\Package;
use App\Models\ProofOfDelivery;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PackageController extends Controller
{
    protected PackageService $packageService;
    protected ApiPackageService $apiPackageService;

    public function __construct(PackageService $packageService, ApiPackageService $apiPackageService)
    {
        $this->packageService = $packageService;
        $this->apiPackageService = $apiPackageService;
    }

    public function getAll()
    {
        try {
            $packages = $this->packageService->searchPackages([]);
            return response()->json([
                'success' => true,
                'data' => $packages
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving packages',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function add(CreatePackageRequest $request)
    {
        try {
            $package = $this->packageService->createPackage($request->validated());
            return response()->json([
                'success' => true,
                'data' => $package->getFormattedDetails(),
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

    public function get($packageId)
    {
        try {
            $package = $this->packageService->getPackageWithDetails($packageId);
            if (!$package) {
                return response()->json([
                    'success' => false,
                    'message' => 'Package not found'
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json([
                'success' => true,
                'data' => $package->getFormattedDetails()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving package',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(Request $request, string $packageId)
{
    try {
        $package = $this->apiPackageService->update($packageId, $request->all());

        return response()->json([
            'success' => true,
            'data'    => $package,
            'message' => 'Package updated successfully'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error updating package',
            'error'   => $e->getMessage()
        ], Response::HTTP_BAD_REQUEST);
    }
}


public function updateIsRated(Request $request, string $packageId)
{
    \Log::info('updateIsRated request data:', $request->all());

    $validated = $request->validate([
        'is_rated' => 'required|boolean',
    ]);

    try {
        $package = $this->apiPackageService->updateIsRated($packageId, $validated['is_rated']);

        return response()->json([
            'success' => true,
            'data' => $package,
            'message' => 'Package rating status updated successfully.',
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to update rating status.',
            'error' => $e->getMessage(),
        ], 400);
    }
}






    public function track($trackingNumber)
    {
        try {
            $package = Package::where('tracking_number', $trackingNumber)->first();
            if (!$package) {
                return response()->json([
                    'success' => false,
                    'message' => 'Package not found'
                ], Response::HTTP_NOT_FOUND);
            }

            $history = $this->packageService->getPackageHistory($package->package_id);
            $state = $package->getState();

            return response()->json([
                'success' => true,
                'data' => [
                    'package' => $package->getFormattedDetails(),
                    'current_state' => [
                        'status' => $state->getStatusName(),
                        'location' => $state->getCurrentLocation(),
                        'color' => $state->getStatusColor(),
                        'can_edit' => $state->canBeEdited(),
                        'can_cancel' => $state->canBeCancelled(),
                        'allowed_transitions' => $state->getAllowedTransitions()
                    ],
                    'history' => $history
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error tracking package',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function process($packageId)
    {
        try {
            $package = $this->packageService->processPackage($packageId);
            return response()->json([
                'success' => true,
                'data' => $package->getFormattedDetails(),
                'message' => 'Package processed successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing package',
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function cancel($packageId)
    {
        try {
            $package = $this->packageService->cancelPackage($packageId);
            return response()->json([
                'success' => true,
                'data' => $package->getFormattedDetails(),
                'message' => 'Package cancelled successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error cancelling package',
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function assign($packageId, Request $request)
    {
        try {
            $request->validate(['driver_id' => 'required|string|exists:user,user_id']);

            $package = $this->packageService->assignPackage($packageId, $request->driver_id);
            return response()->json([
                'success' => true,
                'data' => $package->getFormattedDetails(),
                'message' => 'Package assigned successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error assigning package',
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function deliver($packageId, Request $request)
    {
        try {
            $proofData = $request->validate([
                'delivery_photo' => 'nullable|string',
                'signature' => 'nullable|string',
                'notes' => 'nullable|string'
            ]);

            $package = $this->packageService->deliverPackage($packageId, $proofData);
            return response()->json([
                'success' => true,
                'data' => $package->getFormattedDetails(),
                'message' => 'Package delivered successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error delivering package',
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function search(SearchPackageRequest $request)
    {
        try {
            $packages = $this->packageService->searchPackages($request->validated());
            return response()->json([
                'success' => true,
                'data' => $packages
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error searching packages',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function bulkUpdate(BulkUpdatePackageRequest $request)
    {
        try {
            $results = $this->packageService->bulkUpdate(
                $request->validated()['package_ids'],
                $request->validated()['action'],
                $request->validated()['value'] ?? null
            );
            return response()->json([
                'success' => true,
                'data' => $results
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error performing bulk update',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Legacy API methods for backward compatibility
    public function getCountPackage()
    {
        return response()->json($this->apiPackageService->getCountPackage());
    }

    public function getRecentPackages(int $noOfRecords)
    {
        return response()->json($this->apiPackageService->getRecentPackages($noOfRecords));
    }

    public function getCountByStatus(string $status)
    {
        return response()->json($this->apiPackageService->getCountByStatus($status));
    }

    public function getUnassignedPackages()
    {
        $packages = $this->apiPackageService->getUnassignedPackages();
        return response()->json($packages);
    }

    public function getPackagesByStatus(string $status, int $page, int $pageSize, string $customerId)
    {
        try {
            // normalize status (handle uppercase "DELIVERED")
            $status = strtolower($status);

            $packages = $this->apiPackageService->getPackagesByStatus($status, $page, $pageSize, $customerId);

            return response()->json($packages, 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch package data',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
