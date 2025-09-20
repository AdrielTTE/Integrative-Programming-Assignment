<?php

namespace App\Factories;

use App\Models\Package;
use App\States\Package\PackageState;
use App\States\Package\PendingState;
use App\States\Package\ProcessingState;
use App\States\Package\InTransitState;
use App\States\Package\OutForDeliveryState;
use App\States\Package\DeliveredState;
use App\States\Package\CancelledState;
use App\States\Package\FailedState;
use App\States\Package\ReturnedState;

class PackageStateFactory
{
    private static array $stateMap = [
        Package::STATUS_PENDING => PendingState::class,
        Package::STATUS_PROCESSING => ProcessingState::class,
        Package::STATUS_IN_TRANSIT => InTransitState::class,
        Package::STATUS_OUT_FOR_DELIVERY => OutForDeliveryState::class,
        Package::STATUS_DELIVERED => DeliveredState::class,
        Package::STATUS_CANCELLED => CancelledState::class,
        Package::STATUS_FAILED => FailedState::class,
        Package::STATUS_RETURNED => ReturnedState::class,
    ];

    public static function create(Package $package): PackageState
    {
        $status = strtolower(trim($package->package_status));
        
        // Handle both uppercase and lowercase statuses from database
        if (!isset(self::$stateMap[$status])) {
            // Try to find a matching status by converting keys to lowercase
            foreach (self::$stateMap as $key => $stateClass) {
                if (strtolower($key) === $status) {
                    return new $stateClass($package);
                }
            }
            
            // If still not found, throw exception with helpful message
            throw new \InvalidArgumentException(
                "Unknown package status: '{$package->package_status}'. " .
                "Valid statuses are: " . implode(', ', array_keys(self::$stateMap))
            );
        }

        $stateClass = self::$stateMap[$status];
        return new $stateClass($package);
    }

    public static function createByStatus(string $status, Package $package): PackageState
    {
        $status = strtolower(trim($status));
        
        if (!isset(self::$stateMap[$status])) {
            // Try to find a matching status by converting keys to lowercase
            foreach (self::$stateMap as $key => $stateClass) {
                if (strtolower($key) === $status) {
                    return new $stateClass($package);
                }
            }
            
            throw new \InvalidArgumentException(
                "Unknown package status: '{$status}'. " .
                "Valid statuses are: " . implode(', ', array_keys(self::$stateMap))
            );
        }

        $stateClass = self::$stateMap[$status];
        return new $stateClass($package);
    }

    public static function getAvailableStates(): array
    {
        return array_keys(self::$stateMap);
    }
    
    /**
     * Normalize status to lowercase for consistent handling
     */
    public static function normalizeStatus(string $status): string
    {
        return strtolower(trim($status));
    }
}