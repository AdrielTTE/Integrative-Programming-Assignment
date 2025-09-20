<?php

namespace App\Services\AdminServices;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Collection;

class DashboardService
{
    protected string $baseUrl;

    public function __construct()
    {
       
        $this->baseUrl = config('services.api.base_url', 'http://localhost:8001/api');
    }

    public function getTotalPackages(): int
{
    $response = Http::get("{$this->baseUrl}/package/getCountPackage");

    if ($response->failed()) {
        return 0;
    }

    $data = $response->json();


    if (isset($data['original']['count'])) {
        return (int) $data['original']['count'];
    }

    return 0;
}



    public function getDriverCountByStatus(string $status): int
    {
        $response = Http::get("{$this->baseUrl}/deliveryDriver/getByStatus/{$status}");

    if ($response->failed()) {
        return 0;
    }

    $data = $response->json();

    return isset($data['count']) ? (int) $data['count'] : 0;
    }
    public function getTotalDeliveries(): int
    {
        $response = Http::get("{$this->baseUrl}/delivery/getCountDeliveries");
         if ($response->failed()) {
        return 0;
    }

    $data = $response->json();

    return isset($data['count']) ? (int) $data['count'] : 0;
    }

    public function getDeliveryCountByStatus(string $status): Collection
    {
        $response = Http::get("{$this->baseUrl}/delivery/getCountByStatus/{$status}");
        $rawData = collect($response->json());

        return collect($rawData->pluck('count', 'delivery_status')->toArray());
    }

    public function recentPackages(int $noOfRecords): Collection
    {
        $response = Http::get("{$this->baseUrl}/package/getRecentPackages/{$noOfRecords}");
        return collect($response->json());
    }

    public function getDrivers(int $page, int $pageSize, string $status): Collection
    {
        $response = Http::get("{$this->baseUrl}/deliveryDriver/getBatch/$page/$pageSize/$status", [
            'pageSize' => $pageSize,
            'status'   => $status,
        ]);
        return collect($response->json());
    }

    public function getPackageCountByStatus(string $status): Collection
    {
        $response = Http::get("{$this->baseUrl}/package/getCountByStatus/{$status}");
        $rawData = collect($response->json());

        return collect($rawData->pluck('count', 'package_status')->toArray());
    }

    public function getVehicleCountByStatus(string $status): Collection
    {
        $response = Http::get("{$this->baseUrl}/vehicle/getCountByStatus/{$status}");
        $rawData = collect($response->json());

        return collect($rawData->pluck('count', 'vehicle_status')->toArray());
    }

     public function getCustomerCountByStatus(string $status): Collection
    {
        $response = Http::get("{$this->baseUrl}/customer/getCountByStatus/{$status}");
        $rawData = collect($response->json());

        return collect($rawData->pluck('count', 'customer_status')->toArray());
    }

}
