<?php

namespace App\States\Package;

use App\Models\Package;

class FailedState extends PackageState
{
    public function getStatusName(): string
    {
        return Package::STATUS_FAILED;
    }

    public function getStatusColor(): string
    {
        return 'danger';
    }

    public function getCurrentLocation(): string
    {
        return 'Delivery attempt failed';
    }

    public function canTransitionTo(string $newState): bool
    {
        return in_array($newState, [Package::STATUS_IN_TRANSIT, Package::STATUS_RETURNED]);
    }

    public function getAllowedTransitions(): array
    {
        return [Package::STATUS_IN_TRANSIT, Package::STATUS_RETURNED];
    }

    public function process(array $data = []): PackageState
    {
        return $this->transitionTo(InTransitState::class);
    }
}