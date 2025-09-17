<?php

namespace App\Http\Controllers\CustomerControllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreatePackageRequest;
use App\Http\Requests\UpdatePackageRequest;
use App\Services\PackageService;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

class PackageController extends Controller
{
    private PackageService $packageService;

    public function __construct(PackageService $packageService)
    {
        $this->packageService = $packageService;
    }

    public function index(Request $request)
    {
        $statuses = Package::getStatuses();
        $userId = Auth::id();

        $packages = Package::where('user_id', $userId)
                          ->with(['delivery.driver'])
                          ->when($request->status, function ($query, $status) {
                              return $query->where('package_status', $status);
                          })
                          ->orderBy('created_at', 'desc')
                          ->paginate(15);

        return view('customer.packages.index', compact('packages', 'statuses'));
    }

    public function create()
    {
        $priorities = Package::getPriorities();
        return view('customer.packages.create', compact('priorities'));
    }

    public function store(CreatePackageRequest $request)
    {
        try {
            $package = $this->packageService->createPackage($request->validated());

            return redirect()
                ->route('customer.packages.show', $package->package_id)
                ->with('success', 'Delivery request created successfully! Tracking Number: ' . $package->tracking_number);

        } catch (Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to create delivery request: ' . $e->getMessage());
        }
    }

    public function show(string $packageId)
    {
        $package = Package::where('package_id', $packageId)
                         ->where('user_id', Auth::id())
                         ->with(['delivery.driver'])
                         ->firstOrFail();

        $history = $this->packageService->getPackageHistory($packageId);
        $currentState = $package->getState();

        return view('customer.packages.show', compact('package', 'history', 'currentState'));
    }

    public function edit(string $packageId)
    {
        $package = Package::where('package_id', $packageId)
                         ->where('user_id', Auth::id())
                         ->firstOrFail();

        if (!$package->canBeEdited()) {
            return redirect()
                ->route('customer.packages.show', $packageId)
                ->with('error', 'This package cannot be modified in its current status.');
        }

        $priorities = Package::getPriorities();
        return view('customer.packages.edit', compact('package', 'priorities'));
    }

    public function update(UpdatePackageRequest $request, string $packageId)
    {
        try {
            $package = Package::where('package_id', $packageId)
                             ->where('user_id', Auth::id())
                             ->firstOrFail();

            $this->packageService->updatePackage($package, $request->validated());

            return redirect()
                ->route('customer.packages.show', $packageId)
                ->with('success', 'Package details updated successfully!');

        } catch (Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to update package: ' . $e->getMessage());
        }
    }

    public function destroy(string $packageId)
    {
        try {
            $this->packageService->cancelPackage($packageId, Auth::user());

            return redirect()
                ->route('customer.packages.index')
                ->with('success', 'Delivery request cancelled successfully!');

        } catch (Exception $e) {
            return back()
                ->with('error', 'Failed to cancel package: ' . $e->getMessage());
        }
    }

    public function process(string $packageId)
    {
        try {
            $this->packageService->processPackage($packageId);

            return back()->with('success', 'Package processed successfully!');

        } catch (Exception $e) {
            return back()->with('error', 'Failed to process package: ' . $e->getMessage());
        }
    }
}