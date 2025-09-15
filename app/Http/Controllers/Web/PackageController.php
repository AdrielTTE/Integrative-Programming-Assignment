<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\PackageService;
use App\Services\Api\PackageService as ApiPackageService;
use App\Services\ProofService; 
use App\Http\Requests\CreatePackageRequest;
use App\Http\Requests\UpdatePackageRequest;
use App\Http\Requests\BulkUpdatePackageRequest;
use App\Http\Requests\SearchPackageRequest;
use App\Models\Package;
use App\Models\ProofOfDelivery; 
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth; 

class PackageController extends Controller
{
    protected $packageService;
    protected ProofService $proofService;

    public function __construct(PackageService $packageService, ProofService $proofService)
    {
        $this->packageService = $packageService;
        $this->proofService = $proofService;
    }

    public function track(Request $request)
    {
        $trackingNumber = $request->get('tracking_number');
        if (!$trackingNumber) {
            return view('packages.track', [
                'trackingNumber' => null, 'package' => null, 'history' => [], 'error' => null,
            ]);
        }
        try {
            $package = Package::where('tracking_number', $trackingNumber)->first();
            if (!$package) {
                return view('packages.track', [
                    'trackingNumber' => $trackingNumber, 'package' => null, 'history' => [],
                    'error' => "Package not found with tracking number: {$trackingNumber}",
                ]);
            }
            $history = $this->packageService->getPackageHistory($package->package_id);
            return view('packages.track', [
                'trackingNumber' => $trackingNumber, 'package' => $package, 'history' => $history, 'error' => null,
            ]);
        } catch (\Exception $e) {
            Log::error('Error tracking package: ' . $e->getMessage());
            return view('packages.track', [
                'trackingNumber' => $trackingNumber, 'package' => null, 'history' => [],
                'error' => 'Error tracking package. Please try again.',
            ]);
        }
    }

    /**
     * Display a listing of packages
     */
    public function index(Request $request)
    {
        try {
            $criteria = $request->all();
            $criteria['paginate'] = true;
            $criteria['per_page'] = $request->get('per_page', 15);
            
            $packages = $this->packageService->searchPackages($criteria);
            $statistics = $this->packageService->getStatistics();
            
            return view('packages.index', compact('packages', 'statistics'));
        } catch (\Exception $e) {
            Log::error('Error loading packages: ' . $e->getMessage());
            return back()->with('error', 'Error loading packages. Please try again.');
        }
    }

    /**
     * Show the form for creating a new package
     */
    public function create()
    {
        try {
            $customers = Customer::where('status', Customer::STATUS_ACTIVE)->get();
            $priorities = Package::getPriorities();
            
            return view('packages.create', compact('customers', 'priorities'));
        } catch (\Exception $e) {
            Log::error('Error loading create package form: ' . $e->getMessage());
            return back()->with('error', 'Error loading form. Please try again.');
        }
    }

    /**
     * Store a newly created package
     */
    public function store(CreatePackageRequest $request)
    {
        try {
            $package = $this->packageService->createPackage($request->validated());
            
            return redirect()
                ->route('packages.show', $package->package_id)
                ->with('success', 'Package created successfully! Tracking Number: ' . $package->tracking_number);
        } catch (\Exception $e) {
            Log::error('Error creating package: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Error creating package. Please try again.');
        }
    }

    /**
     * Display the specified package.
     */
     public function show($packageId)
    {
        
        try {
            $package = $this->packageService->getPackageWithDetails($packageId);

            if (!$package) {
                return redirect()->route('customer.home')->with('error', 'Package not found.');
            }

            if (Auth::id() !== $package->customer_id) {
                return redirect()->route('customer.home')->with('error', 'You are not authorized to view this package.');
            }

            $history = $this->packageService->getPackageHistory($packageId);
            $proof = null;
            $metadata = [];
            $verificationDetails = [];

            if ($package->package_status === 'DELIVERED') {
                $proof = $this->proofService->getProofByPackageId($packageId);
                if ($proof) {
                    $metadata = $this->proofService->getProofMetadata($proof);
                    $verificationDetails = $this->proofService->verifyProof($proof);
                }
            }
            return view('packages.customer_show', compact('package', 'history', 'proof', 'metadata', 'verificationDetails'));
        } catch (\Exception $e) {
            Log::error('Error showing package: ' . $e->getMessage());
            return redirect()->route('customer.home')->with('error', 'Error loading package details.');
        }
    }

    /**
     * Show the form for editing the specified package
     */
    public function edit($packageId)
    {
        try {
            $package = $this->packageService->getPackageWithDetails($packageId);
            
            if (!$package) {
                return redirect()
                    ->route('packages.index')
                    ->with('error', 'Package not found.');
            }

            if (!$package->canBeEdited()) {
                return redirect()
                    ->route('packages.show', $packageId)
                    ->with('error', 'This package cannot be edited in its current status.');
            }

            $customers = Customer::where('status', Customer::STATUS_ACTIVE)->get();
            $priorities = Package::getPriorities();
            $statuses = Package::getStatuses();
            
            return view('packages.edit', compact('package', 'customers', 'priorities', 'statuses'));
        } catch (\Exception $e) {
            Log::error('Error loading edit package form: ' . $e->getMessage());
            return back()->with('error', 'Error loading form. Please try again.');
        }
    }

