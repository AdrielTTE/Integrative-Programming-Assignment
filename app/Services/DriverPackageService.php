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
use App\Observers\PackageSubject;
use App\Observers\CustomerObserver;
use Illuminate\Support\Facades\Crypt; // FOR DATA ENCRYPTION

/**
 * Service class that handles package operations for drivers with DATA PROTECTION.
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
                // SECURITY: Mask sensitive address data for list view
                DB::raw("CONCAT(SUBSTRING(package.recipient_address, 1, 20), '...') as recipient_address"),
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
         $success = $this->updateViaDatabase($packageId, $status, $driverId);

        if (!$success) {
            return false;
        }

        // Re-fetch the updated package
        $package = Package::with('customer')->where('package_id', $packageId)->first();

        if (!$package || !$package->customer) {
            Log::warning("Customer missing on package {$packageId}");
            return false;
        }

        // For observer
        $subject = new PackageSubject($package);
        $observer = new CustomerObserver($package->customer);
        $subject->addObserver($observer);
        $observer->forceUpdate($subject);

        return true;
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
                // SECURITY: Truncate address for security
                DB::raw("SUBSTRING(package.recipient_address, 1, 50) as recipient_address"),
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
            // SECURITY: Log sensitive operations
            Log::info('Package status update attempt', [
                'package_id' => $this->hashSensitiveData($packageId),
                'driver_id' => $this->hashSensitiveData($driverId),
                'new_status' => $status,
                'timestamp' => now()
            ]);

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
     * ENHANCED: With data protection for sensitive information
     */
    public function getPackageDetails(string $packageId): object
    {
        $driverId = Auth::id();

        // SECURITY: Log access to sensitive package details
        Log::info('Package details accessed', [
            'package_id' => $this->hashSensitiveData($packageId),
            'driver_id' => $this->hashSensitiveData($driverId),
            'ip' => request()->ip(),
            'timestamp' => now()
        ]);

        $package = DB::table('package')
            ->join('delivery', 'package.package_id', '=', 'delivery.package_id')
            ->leftJoin('user', 'package.user_id', '=', 'user.user_id')
            ->where('package.package_id', $packageId)
            ->where('delivery.driver_id', $driverId) // Security check
            ->select(
                'package.*',
                'delivery.delivery_id',
                'user.username as recipient_name'
            )
            ->first();

        if (!$package) {
            throw new \Exception('Package not found or you are not authorized to view it.');
        }

        // SECURITY: Decrypt sensitive data if it was encrypted
        if (isset($package->recipient_address)) {
            try {
                // Try to decrypt if data was encrypted
                $package->recipient_address = $this->decryptSensitiveData($package->recipient_address);
            } catch (\Exception $e) {
                // If decryption fails, assume it's not encrypted
                // Keep original data
            }
        }

        return $package;
    }

    /**
     * Complete the delivery and save the proof.
     * ENHANCED: With data encryption for sensitive proof information
     */
    public function updateStatusWithProof(string $packageId, array $data): Package
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

        // 4. SECURITY: Encrypt sensitive proof data before storing
        $encryptedRecipientName = !empty($data['recipient_signature_name'])
            ? $this->encryptSensitiveData($data['recipient_signature_name'])
            : null;

        $encryptedNotes = !empty($data['notes'])
            ? $this->encryptSensitiveData($data['notes'])
            : null;

        // 5. Create proof of delivery record with encrypted data
        DB::table('proofofdelivery')->insert([
            'proof_id' => 'PD' . str_pad(rand(10000, 99999), 5, '0', STR_PAD_LEFT),
            'delivery_id' => $delivery->delivery_id,
            'proof_type' => $data['proof_type'] ?? 'SIGNATURE',
            'recipient_signature_name' => $encryptedRecipientName,
            'timestamp_created' => now(),
            'verification_status' => 'PENDING',
            'notes' => $encryptedNotes,
        ]);

        // 6. Update driver status to AVAILABLE
        DB::table('deliverydriver')
            ->where('driver_id', $driverId)
            ->update(['driver_status' => 'AVAILABLE']);

        // 7. SECURITY: Log proof submission
        Log::info("Proof of delivery submitted", [
            'package_id' => $this->hashSensitiveData($packageId),
            'driver_id' => $this->hashSensitiveData($driverId),
            'proof_type' => $data['proof_type'] ?? 'SIGNATURE',
            'ip' => request()->ip(),
            'timestamp' => now()
        ]);

        DB::commit();

        // 8. RETURN the updated Package model (with customer)
        return Package::with('customer')->findOrFail($packageId);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error("Failed to update package status with proof: " . $e->getMessage());
        throw $e;
    }
}


    /**
     * SECURITY METHOD: Encrypt sensitive data
     */
    private function encryptSensitiveData(?string $data): ?string
    {
        if ($data === null || trim($data) === '') {
            return null;
        }

        try {
            return Crypt::encryptString($data);
        } catch (\Exception $e) {
            Log::error('Failed to encrypt sensitive data', ['error' => $e->getMessage()]);
            return $data; // Return original if encryption fails
        }
    }

    /**
     * SECURITY METHOD: Decrypt sensitive data
     */
    private function decryptSensitiveData(?string $encryptedData): ?string
    {
        if ($encryptedData === null || trim($encryptedData) === '') {
            return null;
        }

        try {
            return Crypt::decryptString($encryptedData);
        } catch (\Exception $e) {
            // If decryption fails, assume data is not encrypted
            return $encryptedData;
        }
    }

    /**
     * SECURITY METHOD: Hash sensitive data for logging (one-way)
     */
    private function hashSensitiveData(string $data): string
    {
        return 'HASH_' . substr(hash('sha256', $data), 0, 8);
    }
}
