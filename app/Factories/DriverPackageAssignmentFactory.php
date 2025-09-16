<?php

namespace App\Factories;

use App\Models\Package;
use App\Models\User;

class DriverPackageAssignmentFactory implements PackageAssignmentFactoryInterface
{
    public function assignPackage(int $packageId, int $driverId)
    {
        // Fetch package by ID
        $package = Package::findOrFail($packageId);

        // Check if the driver exists and assign the package
        $driver = User::findOrFail($driverId);

        // Assign the package to the driver
        $package->driver_id = $driver->id;
        $package->save();

        return $package;
    }
}
