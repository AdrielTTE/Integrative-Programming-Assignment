<?php

namespace App\Services;

use App\Services\Strategies\Search\SearchStrategyInterface;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Http;
use App\Models\Package;
use App\Models\User; // It's good practice to import the User model
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

    /**
     * --- THIS IS THE CORRECTED METHOD ---
     */
    public function executeSearch(Request $request): LengthAwarePaginator
    {
        // 1. Get search parameters from the strategy
        $params = $this->searchStrategy->search($request);
        $params['page'] = $request->input('page', 1);

        // 2. Call the API (no more try-catch block)
        $response = Http::get("{$this->baseUrl}/search/packages", $params)->throw()->json();

        // 3. Map the JSON data to Package models
        $items = collect($response['data'] ?? [])->map(function ($item) {
            $package = new Package();
            $package->fill((array)$item);
            
            // This is good practice: create and set the User relationship object
            if (!empty($item['user'])) {
                $package->setRelation('user', new User((array)$item['user']));
            }
            return $package;
        });

        // 4. Create and return the paginator object
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