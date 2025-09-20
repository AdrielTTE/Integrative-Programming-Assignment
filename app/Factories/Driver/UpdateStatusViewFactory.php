<?php

namespace App\Factories\Driver;

use App\Services\DriverPackageService;
use Illuminate\Support\Facades\Auth;

/**
 * Concrete Creator - implements the factory method to create UpdateStatusView
 */
class UpdateStatusViewFactory extends DriverViewFactory
{
    protected DriverPackageService $packageService;

    public function __construct(DriverPackageService $packageService)
    {
        $this->packageService = $packageService;
    }
    
    /**
     * Factory Method Implementation - creates UpdateStatusView
     */
    public function createView(): DriverViewInterface
    {
        $driverId = Auth::user()->user_id;
        $packages = $this->packageService->getPackagesForStatusUpdate($driverId);
        
        return new UpdateStatusView($packages);
    }
}