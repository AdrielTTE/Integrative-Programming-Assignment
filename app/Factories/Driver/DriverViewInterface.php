<?php

namespace App\Factories\Driver;

use Illuminate\View\View;

/**
 * Product Interface - defines the interface for all driver views
 */
interface DriverViewInterface
{
    public function render(): View;
}