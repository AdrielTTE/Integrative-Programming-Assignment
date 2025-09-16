<?php

namespace App\Factories;

class PackageAssignmentFactory
{
    public static function createFactory(): PackageAssignmentFactoryInterface
    {
        // Return the concrete factory for assigning packages to drivers
        return new DriverPackageAssignmentFactory();
    }
}
