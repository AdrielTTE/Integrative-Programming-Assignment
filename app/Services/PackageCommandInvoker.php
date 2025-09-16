<?php

namespace App\Services;

use App\Commands\PackageCommandInterface;
use Illuminate\Support\Collection;

class PackageCommandInvoker
{
    private Collection $history;

    public function __construct()
    {
        $this->history = collect();
    }

    public function execute(PackageCommandInterface $command): mixed
    {
        try {
            $result = $command->execute();
            
            // Store command in history for potential undo
            $this->history->push($command);
            
            return $result;
        } catch (\Exception $e) {
            \Log::error('Command execution failed', [
                'command' => get_class($command),
                'description' => $command->getDescription(),
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function undo(): mixed
    {
        if ($this->history->isEmpty()) {
            throw new \Exception('No commands to undo');
        }

        $lastCommand = $this->history->pop();
        
        if (!$lastCommand->canUndo()) {
            throw new \Exception('Last command cannot be undone');
        }

        return $lastCommand->undo();
    }

    public function getHistory(): Collection
    {
        return $this->history->map(function ($command) {
            return $command->getDescription();
        });
    }

    public function clearHistory(): void
    {
        $this->history = collect();
    }
}