<?php

namespace App\Services\Strategies\Search;

use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class CustomerSearchStrategy implements SearchStrategyInterface
{
    public function search(Request $request): array
    {
        // Build query parameters for the API call, ensuring the customer_id is included.
        $params = $request->only(['keyword', 'date_from', 'date_to']);
        $params['customer_id'] = Auth::id();

        return $params;
    }
}