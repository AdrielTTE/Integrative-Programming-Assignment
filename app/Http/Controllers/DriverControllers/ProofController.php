<?php

namespace App\Http\Controllers\DriverControllers;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Delivery;
use App\Models\DeliveryDriver;
use App\Models\ProofOfDelivery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProofController extends Controller
{
    /**
     * Show the form to create a proof of delivery for a specific package.
     */
    public function create(string $packageId)
    {
        // Security Check: Get the package but only if it is assigned to this driver.
        $package = Package::where('package_id', $packageId)
            ->whereHas('delivery', function ($query) {
                $query->where('driver_id', Auth::id());
            })
            ->firstOrFail(); // Fails if package is not found or not assigned to this driver.

        // Pass the package data to the view.
        return view('DriverViews.proof-of-delivery', compact('package'));
    }

    /**
     * Store the new proof of delivery and update all related statuses.
     * This is the logic that will run when the "Complete Delivery" button is clicked.
     */
    public function store(Request $request, string $packageId)
    {
        // 1. Validate the form input.
        $validated = $request->validate([
            'proof_type' => 'required|string|in:SIGNATURE,PHOTO',
            'recipient_signature_name' => 'required_if:proof_type,SIGNATURE|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        // 2. Use a Database Transaction for safety.
        DB::beginTransaction();
        try {
            $driverId = Auth::id();
            
            // Find the specific delivery record for this package.
            $delivery = Delivery::where('package_id', $packageId)->firstOrFail();

            // 3. Create the new Proof of Delivery record in the database.
            ProofOfDelivery::create([
                'proof_id' => 'PD' . strtoupper(uniqid()),
                'delivery_id' => $delivery->delivery_id,
                'proof_type' => $validated['proof_type'],
                'recipient_signature_name' => $validated['recipient_signature_name'] ?? null,
                'timestamp_created' => now(),
                'verification_status' => 'PENDING',
                'notes' => $validated['notes'],
            ]);

            // 4. Update the Package status to 'DELIVERED'.
            Package::where('package_id', $packageId)->update(['package_status' => 'DELIVERED']);

            // 5. Update the Delivery status and set the actual delivery time.
            $delivery->update([
                'delivery_status' => 'DELIVERED',
                'actual_delivery_time' => now()
            ]);

            // 6. Update the Driver's status back to 'AVAILABLE'.
            DeliveryDriver::where('driver_id', $driverId)->update(['driver_status' => 'AVAILABLE']);
            
            // If all steps succeed, save the changes.
            DB::commit();

        } catch (\Exception $e) {
            // If any step fails, undo all changes and show an error.
            DB::rollBack();
            return back()->with('error', 'Failed to complete delivery: ' . $e->getMessage())->withInput();
        }

        // 7. Redirect the driver to their history page with a success message.
        return redirect()->route('driver.history.index')
            ->with('success', "Delivery for package {$packageId} has been completed and proof was submitted!");
    }
}