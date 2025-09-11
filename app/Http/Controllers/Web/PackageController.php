<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\PackageService;
use App\Http\Requests\CreatePackageRequest;
use App\Http\Requests\UpdatePackageRequest;
use App\Http\Requests\BulkUpdatePackageRequest;
use App\Http\Requests\SearchPackageRequest;
use App\Models\Package;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PackageController extends Controller
{
    protected $packageService;

    public function __construct(PackageService $packageService)
    {
        $this->packageService = $packageService;
    }

    /**
     * Track package (public page + form submit in one method)
     */
    public function track(Request $request)
    {
        $trackingNumber = $request->get('tracking_number');

        // If first time opening /track, just show the form
        if (!$trackingNumber) {
            return view('packages.track', [
                'trackingNumber' => null,
                'package' => null,
                'history' => [],
                'error' => null,
            ]);
        }

        try {
            $package = Package::where('tracking_number', $trackingNumber)->first();

            if (!$package) {
                return view('packages.track', [
                    'trackingNumber' => $trackingNumber,
                    'package' => null,
                    'history' => [],
                    'error' => "Package not found with tracking number: {$trackingNumber}",
                ]);
            }

            $history = $this->packageService->getPackageHistory($package->package_id);

            return view('packages.track', [
                'trackingNumber' => $trackingNumber,
                'package' => $package,
                'history' => $history,
                'error' => null,
            ]);
        } catch (\Exception $e) {
            Log::error('Error tracking package: ' . $e->getMessage());

            return view('packages.track', [
                'trackingNumber' => $trackingNumber,
                'package' => null,
                'history' => [],
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
     * Display the specified package
     */
    public function show($packageId)
    {
        try {
            $package = $this->packageService->getPackageWithDetails($packageId);
            
            if (!$package) {
                return redirect()
                    ->route('packages.index')
                    ->with('error', 'Package not found.');
            }

            $history = $this->packageService->getPackageHistory($packageId);
            $route = $this->packageService->calculateDeliveryRoute($package);
            
            return view('packages.show', compact('package', 'history', 'route'));
        } catch (\Exception $e) {
            Log::error('Error showing package: ' . $e->getMessage());
            return redirect()
                ->route('packages.index')
                ->with('error', 'Error loading package details.');
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