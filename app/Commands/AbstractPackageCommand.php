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
    protected string $customerId;

    public function __construct(PackageService $packageService, array $data = [])
    {
        $this->packageService = $packageService;
        $this->data = $data;
        $this->customerId = Auth::id();
    }

    public function canUndo(): bool
    {
        return false; // Override in subclasses if undo is supported
    }

    public function undo(): mixed
    {
        throw new \Exception("Undo not supported for this command");
    }

    protected function logOperation(string $operation, mixed $result): void
    {
        Log::info("Customer Package Operation", [
            'customer_id' => $this->customerId,
            'operation' => $operation,
            'command' => static::class,
            'result' => $result
        ]);
    }
}