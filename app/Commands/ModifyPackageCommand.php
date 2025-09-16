<?php

namespace App\Commands;

use App\Models\Package;
use Illuminate\Support\Facades\DB;
use Exception;

class ModifyPackageCommand extends AbstractPackageCommand
{
    private string $packageId;
    private array $originalData = [];

    public function __construct($packageService, string $packageId, array $data)
    {
        parent::__construct($packageService, $data);
        $this->packageId = $packageId;
    }

    public function execute(): mixed
    {
        return DB::transaction(function () {
            $package = Package::where('package_id', $this->packageId)
                             ->where('user_id', $this->userId)
                             ->first();

            if (!$package) {
                throw new Exception('Package not found or access denied');
            }

            if (!$package->canBeEdited()) {
                throw new Exception('Package cannot be modified in current status: ' . $package->package_status);
            }

            // Store original data for undo
            $this->originalData = $package->only([
                'package_weight', 'package_dimensions', 'package_contents',
                'sender_address', 'recipient_address', 'priority', 'notes'
            ]);

            $updatedPackage = $this->packageService->updatePackage($package, $this->data);
            
            $this->result = $updatedPackage;
            $this->logOperation('modify', $package->package_id);
            
            return $updatedPackage;
        });
    }

    public function getDescription(): string
    {
        return "Modify package {$this->packageId} for user: {$this->userId}";
    }

    public function canUndo(): bool
    {
        return !empty($this->originalData) && $this->result && $this->result->canBeEdited();
    }

    public function undo(): mixed
    {
        if ($this->canUndo()) {
            $package = $this->packageService->updatePackage($this->result, $this->originalData);
            $this->logOperation('undo_modify', $this->result->package_id);
            return $package;
        }
        return false;
    }
}