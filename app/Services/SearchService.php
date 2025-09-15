<?php

namespace App\Services;

use App\Services\Strategies\Search\SearchStrategyInterface;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Http;
use App\Models\Package;
use Illuminate\Http\Client\RequestException; 

class SearchService
{
    protected SearchStrategyInterface $searchStrategy;
    protected string $baseUrl;

    public function __construct(SearchStrategyInterface $searchStrategy)
    {
        $this->searchStrategy = $searchStrategy;
        $this->baseUrl = config('services.api.base_url', 'http://localhost:8001/api');
    }

    public function executeSearch(Request $request): LengthAwarePaginator
    {
        $params = $this->searchStrategy->search($request);
        $params['page'] = $request->input('page', 1);

       
        $response = Http::get("{$this->baseUrl}/search/packages", $params)->throw()->json();

        $items = collect($response['data'] ?? [])->map(function ($item) {
            $package = new Package();
            $package->fill((array)$item);
            return $package;
        });

        return new LengthAwarePaginator(
            $items,
            $response['total'] ?? 0,
            $response['per_page'] ?? 15,
            $response['current_page'] ?? 1,
            ['path' => $request->url(), 'query' => $request->query()]
        );
    }
    
    public function performBulkAction(array $packageIds, string $action): array
    {
        if (empty($packageIds)) {
            return ['success' => 0, 'failed' => 0, 'message' => 'No packages selected.'];
        }

        $response = Http::post("{$this->baseUrl}/packages/bulk-action", [
            'package_ids' => $packageIds,
            'action' => $action
        ])->throw()->json();
        
        return [
            'success' => $response['success_count'] ?? 0,
            'failed' => $response['failed_count'] ?? count($packageIds),
            'message' => $response['message'] ?? "Bulk action processed."
        ];
    }
}