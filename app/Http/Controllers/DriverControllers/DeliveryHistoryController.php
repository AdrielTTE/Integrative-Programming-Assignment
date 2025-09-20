<?php

namespace App\Http\Controllers\DriverControllers;

use App\Http\Controllers\Controller;
use App\Factories\Driver\DeliveryHistoryFactory;
use App\Services\DriverPackageService;

class DeliveryHistoryController extends Controller
{
    /**
     * Display the driver's delivery history using the Factory Method pattern.
     */
    public function index(DriverPackageService $packageService)
    {
        // 1. Instantiate the factory for the history view
        $factory = new DeliveryHistoryFactory($packageService);
        
        // 2. Let the factory create and render the view
        return $factory->render();
    }
}