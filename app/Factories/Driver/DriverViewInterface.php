<?php

namespace App\Factories\Driver;

use Illuminate\View\View;

/**
 * Interface for all driver views in the Factory Pattern
 */
interface DriverViewInterface
{
    /**
     * Render the view
     * 
     * @return View
     */
    public function render(): View;
}