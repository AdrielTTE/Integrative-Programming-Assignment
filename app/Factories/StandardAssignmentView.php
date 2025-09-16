<?php

namespace App\Factories;

use Illuminate\View\View;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * The Concrete Product
 * Implements the Product interface. This class is responsible for rendering
 * the specific view for standard package assignments.
 */
class StandardAssignmentView implements AssignmentView
{
    protected string $viewName = 'admin.packages.assign';
    protected LengthAwarePaginator $packages;

    public function __construct(LengthAwarePaginator $packages)
    {
        $this->packages = $packages;
    }

    public function render(): View
    {
        return view($this->viewName, ['packages' => $this->packages]);
    }
}