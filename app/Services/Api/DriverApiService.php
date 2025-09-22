<?php

namespace App\Services\Api;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class DriverApiService
{
public function getTotalPackageCount(string $driverId): int
{
return DB::table('delivery')->where('driver_id', $driverId)->count();
}

public function getDeliveryCountByStatus(string $driverId, string $status): int
{
// If status is 'ALL', count everything. Otherwise, filter by the specific status.
$query = DB::table('delivery')->where('driver_id', $driverId);
if (strtoupper($status) !== 'ALL') {
$query->where('delivery_status', $status);
}
return $query->count();
}

public function getRecentPackages(string $driverId, int $limit = 5): Collection
{
return DB::table('package')
->join('delivery', 'package.package_id', '=', 'delivery.package_id')
->where('delivery.driver_id', $driverId)
->whereNotIn('package.package_status', ['DELIVERED', 'FAILED', 'CANCELLED'])
->select('package.package_id', 'package.recipient_address', 'package.package_status', 'delivery.estimated_delivery_time')
->orderBy('delivery.pickup_time', 'desc')
->limit($limit)
->get();
}
}