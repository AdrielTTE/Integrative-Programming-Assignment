<?php

namespace App\Http\Controllers\CustomerControllers;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerDashboardController extends Controller
{
    // Remove this entire constructor block:
    // public function __construct()
    // {
    //     $this->middleware(['auth', 'customer']);
    // }

    /**
     * Display customer dashboard
     */
    public function dashboard(Request $request)
    {
        try {
            $userId = Auth::id();

            // Get user's packages
            $packages = Package::where('customer_id', $userId)->get();

            // Prepare dashboard data
            $dashboardData = [
                'totalPackages' => $packages->count(),
                'activeDeliveries' => $packages->whereIn('package_status', [
                    'processing',
                    'in_transit',
                    'out_for_delivery'
                ])->count(),
                'deliveredPackages' => $packages->where('package_status', 'delivered')->count(),
                'totalSpent' => $packages->where('package_status', '!=', 'cancelled')->sum('shipping_cost') ?? 0,
                'recentPackages' => $packages->sortByDesc('created_at')->take(5),
                'packageStatusData' => $this->getSimpleStatusData($packages),
                'monthlyActivityData' => $this->getMonthlyActivityData($packages),
                'recentNotifications' => [
                    [
                        'message' => 'Welcome to your dashboard!',
                        'icon' => 'fa-info-circle',
                        'created_at' => now()->toISOString()
                    ]
                ],
                'unreadNotifications' => 0
            ];

            return view('CustomerViews.dashboard', $dashboardData);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Dashboard temporarily unavailable',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get simple package status counts
     */
    private function getSimpleStatusData($packages)
    {
        $statusData = [
            'pending' => 0,
            'processing' => 0,
            'in_transit' => 0,
            'out_for_delivery' => 0,
            'delivered' => 0,
            'cancelled' => 0,
            'failed' => 0,
            'returned' => 0
        ];

        foreach ($packages as $package) {
            $status = $package->package_status;
            if (isset($statusData[$status])) {
                $statusData[$status]++;
            }
        }

        return $statusData;
    }

    /**
     * Get monthly activity data for the last 12 months
     */
    private function getMonthlyActivityData($packages)
    {
        if ($packages->isEmpty()) {
            return [];
        }

        $monthlyData = [];
        $currentDate = now();

        for ($i = 11; $i >= 0; $i--) {
            $date = $currentDate->copy()->subMonths($i);
            $monthKey = $date->format('Y-m');
            $monthLabel = $date->format('M Y');

            $count = $packages->filter(function ($package) use ($monthKey) {
                return $package->created_at->format('Y-m') === $monthKey;
            })->count();

            $monthlyData[] = [
                'month' => $monthLabel,
                'count' => $count
            ];
        }

        return $monthlyData;
    }
}
