<?php

namespace App\Factories\Driver;

use Illuminate\View\View;

/**
 * Abstract Creator - defines the factory method
 */
abstract class DriverViewFactory
{
    /**
     * Factory Method - subclasses will implement this to create specific views
     */
    abstract public function createView(): DriverViewInterface;

    /**
     * Template method that uses the factory method
     */
    public function render(): View
    {
        $view = $this->createView();
        return $view->render();
    }
}