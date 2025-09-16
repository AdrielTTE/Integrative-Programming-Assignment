<?php

namespace App\Factories;

interface PackageAssignmentFactoryInterface
{
    public function assignPackage(int $packageId, int $driverId);
}
