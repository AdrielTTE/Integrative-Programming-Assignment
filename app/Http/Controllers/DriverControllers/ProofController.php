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
    public function create(string $packageId)
    {
        $package = Package::where('package_id', $packageId)
            ->whereHas('delivery', function ($query) {
                $query->where('driver_id', Auth::id());
            })
            ->firstOrFail(); 

        return view('DriverViews.proof-of-delivery', compact('package'));
    }

    
    public function store(Request $request, string $packageId)
    {
        $validated = $request->validate([
            'proof_type' => 'required|string|in:SIGNATURE,PHOTO',
            'recipient_signature_name' => 'required_if:proof_type,SIGNATURE|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $driverId = Auth::id();
            
            $delivery = Delivery::where('package_id', $packageId)->firstOrFail();

            ProofOfDelivery::create([
                'proof_id' => 'PD' . strtoupper(uniqid()),
                'delivery_id' => $delivery->delivery_id,
                'proof_type' => $validated['proof_type'],
                'recipient_signature_name' => $validated['recipient_signature_name'] ?? null,
                'timestamp_created' => now(),
                'verification_status' => 'PENDING',
                'notes' => $validated['notes'],
            ]);

            Package::where('package_id', $packageId)->update(['package_status' => 'DELIVERED']);

            $delivery->update([
                'delivery_status' => 'DELIVERED',
                'actual_delivery_time' => now()
            ]);

            DeliveryDriver::where('driver_id', $driverId)->update(['driver_status' => 'AVAILABLE']);
            
            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to complete delivery: ' . $e->getMessage())->withInput();
        }

        return redirect()->route('driver.history.index')
            ->with('success', "Delivery for package {$packageId} has been completed and proof was submitted!");
    }
}