<?php

namespace App\Factories;

use Illuminate\Support\Facades\Http;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Package;
use App\Models\Customer;

/**
 * The Concrete Creator (Factory)
 * Overrides the factory method to return an instance of a Concrete Product.
 */
class StandardAssignmentViewFactory extends AssignmentViewFactory
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.api.base_url', 'http://localhost:8001/api');
    }

    /**
     * Implementation of the factory method.
     * This method creates and returns the StandardAssignmentView product.
     */
    public function createView(): AssignmentView
    {
        $packages = $this->fetchUnassignedPackages();
        return new StandardAssignmentView($packages);
    }

    /**
     * Fetches the data needed by the Product.
     */
    protected function fetchUnassignedPackages(): LengthAwarePaginator
    {
        $response = Http::get("{$this->baseUrl}/package/unassigned", [
            'page' => request('page', 1)
        ])->throw()->json();

        $items = collect($response['data'] ?? [])->map(function ($item) {
            $package = new Package((array)$item);
            if (!empty($item['customer'])) {
                $package->setRelation('customer', new Customer((array)$item['customer']));
            }
            return $package;
        });

        return new LengthAwarePaginator(
            $items,
            $response['total'] ?? 0,
            $response['per_page'] ?? 15,
            $response['current_page'] ?? 1,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }
}