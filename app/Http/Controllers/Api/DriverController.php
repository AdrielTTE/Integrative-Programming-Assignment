<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Api\DriverApiService; // We will create this service next

class DriverController extends Controller
{
protected DriverApiService $driverApiService;

public function __construct(DriverApiService $driverApiService)
{
$this->driverApiService = $driverApiService;
}

public function getTotalPackageCount(string $driverId)
{
$count = $this->driverApiService->getTotalPackageCount($driverId);
return response()->json(['count' => $count]);
}

public function getDeliveryCountByStatus(string $driverId, string $status)
{
$count = $this->driverApiService->getDeliveryCountByStatus($driverId, $status);
// Return in the same format as your admin API: ['STATUS' => count]
return response()->json([strtoupper($status) => $count]);
}

public function getRecentPackages(string $driverId, int $limit = 5)
{
$packages = $this->driverApiService->getRecentPackages($driverId, $limit);
return response()->json($packages);
}
}