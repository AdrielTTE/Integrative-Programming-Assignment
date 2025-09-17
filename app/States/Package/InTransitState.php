<?php

namespace App\States\Package;

use App\Models\Package;

class InTransitState extends PackageState
{
    public function getStatusName(): string
    {
        return Package::STATUS_IN_TRANSIT;
    }

    public function getStatusColor(): string
    {
        return 'primary';
    }

    public function getCurrentLocation(): string
    {
        return 'In transit to destination';
    }

    public function canTransitionTo(string $newState): bool
    {
        return in_array($newState, [
            Package::STATUS_OUT_FOR_DELIVERY, 
            Package::STATUS_RETURNED, 
            Package::STATUS_FAILED
        ]);
    }

    public function getAllowedTransitions(): array
    {
        return [Package::STATUS_OUT_FOR_DELIVERY, Package::STATUS_RETURNED, Package::STATUS_FAILED];
    }

    public function process(array $data = []): PackageState
    {
        return $this->transitionTo(OutForDeliveryState::class);
    }
}