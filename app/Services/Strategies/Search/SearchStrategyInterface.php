<?php

namespace App\Services\Strategies\Search;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

interface SearchStrategyInterface
{
    /**
     * Execute the search strategy.
     *
     * @param Request $request The search request.
     * @return LengthAwarePaginator The paginated search results.
     */
    public function search(Request $request): LengthAwarePaginator;
}