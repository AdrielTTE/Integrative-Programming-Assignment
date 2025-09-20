<?php

namespace App\Factories\Driver;

use Illuminate\View\View;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Concrete Product - implementation for update status view
 */
class UpdateStatusView implements DriverViewInterface
{
    protected LengthAwarePaginator $packages;
    protected string $viewName = 'DriverViews.update-status';

    public function __construct(LengthAwarePaginator $packages)
    {
        $this->packages = $packages;
    }

    public function render(): View
    {
        return view($this->viewName, [
            'packages' => $this->packages
        ]);
    }
}