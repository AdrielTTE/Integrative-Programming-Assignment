<?php

namespace App\States\Package;

use App\Models\Package;

class CancelledState extends PackageState
{
    public function getStatusName(): string
    {
        return Package::STATUS_CANCELLED;
    }

    public function getStatusColor(): string
    {
        return 'danger';
    }

    public function getCurrentLocation(): string
    {
        return 'Shipment cancelled';
    }

    public function canTransitionTo(string $newState): bool
    {
        return false; // Terminal state
    }

    public function getAllowedTransitions(): array
    {
        return [];
    }
}