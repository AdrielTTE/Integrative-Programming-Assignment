<?php

namespace App\Factories\Driver;

use Illuminate\View\View;

/**
 * Abstract Factory class for creating driver views
 */
abstract class DriverViewFactory
{
    /**
     * Factory method to create views
     * 
     * @return DriverViewInterface
     */
    abstract public function createView(): DriverViewInterface;
    
    /**
     * Render the view created by the factory
     * 
     * @return View
     */
    public function render(): View
    {
        $view = $this->createView();
        return $view->render();
    }
}