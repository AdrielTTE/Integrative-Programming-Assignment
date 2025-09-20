<?php

namespace App\Factories\Driver;

use App\Services\DriverPackageService;

/**
 * Concrete Creator - its job is to create the DeliveryHistoryView
 */
class DeliveryHistoryFactory extends DriverViewFactory
{
    protected DriverPackageService $packageService;

    public function __construct(DriverPackageService $packageService)
    {
        $this->packageService = $packageService;
    }
    
    /**
     * Factory Method Implementation
     */
    public function createView(): DriverViewInterface
    {
        // Use the service to get the historical data
        $packages = $this->packageService->getDeliveryHistory();
        
        // Create the specific view product with that data
        return new DeliveryHistoryView($packages);
    }
}