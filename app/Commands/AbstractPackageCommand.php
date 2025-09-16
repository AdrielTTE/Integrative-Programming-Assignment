<?php

namespace App\Commands;

use App\Services\PackageService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

abstract class AbstractPackageCommand implements PackageCommandInterface
{
    protected PackageService $packageService;
    protected array $data;
    protected mixed $result;
    protected ?string $userId;

    public function __construct(PackageService $packageService, array $data = [])
    {
        $this->packageService = $packageService;
        $this->data = $data;
        $this->userId = Auth::id();
    }

    public function canUndo(): bool
    {
        return false; 
    }

    public function undo(): mixed
    {
        throw new \Exception("Undo not supported for this command");
    }

    /**
     * --- THIS METHOD IS NOW CORRECTED ---
     */
    protected function logOperation(string $operation, mixed $result): void
    {
        Log::info("Customer Package Operation", [
            'user_id' => $this->userId,
            'operation' => $operation,
            'command' => static::class,
            'result' => $result
        ]);
    }
}