<?php

namespace App\Commands;

use App\Models\Package;
use Illuminate\Support\Facades\DB;
use Exception;

class CreatePackageCommand extends AbstractPackageCommand
{
    public function execute(): mixed
    {
        return DB::transaction(function () {
            // Add customer_id to data
            $this->data['user_id'] = $this->userId;
            
            // Generate IDs if not provided
            if (empty($this->data['package_id'])) {
                $this->data['package_id'] = Package::generatePackageId();
            }
            
            if (empty($this->data['tracking_number'])) {
                $this->data['tracking_number'] = Package::generateTrackingNumber();
            }

            // Create package through service
            $package = $this->packageService->createPackage($this->data);
            
            $this->result = $package;
            $this->logOperation('create', $package->package_id);
            
            return $package;
        });
    }

    public function getDescription(): string
    {
        return "Create new delivery request for customer: {$this->customerId}";
    }

    public function canUndo(): bool
    {
        return true;
    }

    public function undo(): mixed
    {
        if ($this->result && $this->result->canBeCancelled()) {
            $this->result->package_status = Package::STATUS_CANCELLED;
            $this->result->save();
            $this->logOperation('undo_create', $this->result->package_id);
            return true;
        }
        return false;
    }
}