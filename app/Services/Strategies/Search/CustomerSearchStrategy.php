<?php

namespace App\Services\Strategies\Search;

use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class CustomerSearchStrategy implements SearchStrategyInterface
{
    public function search(Request $request): LengthAwarePaginator
    {
        $customerId = Auth::id(); 
        
        $query = Package::query()->where('customer_id', $customerId);

        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');
            $keywordLike = '%' . $keyword . '%';

            $query->where(function ($q) use ($keyword, $keywordLike) {
                // MODIFIED: Use an exact match for the tracking number, which is more precise.
                $q->where('tracking_number', '=', $keyword)
                  // Keep using LIKE for broader fields like address and contents.
                  ->orWhere('recipient_address', 'LIKE', $keywordLike)
                  ->orWhere('package_contents', 'LIKE', $keywordLike);
            });
        }
        
        // --- FOR DEBUGGING ONLY ---
        // If the search still fails, uncomment the line below to see the exact SQL query.
        // Then, copy the query and run it directly in a database tool like phpMyAdmin to test it.
        // dd($query->toSql());

        return $query->orderBy('created_at', 'desc')->paginate(10);
    }
}