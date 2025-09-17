<?php

namespace App\States\Package;

use App\Models\Package;
use App\Models\ProofOfDelivery;

class OutForDeliveryState extends PackageState
{
    public function getStatusName(): string
    {
        return Package::STATUS_OUT_FOR_DELIVERY;
    }

    public function getStatusColor(): string
    {
        return 'info';
    }

    public function getCurrentLocation(): string
    {
        return 'Out for delivery';
    }

    public function canTransitionTo(string $newState): bool
    {
        return in_array($newState, [
            Package::STATUS_DELIVERED, 
            Package::STATUS_FAILED, 
            Package::STATUS_RETURNED
        ]);
    }

    public function getAllowedTransitions(): array
    {
        return [Package::STATUS_DELIVERED, Package::STATUS_FAILED, Package::STATUS_RETURNED];
    }

    public function deliver(array $proofData = []): PackageState
    {
        $this->package->actual_delivery = now();
        
        // Create proof of delivery if provided
        if (!empty($proofData) && $this->package->delivery) {
            ProofOfDelivery::create(array_merge($proofData, [
                'delivery_id' => $this->package->delivery->delivery_id,
                'created_at' => now()
            ]));
        }
        
        return $this->transitionTo(DeliveredState::class);
    }
}
