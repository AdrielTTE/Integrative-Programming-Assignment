<?php

namespace App\Http\Controllers\DriverControllers;

use App\Http\Controllers\Controller;
use App\Services\DriverPackageService;

use App\Factories\Driver\UpdateStatusViewFactory; // Import the factory
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;
use App\Factories\Driver\PackageDetailsFactory; // Import the new factory
use App\Factories\Driver\AssignedPackagesFactory;


class DriverPackagesController extends Controller
{
    protected DriverPackageService $packageService;

    public function show(string $packageId)
    {
        try {
            $package = $this->packageService->getPackageDetails($packageId);
            // This now points to a new, dedicated details view.
            return view('DriverViews.package-details', compact('package'));
        } catch (\Exception $e) {
            return redirect()->route('driver.packages.index')->with('error', $e->getMessage());
        }
    }


    public function __construct(DriverPackageService $packageService)
    {
        $this->packageService = $packageService;
    }

    /**
     * Display the list of packages assigned to the driver.
     */
    public function index()
    {
        $packages = $this->packageService->getAssignedPackages();

        // This view path matches your folder structure.
        return view('DriverViews.assignedPackages', compact('packages'));
    }

    public function showUpdateForm(string $packageId)
    {
        try {
            // Instantiate the factory for the specific package
            $factory = new UpdateStatusViewFactory($packageId);
            // Let the factory create and render the view
            return $factory->render();
        } catch (RequestException $e) {
            return redirect()->route('driver.packages.index')->with('error', 'Could not load package details: ' . $e->response->json('message'));
        }
    }

    /**
     * Handle the form submission to update the status via the API.
     */
    public function updateStatus(Request $request, string $packageId)
    {
       
        $validated = $request->validate([
            'status' => 'required|string|in:IN_TRANSIT,DELIVERED,FAILED',
            'proof_type' => 'required_if:status,DELIVERED|string|in:SIGNATURE,PHOTO',
            'recipient_signature_name' => 'required_if:proof_type,SIGNATURE|string|max:100',
        ]);

        try {
            $this->packageService->updateStatusWithProof($packageId, $validated);
            return redirect()->route('driver.packages.index')->with('success', "Package {$packageId} status updated successfully.");
        } catch (\Exception $e) {
            return back()->with('error', 'Update failed: ' . $e->getMessage())->withInput();
        }
    }
}
