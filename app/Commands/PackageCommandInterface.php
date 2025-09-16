<?php

namespace App\Commands;

interface PackageCommandInterface
{
    public function execute(): mixed;
    public function getDescription(): string;
    public function canUndo(): bool;
    public function undo(): mixed;
}