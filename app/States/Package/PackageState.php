<?php

namespace App\States\Package;

use App\Models\Package;
use App\Models\User;

abstract class PackageState
{
    protected Package $package;

    public function __construct(Package $package)
    {
        $this->package = $package;
    }

    abstract public function getStatusName(): string;
    abstract public function getStatusColor(): string;
    abstract public function getCurrentLocation(): string;
    abstract public function canTransitionTo(string $newState): bool;
    abstract public function getAllowedTransitions(): array;

    public function canBeEdited(): bool
    {
        return false;
    }

    public function canBeCancelled(): bool
    {
        return false;
    }

    public function canBeAssigned(): bool
    {
        return false;
    }

    public function process(array $data = []): PackageState
    {
        throw new \Exception("Cannot process package in {$this->getStatusName()} state");
    }

    public function cancel(User $user): PackageState
    {
        throw new \Exception("Cannot cancel package in {$this->getStatusName()} state");
    }

    public function assign($driverId): PackageState
    {
        throw new \Exception("Cannot assign package in {$this->getStatusName()} state");
    }

    public function deliver(array $proofData = []): PackageState
    {
        throw new \Exception("Cannot deliver package in {$this->getStatusName()} state");
    }

    protected function transitionTo(string $newStateClass): PackageState
    {
        $this->package->package_status = (new $newStateClass($this->package))->getStatusName();
        $this->package->save();
        
        return new $newStateClass($this->package);
    }
}
