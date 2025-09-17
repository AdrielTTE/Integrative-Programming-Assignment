<?php

namespace App\States\Package;

use App\Models\Package;

class DeliveredState extends PackageState
{
    public function getStatusName(): string
    {
        return Package::STATUS_DELIVERED;
    }

    public function getStatusColor(): string
    {
        return 'success';
    }

    public function getCurrentLocation(): string
    {
        return 'Delivered successfully';
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