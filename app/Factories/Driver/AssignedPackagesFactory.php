<?php

namespace App\Factories\Driver;

use App\Services\DriverPackageService;

// This is a "Concrete Creator"
class AssignedPackagesFactory extends DriverViewFactory
{
    protected DriverPackageService $packageService;

    public function __construct(DriverPackageService $packageService)
    {
        $this->packageService = $packageService;
    }
    public function createView(): DriverViewInterface
    {
        $packages = $this->packageService->getAssignedPackages();
        return new AssignedPackagesView($packages);
    }
}