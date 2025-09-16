<?php

namespace App\Factories;

use Illuminate\View\View;

/**
 * The Creator (Factory) Abstract Class
 * Declares the factory method that returns a Product object.
 */
abstract class AssignmentViewFactory
{
    // The factory method
    abstract public function createView(): AssignmentView;

    /**
     * The Creator's primary responsibility is not always creating products.
     * It usually contains some core business logic that relies on Product objects,
     * returned by the factory method.
     */
    public function renderView(): View
    {
        // Call the factory method to create a Product object.
        $assignmentView = $this->createView();
        
        // Now, use the product.
        return $assignmentView->render();
    }
}