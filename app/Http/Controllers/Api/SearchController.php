<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Search for packages based on various criteria.
     * This version corrects the customer name search.
     */
    public function searchPackages(Request $request)
    {
         $query = Package::query()->with(['user', 'delivery.driver']);

        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');
            $query->where(function ($q) use ($keyword) {
                $q->where('package.tracking_number', 'LIKE', "%{$keyword}%")
                  ->orWhere('package.recipient_address', 'LIKE', "%{$keyword}%")
                  ->orWhere('package.sender_address', 'LIKE', "%{$keyword}%")

                  // --- THIS IS THE CORRECTED SECTION ---
                  // Correctly search customer names using a subquery on the customer table
                  ->orWhereIn('package.user_id', function ($subQuery) use ($keyword) {
                      $subQuery->select('customer_id')->from('customer')
                               ->where('first_name', 'LIKE', "%{$keyword}%")
                               ->orWhere('last_name', 'LIKE', "%{$keyword}%");
                  })

                  // Search the related Driver's name
                  ->orWhereHas('delivery.driver', function ($driverQuery) use ($keyword) {
                      $driverQuery->where('first_name', 'LIKE', "%{$keyword}%")
                                  ->orWhere('last_name', 'LIKE', "%{$keyword}%");
                  });
            });
        }

        // --- Other filters remain the same ---
         if ($request->filled('package_status')) {
            $query->where('package.package_status', $request->input('package_status'));
        }
        
        if ($request->filled('user_id')) {
            $query->where('package.user_id', $request->input('user_id'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('package.created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('package.created_at', '<=', $request->input('date_to'));
        }

        $results = $query->orderBy('package.created_at', 'desc')->paginate(15);
        
        return response()->json($results);
    }
}