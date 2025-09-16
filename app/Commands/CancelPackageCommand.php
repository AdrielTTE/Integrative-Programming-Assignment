<?php

namespace App\Commands;

use App\Models\Package;
use Illuminate\Support\Facades\DB;
use Exception;

class CancelPackageCommand extends AbstractPackageCommand
{
    private string $packageId;
    private string $originalStatus;

    public function __construct($packageService, string $packageId)
    {
        parent::__construct($packageService);
        $this->packageId = $packageId;
    }

    public function execute(): mixed
    {
        return DB::transaction(function () {
            $package = Package::where('package_id', $this->packageId)
                             ->where('customer_id', $this->customerId)
                             ->first();

            if (!$package) {
                throw new Exception('Package not found or access denied');
            }

            if (!$package->canBeCancelled()) {
                throw new Exception('Package cannot be cancelled in current status: ' . $package->package_status);
            }

            // Store original status for potential undo
            $this->originalStatus = $package->package_status;
            
            $package->package_status = Package::STATUS_CANCELLED;
            $package->save();
            
            $this->result = $package;
            $this->logOperation('cancel', $package->package_id);
            
            return $package;
        });
    }

    public function getDescription(): string
    {
        return "Cancel package {$this->packageId} for customer: {$this->customerId}";
    }

    public function canUndo(): bool
    {
        return !empty($this->originalStatus) && 
               in_array($this->originalStatus, [Package::STATUS_PENDING, Package::STATUS_PROCESSING]);
    }

    public function undo(): mixed
    {
        if ($this->canUndo() && $this->result) {
            $this->result->package_status = $this->originalStatus;
            $this->result->save();
            $this->logOperation('undo_cancel', $this->result->package_id);
            return $this->result;
        }
        return false;
    }
}