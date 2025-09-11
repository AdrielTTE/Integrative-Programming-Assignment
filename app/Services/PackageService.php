<?php

namespace App\Services;

use App\Models\Package;
use App\Repositories\PackageRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Service Layer Pattern Implementation
 * Handles business logic for package management
 */
class PackageService
{
    protected $packageRepository;

    public function __construct(PackageRepository $packageRepository)
    {
        $this->packageRepository = $packageRepository;
    }

    /**
     * Create a new package with business logic
     */
    public function createPackage(array $data)
    {
        // Generate IDs if not provided
        if (!isset($data['package_id'])) {
            $data['package_id'] = Package::generatePackageId();
        }

        if (!isset($data['tracking_number'])) {
            $data['tracking_number'] = Package::generateTrackingNumber();
        }

        // Set default status
        if (!isset($data['package_status'])) {
            $data['package_status'] = Package::STATUS_PENDING;
        }

        // Set default priority
        if (!isset($data['priority'])) {
            $data['priority'] = Package::PRIORITY_STANDARD;
        }

        // Create temporary package instance to calculate shipping cost
        $tempPackage = new Package($data);
        if (!isset($data['shipping_cost'])) {
            $data['shipping_cost'] = $tempPackage->calculateShippingCost();
        }

        // Calculate estimated delivery
        if (!isset($data['estimated_delivery'])) {
            $data['estimated_delivery'] = $tempPackage->calculateEstimatedDelivery();
        }

        // Create package using repository
        $package = $this->packageRepository->create($data);

        // Clear cache
        $this->clearPackageCache();

        // Send notification (implement notification system)
        $this->sendPackageCreatedNotification($package);

        return $package;
    }

    /**
     * Update package with business logic
     */
    public function updatePackage(Package $package, array $data)
    {
        // Recalculate shipping cost if weight or dimensions changed
        if (isset($data['package_weight']) || isset($data['package_dimensions']) || isset($data['priority'])) {
            $package->fill($data);
            $data['shipping_cost'] = $package->calculateShippingCost();
        }

        // Recalculate estimated delivery if priority changed
        if (isset($data['priority'])) {
            $package->priority = $data['priority'];
            $data['estimated_delivery'] = $package->calculateEstimatedDelivery();
        }

        // Update package using repository
        $package = $this->packageRepository->update($package->package_id, $data);

        // Clear cache
        $this->clearPackageCache();

        return $package;
    }

    /**
     * Get package statistics
     */
    public function getStatistics($period = 'month')
    {
        return Cache::remember("package_stats_{$period}", 3600, function () use ($period) {
            $query = Package::query();

            // Apply period filter
            switch ($period) {
                case 'today':
                    $query->whereDate('created_at', today());
                    break;
                case 'week':
                    $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'month':
                    $query->whereMonth('created_at', now()->month)
                          ->whereYear('created_at', now()->year);
                    break;
                case 'year':
                    $query->whereYear('created_at', now()->year);
                    break;
            }

            $total = $query->count();
            $statusCounts = [];

            foreach (Package::getStatuses() as $status => $label) {
                $statusCounts[$status] = (clone $query)->where('package_status', $status)->count();
            }

            return [
                'period' => $period,
                'total' => $total,
                'by_status' => $statusCounts,
                'revenue' => $query->sum('shipping_cost'),
                'average_weight' => $query->avg('package_weight'),
                'pending_packages' => Package::pending()->count(),
                'active_packages' => Package::active()->count(),
                'delivered_today' => Package::where('package_status', Package::STATUS_DELIVERED)
                    ->whereDate('actual_delivery', today())
                    ->count()
            ];
        });
    }

