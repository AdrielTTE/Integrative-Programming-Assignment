<?php

namespace App\Services;

use App\Models\DeliveryDriver;
use App\Models\Delivery;
use App\Models\Package;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use App\Models\ProofOfDelivery;
use Illuminate\Support\Str;

/**
 * Service class that handles package operations for drivers.
 */
class DriverPackageService
{
    /**
     * Get packages for status update.
     */
    public function getPackagesForStatusUpdate(string $driverId): LengthAwarePaginator
    {
        $items = $this->fetchPackagesFromDatabase($driverId);
        return $this->createPaginatorFromCollection($items);
    }

    /**
     * Get assigned packages for the driver's main list.
     */
    public function getAssignedPackages(): LengthAwarePaginator
    {
        $driverId = auth()->user()->user_id;

        $query = DB::table('package')
            ->join('delivery', 'package.package_id', '=', 'delivery.package_id')
            ->where('delivery.driver_id', $driverId)
            ->whereNotIn('package.package_status', ['DELIVERED', 'CANCELLED', 'FAILED'])
            ->select(
                'package.package_id',
                'package.tracking_number',
                'package.recipient_address',
                'package.package_status',
                'delivery.estimated_delivery_time'
            )
            ->orderBy('delivery.estimated_delivery_time', 'asc');

        return $query->paginate(10);
    }

    /**
     * Update package status.
     */
    public function updatePackageStatus(string $packageId, string $status, string $driverId): bool
    {
        return $this->updateViaDatabase($packageId, $status, $driverId);
    }

    /**
     * Fetch packages from the database.
     */
    protected function fetchPackagesFromDatabase(string $driverId): array
    {
        return DB::table('package')
            ->join('delivery', 'package.package_id', '=', 'delivery.package_id')
            ->where('delivery.driver_id', $driverId)
            ->whereNotIn('package.package_status', ['DELIVERED', 'CANCELLED', 'FAILED'])
            ->select(
                'package.package_id',
                'package.tracking_number',
                'package.recipient_address',
                'package.package_status'
            )
            ->get()
            ->toArray();
    }

    /**
     * Update package via database.
     */
    protected function updateViaDatabase(string $packageId, string $status, string $driverId): bool
    {
        DB::beginTransaction();

        try {
            // Step 1: Update the package's status.
            DB::table('package')
                ->where('package_id', $packageId)
                ->update(['package_status' => $status]);

            // Step 2: Update the delivery's status and set the actual delivery time if delivered.
            $deliveryUpdateData = ['delivery_status' => $status];
            if ($status === 'DELIVERED') {
                $deliveryUpdateData['actual_delivery_time'] = now();
            }

            DB::table('delivery')
                ->where('package_id', $packageId)
                ->update($deliveryUpdateData);

            // Step 3: If the delivery is complete or failed, update the driver's status to 'AVAILABLE'.
            if (in_array($status, ['DELIVERED', 'FAILED'])) {
                DB::table('deliverydriver')
                    ->where('driver_id', $driverId)
                    ->update(['driver_status' => 'AVAILABLE']);
            }

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Database update failed for package status', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Create paginator from a collection of items.
     */
    protected function createPaginatorFromCollection($items): LengthAwarePaginator
    {
        $perPage = 15;
        $currentPage = request()->get('page', 1);
        $items = collect($items);

        return new LengthAwarePaginator(
            $items->forPage($currentPage, $perPage),
            $items->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url()]
        );
    }

    /**
     * Get delivery history for the driver
     */
    public function getDeliveryHistory(): LengthAwarePaginator
    {
        $driverId = Auth::id();
        $query = DB::table('package')
            ->join('delivery', 'package.package_id', '=', 'delivery.package_id')
            ->where('delivery.driver_id', $driverId)
            ->whereIn('package.package_status', ['DELIVERED', 'FAILED', 'CANCELLED'])
            ->select(
                'package.package_id',
                'package.tracking_number',
                'package.package_status',
                'delivery.actual_delivery_time'
            )
            ->orderBy('delivery.actual_delivery_time', 'desc');

        return $query->paginate(15);
    }

    /**
     * Get package details for a specific package
     * FIXED: Works with user table structure where user_id has prefixes (C for customers, D for drivers)
     */
    // --- NEW METHOD 1: Get details for the proof page ---
    public function getPackageDetails(string $packageId): object
    {
        $driverId = Auth::id();

        // --- THIS IS THE CORRECTED QUERY ---
        $package = DB::table('package')
            ->join('delivery', 'package.package_id', '=', 'delivery.package_id')
            // The fix is to join 'user' on 'user.user_id'
            ->leftJoin('user', 'package.user_id', '=', 'user.user_id')
            ->where('package.package_id', $packageId)
            ->where('delivery.driver_id', $driverId) // Security check
            ->select(
                'package.*', // Select all columns from package
                'delivery.delivery_id',
                'user.username as recipient_name' // Get the name from the user table
            )
            ->first();
        // --- END OF CORRECTION ---

        if (!$package) {
            throw new \Exception('Package not found or you are not authorized to view it.');
        }

        return $package;
    }

    /**
     * Complete the delivery and save the proof.
     */
    public function updateStatusWithProof(string $packageId, array $data): void
    {
        $driverId = Auth::id();

        DB::beginTransaction();

        try {
            // 1. Get the delivery record
            $delivery = DB::table('delivery')
                ->where('package_id', $packageId)
                ->where('driver_id', $driverId)
                ->first();

            if (!$delivery) {
                throw new \Exception('Delivery record not found');
            }

            // 2. Update package status to DELIVERED
            DB::table('package')
                ->where('package_id', $packageId)
                ->update([
                    'package_status' => 'DELIVERED',
                    'updated_at' => now()
                ]);

            // 3. Update delivery status and actual delivery time
            DB::table('delivery')
                ->where('delivery_id', $delivery->delivery_id)
                ->update([
                    'delivery_status' => 'DELIVERED',
                    'actual_delivery_time' => now()
                ]);

            // 4. Create proof of delivery record
            DB::table('proofofdelivery')->insert([
                'proof_id' => 'PD' . str_pad(rand(10000, 99999), 5, '0', STR_PAD_LEFT),
                'delivery_id' => $delivery->delivery_id,
                'proof_type' => $data['proof_type'] ?? 'SIGNATURE',
                'recipient_signature_name' => $data['recipient_signature_name'] ?? null,
                'timestamp_created' => now(),
                'verification_status' => 'PENDING',
                'notes' => $data['notes'] ?? null,
            ]);

            // 5. Update driver status to AVAILABLE
            DB::table('deliverydriver')
                ->where('driver_id', $driverId)
                ->update(['driver_status' => 'AVAILABLE']);

            DB::commit();
            Log::info("Successfully updated package {$packageId} to DELIVERED with proof");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to update package status with proof: " . $e->getMessage());
            throw $e;
        }
    }
}
