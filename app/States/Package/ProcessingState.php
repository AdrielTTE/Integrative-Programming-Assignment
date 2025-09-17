<?php

namespace App\States\Package;

use App\Models\Package;
use App\Models\DeliveryAssignment;

class ProcessingState extends PackageState
{
    public function getStatusName(): string
    {
        return Package::STATUS_PROCESSING;
    }

    public function getStatusColor(): string
    {
        return 'info';
    }

    public function getCurrentLocation(): string
    {
        return 'At sorting facility';
    }

    public function canTransitionTo(string $newState): bool
    {
        return in_array($newState, [Package::STATUS_IN_TRANSIT, Package::STATUS_CANCELLED]);
    }

    public function getAllowedTransitions(): array
    {
        return [Package::STATUS_IN_TRANSIT, Package::STATUS_CANCELLED];
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
        return $this->transitionTo(InTransitState::class);
    }

    public function cancel(\App\Models\User $user): PackageState
    {
        return $this->transitionTo(CancelledState::class);
    }

    public function assign($driverId): PackageState
    {
        // Create delivery assignment
        DeliveryAssignment::create([
            'package_id' => $this->package->package_id,
            'driver_id' => $driverId,
            'assigned_at' => now()
        ]);
        
        return $this->transitionTo(InTransitState::class);
    }
}