    /**
     * Bulk update packages
     */
    public function bulkUpdate(array $packageIds, string $action, string $value)
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'details' => []
        ];

        foreach ($packageIds as $packageId) {
            try {
                $package = Package::find($packageId);
                
                if (!$package) {
                    $results['failed']++;
                    $results['details'][] = [
                        'package_id' => $packageId,
                        'error' => 'Package not found'
                    ];
                    continue;
                }

                switch ($action) {
                    case 'update_status':
                        if ($package->updateStatus($value)) {
                            $results['success']++;
                        } else {
                            $results['failed']++;
                            $results['details'][] = [
                                'package_id' => $packageId,
                                'error' => 'Invalid status transition'
                            ];
                        }
                        break;

                    case 'assign_driver':
                        $package->assigned_driver_id = $value;
                        if ($package->save()) {
                            $results['success']++;
                        } else {
                            $results['failed']++;
                            $results['details'][] = [
                                'package_id' => $packageId,
                                'error' => 'Failed to assign driver'
                            ];
                        }
                        break;

                    default:
                        $results['failed']++;
                        $results['details'][] = [
                            'package_id' => $packageId,
                            'error' => 'Unknown action'
                        ];
                }
            } catch (\Exception $e) {
                $results['failed']++;
                $results['details'][] = [
                    'package_id' => $packageId,
                    'error' => $e->getMessage()
                ];
            }
        }

        $this->clearPackageCache();

        return $results;
    }

    /**
     * Search packages with complex criteria
     */
    public function searchPackages(array $criteria)
    {
        return $this->packageRepository->search($criteria);
    }

    /**
     * Get packages requiring attention
     */
    public function getPackagesRequiringAttention()
    {
        return Cache::remember('packages_attention', 900, function () {
            return $this->packageRepository->getPackagesRequiringAttention();
        });
    }

    /**
     * Get unassigned packages
     */
    public function getUnassignedPackages()
    {
        return $this->packageRepository->getUnassignedPackages();
    }

    /**
     * Clear package-related cache
     */
    protected function clearPackageCache()
    {
        Cache::forget('package_stats_today');
        Cache::forget('package_stats_week');
        Cache::forget('package_stats_month');
        Cache::forget('package_stats_year');
        Cache::forget('packages_attention');
    }

    /**
     * Send notification for package creation
     */
    protected function sendPackageCreatedNotification(Package $package)
    {
        // Implement notification logic here
        // This could send email, SMS, or push notifications
        Log::info('Package created notification', [
            'package_id' => $package->package_id,
            'tracking_number' => $package->tracking_number
        ]);
    }

    /**
     * Calculate delivery route (integrate with Route module)
     */
    public function calculateDeliveryRoute(Package $package)
    {
        // This would integrate with the Route module
        // For now, return a simple response
        return [
            'origin' => $package->sender_address,
            'destination' => $package->recipient_address,
            'estimated_distance' => rand(10, 100) . ' km',
            'estimated_time' => rand(30, 180) . ' minutes'
        ];
    }

    /**
     * Get package history/audit trail
     */
    public function getPackageHistory($packageId)
    {
        // This would retrieve from an audit log table
        // For now, return mock data
        return [
            [
                'timestamp' => now()->subDays(2),
                'action' => 'Package created',
                'status' => Package::STATUS_PENDING,
                'user' => 'System'
            ],
            [
                'timestamp' => now()->subDays(1),
                'action' => 'Status updated',
                'status' => Package::STATUS_PROCESSING,
                'user' => 'Admin'
            ],
            [
                'timestamp' => now()->subHours(6),
                'action' => 'Driver assigned',
                'status' => Package::STATUS_IN_TRANSIT,
                'user' => 'Dispatcher'
            ]
        ];
    }

    /**
     * Generate package report
     */
    public function generateReport($startDate, $endDate, $format = 'json')
    {
        $packages = Package::whereBetween('created_at', [$startDate, $endDate])->get();

        $report = [
            'period' => [
                'start' => $startDate,
                'end' => $endDate
            ],
            'summary' => [
                'total_packages' => $packages->count(),
                'total_revenue' => $packages->sum('shipping_cost'),
                'average_weight' => $packages->avg('package_weight'),
                'by_status' => $packages->groupBy('package_status')->map->count(),
                'by_priority' => $packages->groupBy('priority')->map->count()
            ],
            'packages' => $packages->map(function ($package) {
                return $package->getFormattedDetails();
            })
        ];

        // Handle different export formats
        switch ($format) {
            case 'csv':
                return $this->exportToCsv($report);
            case 'pdf':
                return $this->exportToPdf($report);
            default:
                return $report;
        }
    }

    /**
     * Export report to CSV
     */
    protected function exportToCsv($report)
    {
        // Implement CSV export logic
        return $report; // Placeholder
    }

    /**
     * Export report to PDF
     */
    protected function exportToPdf($report)
    {
        // Implement PDF export logic
        return $report; // Placeholder
    }
}