<?php

namespace App\States\Package;

use App\Models\Package;

class ReturnedState extends PackageState
{
    public function getStatusName(): string
    {
        return Package::STATUS_RETURNED;
    }

    public function getStatusColor(): string
    {
        return 'secondary';
    }

    public function getCurrentLocation(): string
    {
        return 'Returned to sender';
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