    /**
     * Update the specified package
     */
    public function update(UpdatePackageRequest $request, $packageId)
    {
        try {
            $package = $this->packageService->getPackageWithDetails($packageId);
            
            if (!$package) {
                return redirect()
                    ->route('packages.index')
                    ->with('error', 'Package not found.');
            }

            $updatedPackage = $this->packageService->updatePackage($package, $request->validated());
            
            return redirect()
                ->route('packages.show', $packageId)
                ->with('success', 'Package updated successfully!');
        } catch (\Exception $e) {
            Log::error('Error updating package: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Error updating package. Please try again.');
        }
    }

    /**
     * Remove the specified package
     */
    public function destroy($packageId)
    {
        try {
            $package = $this->packageService->getPackageWithDetails($packageId);
            
            if (!$package) {
                return redirect()
                    ->route('packages.index')
                    ->with('error', 'Package not found.');
            }

            if (!$package->canBeCancelled()) {
                return redirect()
                    ->route('packages.show', $packageId)
                    ->with('error', 'This package cannot be cancelled in its current status.');
            }

            // Soft delete the package
            $package->delete();
            
            return redirect()
                ->route('packages.index')
                ->with('success', 'Package cancelled successfully!');
        } catch (\Exception $e) {
            Log::error('Error cancelling package: ' . $e->getMessage());
            return back()->with('error', 'Error cancelling package. Please try again.');
        }
    }

    /**
     * Handle bulk operations
     */
    public function bulkUpdate(BulkUpdatePackageRequest $request)
    {
        try {
            $results = $this->packageService->bulkUpdate(
                $request->validated()['package_ids'],
                $request->validated()['action'],
                $request->validated()['value']
            );
            
            $message = "Bulk update completed: {$results['success']} successful, {$results['failed']} failed";
            
            if ($results['failed'] > 0) {
                return back()->with('warning', $message);
            }
            
            return back()->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Error in bulk update: ' . $e->getMessage());
            return back()->with('error', 'Error performing bulk update. Please try again.');
        }
    }

    /**
     * Search packages with advanced criteria
     */
    public function search(SearchPackageRequest $request)
    {
        try {
            $criteria = $request->validated();
            $criteria['paginate'] = true;
            $criteria['per_page'] = $request->get('per_page', 15);
            
            $packages = $this->packageService->searchPackages($criteria);
            
            return view('packages.search-results', compact('packages', 'criteria'));
        } catch (\Exception $e) {
            Log::error('Error searching packages: ' . $e->getMessage());
            return back()->with('error', 'Error searching packages. Please try again.');
        }
    }
    /**
     * Get the proof of delivery for a specific package.
     *
     * @param string $package_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProof(string $package_id)
    {
        $proof = ProofOfDelivery::whereHas('delivery', function ($query) use ($package_id) {
            $query->where('package_id', $package_id);
        })->first();

        // If no proof is found, return a 404 error.
        if (!$proof) {
            return response()->json(['message' => 'Proof of delivery not found for this package.'], 404);
        }

        // If a proof is found, return it as JSON.
        return response()->json($proof);
    }

    /**
     * Display dashboard with statistics
     */
    public function dashboard()
    {
        try {
            $todayStats = $this->packageService->getStatistics('today');
            $weekStats = $this->packageService->getStatistics('week');
            $monthStats = $this->packageService->getStatistics('month');
            $yearStats = $this->packageService->getStatistics('year');
            
            $packagesRequiringAttention = $this->packageService->getPackagesRequiringAttention();
            $unassignedPackages = $this->packageService->getUnassignedPackages();
            
            return view('packages.dashboard', compact(
                'todayStats', 
                'weekStats', 
                'monthStats', 
                'yearStats',
                'packagesRequiringAttention',
                'unassignedPackages'
            ));
        } catch (\Exception $e) {
            Log::error('Error loading dashboard: ' . $e->getMessage());
            return view('packages.dashboard')->with('error', 'Error loading dashboard data.');
        }
    }
    
    /**
     * Generate and download reports
     */
    public function generateReport(Request $request)
    {
        try {
            $startDate = $request->get('start_date', now()->subMonth()->format('Y-m-d'));
            $endDate = $request->get('end_date', now()->format('Y-m-d'));
            $format = $request->get('format', 'json');
            
            $report = $this->packageService->generateReport($startDate, $endDate, $format);
            
            if ($format === 'csv') {
                return response()->download($report['file_path']);
            } elseif ($format === 'pdf') {
                return response()->download($report['file_path']);
            }
            
            return response()->json($report);
        } catch (\Exception $e) {
            Log::error('Error generating report: ' . $e->getMessage());
            return back()->with('error', 'Error generating report. Please try again.');
        }
    }
}