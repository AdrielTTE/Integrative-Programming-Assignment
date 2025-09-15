<?php

namespace App\Services\Strategies\Search;

use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class AdminSearchStrategy implements SearchStrategyInterface
{
    public function search(Request $request): array
    {
        // This strategy now returns an array of query parameters for the API call.
        return $request->only([
            'keyword',
            'driver_status',
            'package_status',
            'date_from',
            'date_to'
        ]);
    }
}