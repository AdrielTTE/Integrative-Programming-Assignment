<?php

namespace App\Factories\Driver;

use Illuminate\View\View;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Concrete Product - for the Delivery History view
 */
class DeliveryHistoryView implements DriverViewInterface
{
    protected LengthAwarePaginator $packages;
    protected string $viewName = 'DriverViews.delivery-history'; // Points to our new view

    public function __construct(LengthAwarePaginator $packages)
    {
        $this->packages = $packages;
    }

    public function render(): View
    {
        return view($this->viewName, ['packages' => $this->packages]);
    }
}