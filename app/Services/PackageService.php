<?php

namespace App\Services;

use App\Models\Package;
use App\Models\User;
use App\Repositories\PackageRepository;
use App\Factories\PackageStateFactory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class PackageService
{
    protected PackageRepository $repository;

    public function __construct(PackageRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Create a new package with security checks
     */
    public function createPackage(array $data): Package
    {
        $user = Auth::user();

        // Ensure user_id is from authenticated user (prevent privilege escalation)
        $data['user_id'] = $user->user_id;

        // Rate limiting - prevent spam package creation
        $cacheKey = "package_creation_rate_limit_{$user->user_id}";
        $attempts = Cache::get($cacheKey, 0);

        if ($attempts >= 10) { // Max 10 packages per hour
            throw new \Exception('Package creation rate limit exceeded. Please try again later.');
        }

        Cache::put($cacheKey, $attempts + 1, 3600); // 1 hour

        $package = $this->repository->create($data);

        Log::info('Package created', [
            'package_id' => $package->package_id,
            'user_id' => $package->user_id,
            'status' => $package->package_status,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);

        return $package;
    }

    /**
     * Process package
     */
    public function processPackage(string $packageId): Package
    {
        $package = Package::findOrFail($packageId);
        $user = Auth::user();
        
        // Authorization check - ensure user owns the package
        if (!$this->canUserModifyPackage($user, $package)) {
            throw new \Exception('Unauthorized: You can only modify your own packages');
        }

        // Check if package can be edited based on status
        if (in_array($package->package_status, ['delivered', 'cancelled', 'returned'])) {
            throw new \Exception('Package cannot be edited in current state: ' . $package->package_status);
        }

        // Sanitize and validate critical fields
        $data = $this->sanitizePackageData($data);
        $updated = $this->repository->update($package->package_id, $data);
        
        Log::info('Package updated', [
            'package_id' => $package->package_id,
            'user_id' => $user->user_id,
            'changes' => array_keys($data),
            'ip_address' => request()->ip()
        ]);

        return $updated;
    }

    /**
     * Process package - simplified without role checks
     */
    public function processPackage(string $packageId, array $data = []): Package
    {
        $package = $this->repository->find($packageId);
        $user = Auth::user();
        
        if (!$package) {
            throw new \Exception('Package not found');
        }

        // For now, allow any authenticated user to process packages
        // You can add more specific logic here if needed
        
        // Update package status based on current status
        $this->updatePackageStatus($package, $data);
        
        Log::info('Package processed', [
            'package_id' => $packageId,
            'old_status' => $package->getOriginal('package_status'),
            'new_status' => $package->package_status,
            'user_id' => $user->user_id
        ]);

        return $package->fresh();
    }

    /**
     * Cancel package with ownership verification
     */
    public function cancelPackage(string $packageId, User $user = null): Package
    {
        $package = Package::findOrFail($packageId);
        $user = $user ?? Auth::user();
        
        if (!$package) {
            throw new \Exception('Package not found');
        }

        // Authorization check - ensure user owns package
        if (!$this->canUserModifyPackage($user, $package)) {
            throw new \Exception('Unauthorized: You can only cancel your own packages');
        }

        // Check if package can be cancelled
        if (in_array($package->package_status, ['delivered', 'cancelled', 'returned'])) {
            throw new \Exception('Package cannot be cancelled in current state: ' . $package->package_status);
        }

        $package->package_status = 'cancelled';
        $package->save();
        
        Log::info('Package cancelled', [
            'package_id' => $packageId,
            'cancelled_by' => $user->user_id,
            'new_status' => $package->package_status
        ]);

        return $package->fresh();
    }

    /**
     * Search packages with user filtering
     */
    public function searchPackages(array $criteria)
    {
        $user = Auth::user();

        // For now, users can only see their own packages
        $criteria['user_id'] = $user->user_id;

        return $this->repository->search($criteria);
    }

    /**
     * Get package with authorization check
     */
    public function getPackageWithDetails(string $packageId): ?Package
    {
        $package = $this->repository->findWithRelations($packageId);
        $user = Auth::user();
        
        if (!$package) {
            return null;
        }

        $user = Auth::user();
        
        // Check if user can view this package
        if ($user && !$this->canUserViewPackage($user, $package)) {
            return null;
        }

        return $package;
    }

    /**
     * SIMPLIFIED: Check if user can modify package (ownership only)
     */
    private function canUserModifyPackage(User $user, Package $package): bool
    {
        return $package->user_id === $user->user_id;
    }

    /**
     * Check if a user can view a package
     */
    private function canUserViewPackage(User $user, Package $package): bool
    {
        // Admins can view any package
        if (str_starts_with($user->user_id, 'AD')) {
            return true;
        }

        // Drivers can view packages assigned to them
        if (str_starts_with($user->user_id, 'D')) {
            // Check if the driver is assigned to this package
            return $package->assignment && $package->assignment->driver_id === $user->user_id;
        }

        // Customers can only view their own packages
        if (str_starts_with($user->user_id, 'C')) {
            return $package->user_id === $user->user_id;
        }

        // Default deny for unknown user types
        return false;
    }

    /**
     * Sanitize package data to prevent injection attacks
     */
    private function sanitizePackageData(array $data): array
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }

    /**
     * Assign package to driver (admin only)
     * 
     * @param string $packageId
     * @param string $driverId
     * @return Package
     * @throws \Exception
     */
    public function assignPackage(string $packageId, string $driverId): Package
    {
        $user = Auth::user();
        
        // Only admins can assign packages
        if (!str_starts_with($user->user_id, 'AD')) {
            throw new \Exception('Unauthorized: Only admins can assign packages to drivers');
        }
        
        $package = Package::findOrFail($packageId);
        
        if (!$package->canBeAssigned()) {
            throw new \Exception("Package cannot be assigned in its current status");
        }
        
        $package->assign($driverId);
        return $package->fresh();
    }

    /**
     * Deliver package (driver operation)
     * 
     * @param string $packageId
     * @param array $proofData
     * @return Package
     * @throws \Exception
     */
    public function deliverPackage(string $packageId, array $proofData = []): Package
    {
        $package = Package::findOrFail($packageId);
        $user = Auth::user();
        
        // Check if user is authorized to mark as delivered
        if (str_starts_with($user->user_id, 'D')) {
            // Driver must be assigned to this package
            if (!$package->assignment || $package->assignment->driver_id !== $user->user_id) {
                throw new \Exception('Unauthorized: You are not assigned to this package');
            }
        } elseif (!str_starts_with($user->user_id, 'AD')) {
            // Only drivers and admins can mark packages as delivered
            throw new \Exception('Unauthorized: Only drivers and admins can mark packages as delivered');
        }
        
        $package->deliver($proofData);
        return $package->fresh();
    }

    /**
     * Update package status based on current state
     */
     public function updatePackage(Package $package, array $data): Package
    {
        $currentStatus = $package->package_status;
        $newStatus = $data['status'] ?? null;
        
        // Define allowed status transitions
        $allowedTransitions = [
            'pending' => ['processing', 'cancelled'],
            'processing' => ['picked_up', 'cancelled'],
            'picked_up' => ['in_transit'],
            'in_transit' => ['out_for_delivery', 'failed'],
            'out_for_delivery' => ['delivered', 'failed'],
            'failed' => ['out_for_delivery', 'returned'],
        ];
        
        if ($newStatus && isset($allowedTransitions[$currentStatus])) {
            if (in_array($newStatus, $allowedTransitions[$currentStatus])) {
                $package->package_status = $newStatus;
                
                // Update delivery timestamp if delivered
                if ($newStatus === 'delivered') {
                    $package->actual_delivery = now();
                }
                
                $package->save();
            }
        }
    }

    public function getStatistics(string $period = 'month'): array
    {
        $user = Auth::user();

        // Return statistics for the current user's packages only
        return $this->repository->getUserStats($user->user_id)->mapWithKeys(function ($stat) {
            return [$stat->package_status => $stat->count];
        })->toArray();
    }

    /**
     * Get package history with proper authorization
     */
    public function getPackageHistory(string $packageId): array
    {
        $package = Package::where('package_id', $packageId)->firstOrFail();
        $user = Auth::user();
        
        // SIMPLIFIED Authorization check - only check ownership
        if (!$this->canUserViewPackage($user, $package)) {
            throw new \Exception('Unauthorized: Access denied to package history');
        }

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