<?php

namespace App\Services\CustomerServices;

use App\Models\Package;
use App\Services\Api\PackageService;
use App\Services\Api\DeliveryDriverService;
use App\Services\Api\DeliveryService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class CustomerNotificationService{

protected string $baseUrl;
    public function __construct(){
        $this->baseUrl = config('services.api.base_url', 'http://localhost:8001/api');
    }

    public function getNotifications(string $customerId){
        $response = Http::get("{$this->baseUrl}/notifications/getByCustomerId/{$customerId}");

         if ($response->failed()) {
        return 0;
    }

    return $response->json();
    }

    public function updateReadAt(string $notification_id){
        $response = Http::patch("{$this->baseUrl}/notifications/markAsRead/{$notification_id}");
         if ($response->failed()) {

        return 0;
    }
    return $response->json();

}

}
