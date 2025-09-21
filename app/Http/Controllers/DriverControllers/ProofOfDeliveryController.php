<?php

namespace App\Http\Controllers\DriverControllers;

use App\Http\Controllers\Controller;
use App\Services\DriverPackageService;
use App\Factories\Driver\ProofOfDeliveryViewFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ProofOfDeliveryController extends Controller
{
    protected DriverPackageService $packageService;

    public function __construct(DriverPackageService $packageService)
    {
        $this->packageService = $packageService;
    }

    /**
     * Show the form for creating a proof of delivery.
     */
    public function create(string $packageId)
    {
        try {
            $package = $this->packageService->getPackageDetails($packageId);
            return view('DriverViews.proof-of-delivery', compact('package'));
        } catch (\Exception $e) {
            return redirect()->route('driver.status.index')->with('error', $e->getMessage());
        }
    }

    /**
     * Store the new proof of delivery and update all related statuses.
     */
    public function store(Request $request, string $packageId)
{
    // ADD DEBUGGING
    Log::info("=== PROOF STORE CALLED ===");
    Log::info("Package ID: " . $packageId);
    Log::info("Request data: ", $request->all());
    Log::info("Current user: " . Auth::id());

    $driverId = Auth::user()->user_id;

    try {
        // Simple data collection - no validation, just get what we can
        $data = [
            'proof_type' => $request->input('proof_type', 'SIGNATURE'),
            'recipient_signature_name' => $request->input('recipient_signature_name', 'N/A'),
            'notes' => $request->input('notes', null),
            'status' => 'DELIVERED'
        ];

        Log::info("Data to be processed: ", $data);

        // Use the service to handle the database transaction
        $this->packageService->updateStatusWithProof($packageId, $data);
        
        Log::info("Service method completed successfully");
        
        return redirect()->route('driver.status.index')
            ->with('success', "Delivery completed successfully!");
            
    } catch (\Exception $e) {
        Log::error("ERROR in store method: " . $e->getMessage());
        Log::error("Stack trace: " . $e->getTraceAsString());
        
        return redirect()->route('driver.status.index')
            ->with('error', "Error: " . $e->getMessage());
    }
}
}