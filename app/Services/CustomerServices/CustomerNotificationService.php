<?php

namespace App\Services\CustomerServices;

use App\Models\Package;
use App\Services\Api\PackageService;
use App\Services\Api\DeliveryDriverService;
use App\Services\Api\DeliveryService;
use Illuminate\Support\Collection;

class CustomerNotificationService{

     protected PackageService $packageService;

    protected DeliveryService $deliveryService;


    public function __construct(PackageService $packageService,DeliveryService $deliveryService){
        $this->packageService = $packageService;
        $this->deliveryService = $deliveryService;
    }
}
