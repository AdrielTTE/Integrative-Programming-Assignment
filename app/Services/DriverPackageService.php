<?php

namespace App\Services;

use App\Models\DeliveryDriver; // <-- IMPORT THE DeliveryDriver MODEL
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

/**
 * Service class that handles package operations for drivers.
 */
class DriverPackageService
{
    /**
     * Get packages for status update.
     * This now fetches directly from the database for simplicity and reliability.
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
     * This now calls the database method directly and passes the driverId.
     */
    public function updatePackageStatus(string $packageId, string $status, string $driverId): bool
    {
        // For simplicity and to ensure it works, we will call the direct database update method.
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
     * --- THIS IS THE FULLY CORRECTED METHOD ---
     */
    protected function updateViaDatabase(string $packageId, string $status, string $driverId): bool
    {
        DB::beginTransaction();
        
        try {
            // Step 1: Update the package's status.
            // Note: We are NOT updating `updated_at`.
            DB::table('package')
                ->where('package_id', $packageId)
                ->update(['package_status' => $status]);

            // Step 2: Update the delivery's status and set the actual delivery time if delivered.
            $deliveryUpdateData = ['delivery_status' => $status];
            if ($status === 'DELIVERED') {
                $deliveryUpdateData['actual_delivery_time'] = now()->addDay();
            }

            DB::table('delivery')
                ->where('package_id', $packageId)
                ->update($deliveryUpdateData);

            // --- THIS IS THE NEW LOGIC ---
            // Step 3: If the delivery is complete or failed, update the driver's status to 'AVAILABLE'.
            if (in_array($status, ['DELIVERED', 'FAILED'])) {
                DeliveryDriver::where('driver_id', $driverId)
                              ->update(['driver_status' => 'AVAILABLE']);
            }
            // --- END OF NEW LOGIC ---

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
}