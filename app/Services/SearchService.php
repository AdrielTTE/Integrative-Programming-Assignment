<?php

namespace App\Services;

use App\Services\Strategies\Search\SearchStrategyInterface;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use App\Models\Package;

class SearchService
{
    protected SearchStrategyInterface $searchStrategy;

    public function __construct(SearchStrategyInterface $searchStrategy)
    {
        $this->searchStrategy = $searchStrategy;
    }

    public function executeSearch(Request $request): LengthAwarePaginator
    {
        return $this->searchStrategy->search($request);
    }

    /**
     * Apply bulk operations to a filtered set of package IDs.
     *
     * @param array $packageIds
     * @param string $action
     * @return array
     */
    public function performBulkAction(array $packageIds, string $action): array
    {
        if (empty($packageIds)) {
            return ['success' => 0, 'failed' => 0, 'message' => 'No packages selected.'];
        }

        // Securely ensure all IDs are valid before processing
        $validatedIds = DB::table('Package')->whereIn('package_id', $packageIds)->pluck('package_id')->toArray();

        $count = 0;
        switch ($action) {
            case 'cancel':
                $count = Package::whereIn('package_id', $validatedIds)
                    ->where('package_status', 'PENDING')
                    ->update(['package_status' => 'CANCELLED']);
                break;
            // Add other bulk actions here, e.g., 'archive', 'export'
        }

        return [
            'success' => $count,
            'failed' => count($packageIds) - $count,
            'message' => "Successfully processed {$count} packages."
        ];
    }
}