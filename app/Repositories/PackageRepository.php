<?php

namespace App\Repositories;

use App\Models\Package;
use Illuminate\Support\Facades\DB;

/**
 * Repository Pattern Implementation
 * Handles data access for packages
 */
class PackageRepository
{
    protected $model;

    public function __construct()
    {
        $this->model = new Package();
    }

    /**
     * Get all packages with relationships
     */
    public function all()
    {
        return $this->model->with(['user', 'delivery', 'assignment'])->get();
    }

    /**
     * Find package by ID with relationships
     */
    public function findWithRelations($id)
    {
        return $this->model->with(['user', 'delivery', 'assignment'])
                          ->where('package_id', $id)
                          ->first();
    }

    /**
     * Find package by ID
     */
    public function find($id)
    {
        return $this->model->where('package_id', $id)->first();
    }

    /**
     * Create new package
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * Update package
     */
    public function update($id, array $data)
    {
        $package = $this->find($id);
        if ($package) {
            $package->update($data);
            return $package->fresh();
        }
        return null;
    }

    /**
     * Delete package (soft delete)
     */
    public function delete($id)
    {
        $package = $this->find($id);
        if ($package) {
            return $package->delete();
        }
        return false;
    }

    /**
     * Search packages with complex criteria
     */
    public function search(array $criteria)
    {
        $query = $this->model->query();

        // Search by tracking number
        if (!empty($criteria['tracking_number'])) {
            $query->where('tracking_number', 'like', '%' . $criteria['tracking_number'] . '%');
        }


        // Filter by user (was customer)
        if (!empty($criteria['user_id'])) {
            $query->where('user_id', $criteria['user_id']);
        }
        

        $query->with(['user', 'delivery', 'assignment']);

        // Sorting
        $sortBy = $criteria['sort_by'] ?? 'created_at';
        $sortOrder = $criteria['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        // Return paginated or all results
        if (isset($criteria['paginate']) && $criteria['paginate']) {
            return $query->paginate($criteria['per_page'] ?? 15);
        }

        return $query->get();
    }

    /**
     * Get packages by status
     */
    public function getByStatus($status)
    {
        // MODIFIED: 'customer' is now 'user'
        return $this->model->where('package_status', $status)
                          ->with(['user', 'delivery', 'assignment'])
                          ->get();
    }

    /**
     * Get packages for dashboard statistics
     */
    public function getDashboardStats()
    {
        return DB::table('package')
            ->select('package_status', DB::raw('count(*) as count'))
            ->groupBy('package_status')
            ->get();
    }


    /**
     * Get packages requiring attention
     */
    public function getPackagesRequiringAttention()
    {
        return $this->model->where(function ($query) {
            // ... logic ...
        })->orWhere(function ($query) {
            // ... logic ...
        })->orWhere(function ($query) {
            // ... logic ...
        })
        // MODIFIED: 'customer' is now 'user'
        ->with(['user', 'delivery', 'assignment'])
        ->get();
    }

    /**
     * Get unassigned packages
     */
    public function getUnassignedPackages()
    {
        return $this->model->whereDoesntHave('assignment')
                          ->whereIn('package_status', [
                              Package::STATUS_PENDING,
                              Package::STATUS_PROCESSING
                          ])
                          // MODIFIED: 'customer' is now 'user'
                          ->with('user')
                          ->orderBy('priority', 'desc')
                          ->orderBy('created_at', 'asc')
                          ->get();
    }

    /**
     * Batch update packages
     */
    public function batchUpdate(array $ids, array $data)
    {
        return $this->model->whereIn('package_id', $ids)->update($data);
    }

    /**
     * Get packages by driver
     */
    public function getByDriver($driverId)
    {
        return $this->model->whereHas('assignment', function ($query) use ($driverId) {
            $query->where('driver_id', $driverId);
        })
        // MODIFIED: 'customer' is now 'user'
        ->with(['user', 'delivery', 'assignment'])
        ->get();
    }

    /**
     * Get today's deliveries
     */
    public function getTodaysDeliveries()
    {
        return $this->model->whereDate('estimated_delivery', today())
                          ->orWhere(function ($query) {
                              $query->where('package_status', Package::STATUS_OUT_FOR_DELIVERY);
                          })
                          // MODIFIED: 'customer' is now 'user'
                          ->with(['user', 'delivery', 'assignment'])
                          ->orderBy('priority', 'desc')
                          ->get();
    }

    // ... getStatusCountByPeriod is fine ...

    /**
     * Get top users by package count
     * NOTE: This method is now logically finding top users, not customers.
     */
    public function getTopCustomers($limit = 10) // Renaming to getTopUsers would be ideal
    {
        return DB::table('package')
            // MODIFIED: select 'user_id'
            ->select('user_id', DB::raw('COUNT(*) as package_count'), DB::raw('SUM(shipping_cost) as total_revenue'))
            // MODIFIED: group by 'user_id'
            ->groupBy('user_id')
            ->orderBy('package_count', 'desc')
            ->limit($limit)
            ->get();
    }
}

