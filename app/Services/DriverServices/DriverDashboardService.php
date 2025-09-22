<?php

namespace App\Services\DriverServices;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class DriverDashboardService
{
    /**
     * --- THIS IS THE MISSING METHOD ---
     * Get all delivery statistics for the currently authenticated driver.
     */
    public function getDeliveryStats(): array
    {
        $driverId = Auth::id();

        $stats = DB::table('delivery')
            ->where('driver_id', $driverId)
            ->select('delivery_status', DB::raw('count(*) as count'))
            ->groupBy('delivery_status')
            ->pluck('count', 'delivery_status');

        return [
            'total_assigned' => $stats->sum(),
            'scheduled' => $stats->get('SCHEDULED', 0),
            'in_transit' => $stats->get('IN_TRANSIT', 0),
            'delivered' => $stats->get('DELIVERED', 0),
            'failed' => $stats->get('FAILED', 0),
        ];
    }

    /**
     * --- THIS IS THE MISSING METHOD ---
     * Gets the most recent active packages for the logged-in driver.
     */
    public function getRecentPackages(int $limit = 5): Collection
    {
        $driverId = Auth::id();

        return DB::table('package')
            ->join('delivery', 'package.package_id', '=', 'delivery.package_id')
            ->where('delivery.driver_id', $driverId)
            ->whereNotIn('package.package_status', ['DELIVERED', 'FAILED', 'CANCELLED'])
            ->select('package.package_id', 'package.recipient_address', 'package.package_status', 'delivery.estimated_delivery_time')
            ->orderBy('delivery.pickup_time', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Fetches the details of the currently logged-in driver.
     */
    public function getDriverDetails()
    {
        $driverId = Auth::id();
        return DB::table('deliverydriver')->where('driver_id', $driverId)->first();
    }

    /**
     * Updates the status of the specified driver in the database.
     */
    public function updateDriverStatus(string $newStatus): bool
    {
        $driverId = Auth::id();
        $affectedRows = DB::table('deliverydriver')
            ->where('driver_id', $driverId)
            ->update(['driver_status' => $newStatus]);

        return $affectedRows > 0;
    }

    public function getPackageCountByStatus(string $status): int
    {
         $response = Http::get("http://localhost:8001/api/delivery/getCountByStatus/{$status}");

    if ($response->failed()) {
        return 0;
    }

    $data = $response->json();


    if (!empty($data) && isset($data[0]['count'])) {
        return (int) $data[0]['count'];
    }

    return 0;
    }
}
