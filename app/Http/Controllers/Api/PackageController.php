<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Api\PackageService as ApiPackageService;
use App\Services\PackageService;
use App\Http\Requests\CreatePackageRequest;
use App\Http\Requests\UpdatePackageRequest;
use App\Http\Requests\BulkUpdatePackageRequest;
use App\Http\Requests\SearchPackageRequest;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PackageController extends Controller
{
    protected $packageService;
    protected $apiPackageService;

    public function __construct(PackageService $packageService, ApiPackageService $apiPackageService)
    {
        $this->packageService = $packageService;
        $this->apiPackageService = $apiPackageService;
    }

    // Your existing methods...
    public function getAll()
    {
        try {
            $packages = $this->packageService->getAllPackages();
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
                'data' => $package,
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
                'data' => $package
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving package',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Add the missing methods:
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
            return response()->json([
                'success' => true,
                'data' => [
                    'package' => $package,
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

    public function getStatistics($period = 'month')
    {
        try {
            $statistics = $this->packageService->getStatistics($period);
            return response()->json([
                'success' => true,
                'data' => $statistics
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving statistics',
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
                $request->validated()['value']
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

     public function getCountPackage(){
        return response()->json($this->apiPackageService->getCountPackage());
    }

    public function getRecentPackages(int $noOfRecords){
        return response()->json($this->apiPackageService->getRecentPackages($noOfRecords));
    }
}
