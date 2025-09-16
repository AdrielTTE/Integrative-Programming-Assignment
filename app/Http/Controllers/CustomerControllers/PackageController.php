<?php

namespace App\Http\Controllers\CustomerControllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreatePackageRequest;
use App\Http\Requests\UpdatePackageRequest;
use App\Commands\CreatePackageCommand;
use App\Commands\ModifyPackageCommand;
use App\Commands\CancelPackageCommand;
use App\Services\PackageCommandInvoker;
use App\Services\PackageService;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

class PackageController extends Controller
{
    private PackageCommandInvoker $commandInvoker;
    private PackageService $packageService;

    public function __construct(
        PackageCommandInvoker $commandInvoker, 
        PackageService $packageService
    ) {
        $this->commandInvoker = $commandInvoker;
        $this->packageService = $packageService;
        $this->middleware('auth');
        $this->middleware('customer');
    }

    /**
     * Display customer's packages
     */
    public function index(Request $request)
    {
        $customerId = Auth::id();
        
        $packages = Package::where('customer_id', $customerId)
                          ->with(['delivery.driver'])
                          ->when($request->status, function ($query, $status) {
                              return $query->where('package_status', $status);
                          })
                          ->orderBy('created_at', 'desc')
                          ->paginate(15);

        return view('customer.packages.index', compact('packages'));
    }

    /**
     * Show form for creating new package
     */
    public function create()
    {
        $priorities = Package::getPriorities();
        return view('customer.packages.create', compact('priorities'));
    }

    /**
     * Store new package using Command Pattern
     */
    public function store(CreatePackageRequest $request)
    {
        try {
            $command = new CreatePackageCommand($this->packageService, $request->validated());
            $package = $this->commandInvoker->execute($command);

            return redirect()
                ->route('customer.packages.show', $package->package_id)
                ->with('success', 'Delivery request created successfully! Tracking Number: ' . $package->tracking_number);

        } catch (Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to create delivery request: ' . $e->getMessage());
        }
    }

    /**
     * Display specific package
     */
    public function show(string $packageId)
    {
        $package = Package::where('package_id', $packageId)
                         ->where('customer_id', Auth::id())
                         ->with(['delivery.driver'])
                         ->firstOrFail();

        $history = $this->packageService->getPackageHistory($packageId);

        return view('customer.packages.show', compact('package', 'history'));
    }

    /**
     * Show form for editing package
     */
    public function edit(string $packageId)
    {
        $package = Package::where('package_id', $packageId)
                         ->where('customer_id', Auth::id())
                         ->firstOrFail();

        if (!$package->canBeEdited()) {
            return redirect()
                ->route('customer.packages.show', $packageId)
                ->with('error', 'This package cannot be modified in its current status.');
        }

        $priorities = Package::getPriorities();
        return view('customer.packages.edit', compact('package', 'priorities'));
    }

    /**
     * Update package using Command Pattern
     */
    public function update(UpdatePackageRequest $request, string $packageId)
    {
        try {
            $command = new ModifyPackageCommand(
                $this->packageService, 
                $packageId, 
                $request->validated()
            );
            
            $package = $this->commandInvoker->execute($command);

            return redirect()
                ->route('customer.packages.show', $packageId)
                ->with('success', 'Package details updated successfully!');

        } catch (Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to update package: ' . $e->getMessage());
        }
    }

    /**
     * Cancel package using Command Pattern
     */
    public function destroy(string $packageId)
    {
        try {
            $command = new CancelPackageCommand($this->packageService, $packageId);
            $package = $this->commandInvoker->execute($command);

            return redirect()
                ->route('customer.packages.index')
                ->with('success', 'Delivery request cancelled successfully!');

        } catch (Exception $e) {
            return back()
                ->with('error', 'Failed to cancel package: ' . $e->getMessage());
        }
    }

    /**
     * Undo last operation
     */
    public function undo()
    {
        try {
            $result = $this->commandInvoker->undo();
            
            return back()
                ->with('success', 'Last operation has been undone successfully!');

        } catch (Exception $e) {
            return back()
                ->with('error', 'Cannot undo: ' . $e->getMessage());
        }
    }

    /**
     * Get operation history (for debugging/admin)
     */
    public function history()
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $history = $this->commandInvoker->getHistory();
        return response()->json($history);
    }
}