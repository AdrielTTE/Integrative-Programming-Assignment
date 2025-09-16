<?php

namespace App\Factories;

use Illuminate\View\View;

/**
 * The Product Interface
 * Declares the interface for the objects the factory method creates.
 */
interface AssignmentView
{
    public function render(): View;
}