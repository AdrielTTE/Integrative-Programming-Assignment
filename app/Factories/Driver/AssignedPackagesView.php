<?php

namespace App\Factories\Driver;

use Illuminate\View\View;
use Illuminate\Pagination\LengthAwarePaginator;

// This is a "Concrete Product"
class AssignedPackagesView implements DriverViewInterface
{
    protected LengthAwarePaginator $packages;
    protected string $viewName = 'DriverViews.assignedPackages';

    public function __construct(LengthAwarePaginator $packages)
    {
        $this->packages = $packages;
    }

    public function render(): View
    {
        return view($this->viewName, ['packages' => $this->packages]);
    }
}