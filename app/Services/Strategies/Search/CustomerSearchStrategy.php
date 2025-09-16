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
        $params = $request->only(['keyword', 'date_from', 'date_to']);
        $params['user_id'] = Auth::id();

        return $params;
    }
}