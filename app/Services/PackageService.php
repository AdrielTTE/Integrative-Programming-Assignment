<?php

namespace App\Services;

use App\Models\Package;
use App\Models\User;
use App\Repositories\PackageRepository;
use App\Factories\PackageStateFactory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class PackageService
{
    protected PackageRepository $repository;

    public function __construct(PackageRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Create a new package
     */
    public function createPackage(array $data): Package
    {
        $data['user_id'] = $data['user_id'] ?? Auth::id();
        $package = $this->repository->create($data);
        
        Log::info('Package created', [
            'package_id' => $package->package_id,
            'user_id' => $package->user_id,
            'status' => $package->package_status
        ]);

        return $package;
    }

    /**
     * Update package using current state
     */
    public function updatePackage(Package $package, array $data): Package
    {
        $state = $package->getState();
        
        if (!$state->canBeEdited()) {
            throw new \Exception('Package cannot be edited in current state: ' . $state->getStatusName());
        }

        $updated = $this->repository->update($package->package_id, $data);
        
        Log::info('Package updated', [
            'package_id' => $package->package_id,
            'user_id' => Auth::id(),
            'changes' => $data
        ]);

        return $updated;
    }

    /**
     * Process package to next state
     */
    public function processPackage(string $packageId, array $data = []): Package
    {
        $package = $this->repository->find($packageId);
        
        if (!$package) {
            throw new \Exception('Package not found');
        }

        $newState = $package->process($data);
        
        Log::info('Package processed', [
            'package_id' => $packageId,
            'old_status' => $package->getOriginal('package_status'),
            'new_status' => $newState->getStatusName(),
            'user_id' => Auth::id()
        ]);

        return $package->fresh();
    }

    /**
     * Cancel package
     */
    public function cancelPackage(string $packageId, ?User $user = null): Package
    {
        $package = $this->repository->find($packageId);
        
        if (!$package) {
            throw new \Exception('Package not found');
        }

        $user = $user ?? Auth::user();
        $newState = $package->cancel($user);
        
        Log::info('Package cancelled', [
            'package_id' => $packageId,
            'cancelled_by' => $user->user_id,
            'new_status' => $newState->getStatusName()
        ]);

        return $package->fresh();
    }

    /**
     * Assign package to driver
     */
    public function assignPackage(string $packageId, string $driverId): Package
    {
        $package = $this->repository->find($packageId);
        
        if (!$package) {
            throw new \Exception('Package not found');
        }

        $newState = $package->assign($driverId);
        
        Log::info('Package assigned', [
            'package_id' => $packageId,
            'driver_id' => $driverId,
            'new_status' => $newState->getStatusName(),
            'assigned_by' => Auth::id()
        ]);

        return $package->fresh();
    }

    /**
     * Mark package as delivered
     */
    public function deliverPackage(string $packageId, array $proofData = []): Package
    {
        $package = $this->repository->find($packageId);
        
        if (!$package) {
            throw new \Exception('Package not found');
        }

        $newState = $package->deliver($proofData);
        
        Log::info('Package delivered', [
            'package_id' => $packageId,
            'delivered_by' => Auth::id(),
            'delivery_time' => now()
        ]);

        return $package->fresh();
    }

    /**
     * Get package with details
     */
    public function getPackageWithDetails(string $packageId): ?Package
    {
        return $this->repository->findWithRelations($packageId);
    }

    /**
     * Search packages
     */
    public function searchPackages(array $criteria)
    {
        return $this->repository->search($criteria);
    }

    /**
     * Get package statistics
     */
    public function getStatistics(string $period = 'month'): array
    {
        return $this->repository->getDashboardStats()->mapWithKeys(function ($stat) {
            return [$stat->package_status => $stat->count];
        })->toArray();
    }

    /**
     * Get packages requiring attention
     */
    public function getPackagesRequiringAttention()
    {
        return $this->repository->getPackagesRequiringAttention();
    }

    /**
     * Get unassigned packages
     */
    public function getUnassignedPackages()
    {
        return $this->repository->getUnassignedPackages();
    }

    /**
     * Bulk update packages
     */
    public function bulkUpdate(array $packageIds, string $action, $value): array
    {
        $results = ['success' => 0, 'failed' => 0, 'errors' => []];

        foreach ($packageIds as $packageId) {
            try {
                $package = $this->repository->find($packageId);
                
                if (!$package) {
                    $results['failed']++;
                    $results['errors'][] = "Package {$packageId} not found";
                    continue;
                }

                switch ($action) {
                    case 'process':
                        $package->process();
                        break;
                    case 'cancel':
                        $package->cancel(Auth::user());
                        break;
                    case 'assign':
                        $package->assign($value);
                        break;
                    default:
                        throw new \Exception("Unknown action: {$action}");
                }

                $results['success']++;
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = "Package {$packageId}: " . $e->getMessage();
            }
        }

        return $results;
    }

    /**
     * Get package history
     */
    public function getPackageHistory(string $packageId): array
{
    $package = Package::where('package_id', $packageId)->firstOrFail();
    
    // Generate history based on package status
    return $this->generateHistoryFromPackageData($package);
}

private function generateHistoryFromPackageData(Package $package): array
{
    $history = [];

    // Always include package creation
    $history[] = [
        'status' => 'Package Created',
        'action' => 'Pickup request submitted successfully',
        'timestamp' => $package->created_at,
    ];

    // Add progression based on current status
    if ($package->package_status !== 'pending') {
        $history[] = [
            'status' => 'Processing Started', 
            'action' => 'Package accepted and being prepared for pickup',
            'timestamp' => $this->estimateStatusChangeTime($package, 'processing'),
        ];
    }

    if (in_array($package->package_status, ['in_transit', 'out_for_delivery', 'delivered', 'failed'])) {
        $history[] = [
            'status' => 'Picked Up',
            'action' => 'Package collected from pickup location',
            'timestamp' => $this->estimateStatusChangeTime($package, 'picked_up'),
        ];
        
        $history[] = [
            'status' => 'In Transit',
            'action' => 'Package en route to destination',
            'timestamp' => $this->estimateStatusChangeTime($package, 'in_transit'),
        ];
    }

    if (in_array($package->package_status, ['out_for_delivery', 'delivered', 'failed'])) {
        $history[] = [
            'status' => 'Out for Delivery',
            'action' => 'Package loaded for final delivery',
            'timestamp' => $this->estimateStatusChangeTime($package, 'out_for_delivery'),
        ];
    }

    // Terminal states
    switch ($package->package_status) {
        case 'delivered':
            $history[] = [
                'status' => 'Delivered',
                'action' => 'Package successfully delivered to recipient',
                'timestamp' => $package->actual_delivery ?? $package->updated_at,
            ];
            break;
            
        case 'cancelled':
            $history[] = [
                'status' => 'Cancelled',
                'action' => 'Delivery request cancelled by customer',
                'timestamp' => $package->updated_at,
            ];
            break;
            
        case 'failed':
            $history[] = [
                'status' => 'Delivery Failed',
                'action' => 'Delivery attempt unsuccessful - will retry',
                'timestamp' => $package->updated_at,
            ];
            break;
            
        case 'returned':
            $history[] = [
                'status' => 'Returned to Sender',
                'action' => 'Package returned due to delivery failure',
                'timestamp' => $package->updated_at,
            ];
            break;
    }

    // Sort by timestamp, most recent first
    usort($history, function ($a, $b) {
        return $b['timestamp']->timestamp <=> $a['timestamp']->timestamp;
    });

    return $history;
}

private function estimateStatusChangeTime(Package $package, string $status): \Carbon\Carbon
{
    // Since we don't have actual timestamps, estimate based on created_at
    $baseTime = $package->created_at;
    
    $estimates = [
        'processing' => 2, // 2 hours after creation
        'picked_up' => 6,  // 6 hours after creation  
        'in_transit' => 12, // 12 hours after creation
        'out_for_delivery' => 24, // 24 hours after creation
    ];
    
    $hoursToAdd = $estimates[$status] ?? 1;
    return $baseTime->copy()->addHours($hoursToAdd);
}
}