<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\SearchService;
use App\Services\Strategies\Search\CustomerSearchStrategy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        Auth::loginUsingId('C004');
        $strategy = new CustomerSearchStrategy();
        $searchService = new SearchService($strategy);
        $results = $searchService->executeSearch($request);
        return view('search.customer_search', [
            'results' => $results,
            'input' => $request->all()
        ]);
    }
}