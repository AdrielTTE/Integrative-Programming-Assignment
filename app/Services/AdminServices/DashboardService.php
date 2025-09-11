<?php

namespace App\Services\AdminServices;

use App\Models\Package;
use App\Services\Api\PackageService;
use App\Services\Api\DeliveryDriverService;
use App\Services\Api\DeliveryService;
use Illuminate\Support\Collection;
class DashboardService{

    protected PackageService $packageService;
    protected DeliveryDriverService $deliveryDriverService;
    protected DeliveryService $deliveryService;


    public function __construct(PackageService $packageService, DeliveryDriverService $deliveryDriverService, DeliveryService $deliveryService){
        $this->packageService = $packageService;
        $this->deliveryDriverService = $deliveryDriverService;
        $this->deliveryService = $deliveryService;
    }
    public function getTotalPackages(): int
    {
        return $this->packageService->getCountPackage();

    }

    public function getDriverCountByStatus(string $status): int{
        return $this->deliveryDriverService->getCountByStatus($status);

    }

    public function getTotalDeliveries(): int{
        return $this->deliveryService->getCountDeliveries();

    }

    public function getDeliveryCountByStatus(string $status): int{
        return $this->deliveryService->getCountByStatus($status);
    }

    public function recentPackages(int $noOfRecords): Collection{
        return $this->packageService->getRecentPackages($noOfRecords);
    }

    public function getDrivers(int $page, int $pageSize, string $status): Collection{
        return $this->deliveryDriverService->getBatch($page, $pageSize, $status);
    }

    public function getPackageCountByStatus(string $status): Collection
{
    $rawData = $this->packageService->getCountByStatus($status);
    return collect($rawData->pluck('count', 'package_status')->toArray());
}

}
