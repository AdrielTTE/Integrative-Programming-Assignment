<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\SearchService;
use App\Services\Strategies\Search\CustomerSearchStrategy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SearchController extends Controller
{
    /**
     * Handles the customer-facing package search.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function search(Request $request)
    {
        // Set the strategy for a customer-specific search
        $strategy = new CustomerSearchStrategy();

        // Initialize the Search Service with the customer strategy
        $searchService = new SearchService($strategy);

        // Execute the search by calling the API
        $results = $searchService->executeSearch($request);

        // Return the view with the search results and original input
        return view('search.customer_search', [
            'results' => $results,
            'input' => $request->all()
        ]);
    }
}