<?php

namespace App\Factories\Driver;

use App\Services\DriverPackageService;

/**
 * Concrete Creator - implements the factory method to create ProofOfDeliveryView
 */
class ProofOfDeliveryViewFactory extends DriverViewFactory
{
    protected DriverPackageService $packageService;
    protected string $packageId;

    public function __construct(DriverPackageService $packageService, string $packageId)
    {
        $this->packageService = $packageService;
        $this->packageId = $packageId;
    }
    
    /**
     * Factory Method Implementation - creates ProofOfDeliveryView
     */
    public function createView(): DriverViewInterface
    {
        $package = $this->packageService->getPackageDetails($this->packageId);
        
        return new ProofOfDeliveryView($package);
    }
}