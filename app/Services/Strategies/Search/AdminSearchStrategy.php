<?php

namespace App\Services\Strategies\Search;

use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class AdminSearchStrategy implements SearchStrategyInterface
{
    public function search(Request $request): LengthAwarePaginator
    {
        $query = Package::query()->with(['customer', 'delivery.driver']);

        // System-Wide Search Capabilities
        if ($request->filled('keyword')) {
            $keyword = '%' . $request->input('keyword') . '%';
            $query->where(function ($q) use ($keyword) {
                $q->where('tracking_number', 'LIKE', $keyword)
                    ->orWhere('recipient_address', 'LIKE', $keyword)
                    ->orWhere('sender_address', 'LIKE', $keyword)
                    ->orWhereHas('customer', function ($subQ) use ($keyword) {
                        $subQ->where('first_name', 'LIKE', $keyword)
                             ->orWhere('last_name', 'LIKE', $keyword);
                    })
                    ->orWhereHas('delivery.driver', function ($subQ) use ($keyword) {
                        $subQ->where('first_name', 'LIKE', $keyword)
                             ->orWhere('last_name', 'LIKE', $keyword);
                    });
            });
        }

        // Administrative Filter Tools
        if ($request->filled('driver_status')) {
            $query->whereHas('delivery.driver', function ($q) use ($request) {
                $q->where('driver_status', $request->input('driver_status'));
            });
        }
        
        if ($request->filled('package_status')) {
            $query->where('package_status', $request->input('package_status'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        return $query->orderBy('created_at', 'desc')->paginate(15);
    }
}