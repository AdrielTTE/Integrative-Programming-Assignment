<?php

namespace App\Http\Controllers\DriverControllers;

use App\Http\Controllers\Controller;
use App\Services\DriverPackageService;

use App\Factories\Driver\UpdateStatusViewFactory; // Import the factory
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;

class DriverPackagesController extends Controller
{
    protected DriverPackageService $packageService;

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
        $request->validate(['status' => 'required|string']);

        try {
            Http::withToken(session('api_token')) // Secure Coding Practice
                ->post(config('services.api.base_url')."/delivery/package/{$packageId}/update-status", [
                    'status' => $request->input('status'),
                ])
                ->throw(); // Throw an exception if the API returns an error

            return redirect()->route('driver.packages.index')->with('success', "Status for package {$packageId} updated successfully.");

        } catch (RequestException $e) {
            return back()->with('error', 'API Error: ' . $e->response->json('message', 'An unknown error occurred.'))->withInput();
        }
    }
}