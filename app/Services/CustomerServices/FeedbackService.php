<?php

namespace App\Services\CustomerServices;

use App\Models\Package;
use App\Services\Api\PackageService;
use App\Services\Api\DeliveryDriverService;
use App\Services\Api\DeliveryService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class FeedbackService{

    protected string $baseUrl;

    public function __construct()
    {
        // You can make this configurable via .env
        $this->baseUrl = config('services.api.base_url', 'http://localhost:8001/api');
    }

    public function getDeliveredPackages(string $status, int $page, int $pageSize, string $customerId){
        $url = "{$this->baseUrl}/package/getPackagesByStatus/{$status}/{$page}/{$pageSize}/{$customerId}";

    $response = Http::get($url);

    if ($response->successful()) {
    $json = $response->json();
    return collect($json['data'] ?? []);
}


    // Handle errors gracefully
    return collect([
        'error' => true,
        'message' => 'Failed to fetch package data',
        'status' => $response->status(),
    ]);
    }
    }

