<?php

namespace App\Factories\Driver;

use App\Services\DriverPackageService;

/**
 * Concrete Creator - its job is to create the PackageDetailsView
 */
class PackageDetailsFactory extends DriverViewFactory
{
    protected DriverPackageService $packageService;
    protected string $packageId;

    public function __construct(DriverPackageService $packageService, string $packageId)
    {
        $this->packageService = $packageService;
        $this->packageId = $packageId;
    }
    
    public function createView(): DriverViewInterface
    {
        $package = $this->packageService->getPackageDetails($this->packageId);
        return new PackageDetailsView($package);
    }
}