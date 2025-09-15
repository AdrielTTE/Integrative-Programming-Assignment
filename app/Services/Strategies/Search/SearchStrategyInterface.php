<?php

namespace App\Services\Strategies\Search;

use Illuminate\Http\Request;

interface SearchStrategyInterface
{
    /**
     * Prepare the search parameters for an API request.
     *
     * @param Request $request The search request.
     * @return array The array of parameters to be sent to the API.
     */
    // CHANGE THE RETURN TYPE HERE
    public function search(Request $request): array;
}