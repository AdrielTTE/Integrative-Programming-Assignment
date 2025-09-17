<?php

namespace App\States\Package;

use App\Models\Package;

class PendingState extends PackageState
{
    public function getStatusName(): string
    {
        return Package::STATUS_PENDING;
    }

    public function getStatusColor(): string
    {
        return 'warning';
    }

    public function getCurrentLocation(): string
    {
        return 'Package registered, awaiting pickup';
    }

    public function canTransitionTo(string $newState): bool
    {
        return in_array($newState, [Package::STATUS_PROCESSING, Package::STATUS_CANCELLED]);
    }

    public function getAllowedTransitions(): array
    {
        return [Package::STATUS_PROCESSING, Package::STATUS_CANCELLED];
    }

    public function canBeEdited(): bool
    {
        return true;
    }

    public function canBeCancelled(): bool
    {
        return true;
    }

    public function canBeAssigned(): bool
    {
        return true;
    }

    public function process(array $data = []): PackageState
    {
        return $this->transitionTo(ProcessingState::class);
    }

    public function cancel(\App\Models\User $user): PackageState
    {
        return $this->transitionTo(CancelledState::class);
    }
}