<?php

namespace App\Factories\Driver;

use Illuminate\View\View;

/**
 * Concrete Product - for the Package Details view
 */
class PackageDetailsView implements DriverViewInterface
{
    protected object $package;
    protected string $viewName = 'DriverViews.package-details';

    public function __construct(object $package)
    {
        $this->package = $package;
    }

    public function render(): View
    {
        return view($this->viewName, ['package' => $this->package]);
    }
}