<?php

namespace App\Commands;

use App\Models\Package;
use Illuminate\Support\Facades\DB;
use Exception;
// --- FIX #1: IMPORT THE CORRECT SERVICE ---
use App\Services\PackageService; 

class CancelPackageCommand extends AbstractPackageCommand 
{
    private string $packageId;
    private ?string $originalStatus = null;

    // --- FIX #2: USE THE CORRECT TYPE HINT ---
    public function __construct(PackageService $packageService, string $packageId)
    {
        // Call the parent constructor to set up the service and userId
        parent::__construct($packageService); 
        $this->packageId = $packageId;
    }

    public function execute(): mixed
    {
        return DB::transaction(function () {
            // Find the package, ensuring it belongs to the authenticated user
            $package = Package::where('package_id', $this->packageId)
                             ->where('user_id', $this->userId)
                             ->first();

            if (!$package) {
                throw new Exception('Package not found or access denied');
            }

            if (!$package->canBeCancelled()) {
                throw new Exception('Package cannot be cancelled in its current status: ' . $package->package_status);
            }

            // Store original status for potential undo
            $this->originalStatus = $package->package_status;

            // Use a dedicated service method if it exists, or update directly
            $package->updateStatus(Package::STATUS_CANCELLED);
            
            $this->result = $package;
            // The logOperation method is in the parent, so it's already fixed.
            $this->logOperation('cancel', $package->package_id);
            
            return $package;
        });
    }

    public function getDescription(): string
    {
        return "Cancel package {$this->packageId} for user: {$this->userId}";
    }

    public function canUndo(): bool
    {
        return $this->originalStatus !== null && $this->result;
    }

    public function undo(): mixed
    {
        if ($this->canUndo()) {
            $this->result->updateStatus($this->originalStatus);
            $this->logOperation('undo_cancel', $this->result->package_id);
            return $this->result;
        }
        return false;
    }
}