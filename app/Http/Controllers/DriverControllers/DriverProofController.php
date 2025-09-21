<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Services\DriverPackageService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Package;

class DriverProofController extends Controller
{
    protected $packageService;

    public function __construct(DriverPackageService $packageService)
    {
        $this->packageService = $packageService;
    }

    /**
     * Show the proof submission form.
     */
    public function create(string $packageId)
    {
        $package = $this->packageService->getPackageDetails($packageId);
        return view('DriverViews.proof-of-delivery', compact('package')); // Matches your view name
    }

    /**
     * Store the proof and complete delivery.
     */
    public function store(Request $request, string $packageId)
    {
        // Validate form input
        $validated = $request->validate([
            'proof_type' => 'required|in:SIGNATURE,PHOTO',
            'recipient_signature_name' => 'required_if:proof_type,SIGNATURE|string|max:255',
            'proof_photo' => 'required_if:proof_type,PHOTO|image|mimes:jpeg,png,jpg|max:2048', // Max 2MB
            'delivery_location' => 'nullable|string|max:255', // If added to model
            'notes' => 'nullable|string|max:1000',
        ]);

        // Submit proof via service
        $proof = $this->packageService->submitProofAndComplete(
            $packageId,
            $validated,
            $request->file('proof_photo')
        );

        // Redirect to update status page or dashboard with success
        return redirect()->route('driver.status.index') // Assuming this is your update-status route
            ->with('success', 'Delivery completed and proof submitted successfully. Proof ID: ' . $proof->proof_id);
    }
}