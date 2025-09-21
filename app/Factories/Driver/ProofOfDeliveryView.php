<?php

namespace App\Factories\Driver;

use Illuminate\View\View;
use stdClass;

/**
 * Concrete Product - implementation for proof of delivery view
 */
class ProofOfDeliveryView implements DriverViewInterface
{
    protected $package;
    protected string $viewName = 'DriverViews.proof-of-delivery';

    public function __construct($package)
    {
        $this->package = $package;
    }

    public function render(): View
    {
        return view($this->viewName, [
            'package' => $this->package
        ]);
    }
}