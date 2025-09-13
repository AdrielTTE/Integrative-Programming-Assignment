<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use App\Services\SearchService;
use App\Services\Strategies\Search\AdminSearchStrategy;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    protected SearchService $searchService;
    
    public function __construct()
    {
        $strategy = new AdminSearchStrategy();
        $this->searchService = new SearchService($strategy);
    }

    public function search(Request $request)
    {
        $results = null;
        if ($request->query()) {
             $results = $this->searchService->executeSearch($request);
        }

        return view('admin.search.system_wide_search', [
            'results' => $results,
            'input' => $request->all()
        ]);
    }
    
    public function bulkAction(Request $request)
    {
        $request->validate([
            'package_ids' => 'required|array|min:1',
            'package_ids.*' => 'string',
            'action' => 'required|string|in:cancel',
        ]);

        $result = $this->searchService->performBulkAction(
            $request->input('package_ids'),
            $request->input('action')
        );

        return back()->with('success', $result['message']);
    }
}