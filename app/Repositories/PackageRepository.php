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
        return $this->model->with(['customer', 'delivery', 'assignment'])->get();
    }

    /**
     * Find package by ID with relationships
     */
    public function findWithRelations($id)
    {
        return $this->model->with(['customer', 'delivery', 'assignment'])
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

        // Search by package ID
        if (!empty($criteria['package_id'])) {
            $query->where('package_id', 'like', '%' . $criteria['package_id'] . '%');
        }

        // Search by addresses
        if (!empty($criteria['address'])) {
            $query->where(function ($q) use ($criteria) {
                $q->where('sender_address', 'like', '%' . $criteria['address'] . '%')
                  ->orWhere('recipient_address', 'like', '%' . $criteria['address'] . '%');
            });
        }

        // Filter by status
        if (!empty($criteria['package_status'])) {
            if (is_array($criteria['package_status'])) {
                $query->whereIn('package_status', $criteria['package_status']);
            } else {
                $query->where('package_status', $criteria['package_status']);
            }
        }

        // Filter by priority
        if (!empty($criteria['priority'])) {
            $query->where('priority', $criteria['priority']);
        }

        // Filter by customer
        if (!empty($criteria['customer_id'])) {
            $query->where('customer_id', $criteria['customer_id']);
        }

        // Filter by date range
        if (!empty($criteria['date_from'])) {
            $query->whereDate('created_at', '>=', $criteria['date_from']);
        }

        if (!empty($criteria['date_to'])) {
            $query->whereDate('created_at', '<=', $criteria['date_to']);
        }

        // Filter by weight range
        if (!empty($criteria['weight_min'])) {
            $query->where('package_weight', '>=', $criteria['weight_min']);
        }

        if (!empty($criteria['weight_max'])) {
            $query->where('package_weight', '<=', $criteria['weight_max']);
        }

        // Include relationships
        $query->with(['customer', 'delivery', 'assignment']);

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
        return $this->model->where('package_status', $status)
                          ->with(['customer', 'delivery', 'assignment'])
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
     * Get revenue statistics
     */
    public function getRevenueStats($startDate = null, $endDate = null)
    {
        $query = DB::table('package')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(shipping_cost) as revenue'),
                DB::raw('COUNT(*) as package_count'),
                DB::raw('AVG(package_weight) as avg_weight')
            )
            ->where('package_status', '!=', Package::STATUS_CANCELLED);

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        return $query->groupBy('date')
                     ->orderBy('date', 'desc')
                     ->get();
    }

    /**
     * Get packages requiring attention
     */
    public function getPackagesRequiringAttention()
    {
        return $this->model->where(function ($query) {
            // Packages pending for more than 2 days
            $query->where('package_status', Package::STATUS_PENDING)
                  ->where('created_at', '<', now()->subDays(2));
        })->orWhere(function ($query) {
            // Packages past estimated delivery
            $query->where('package_status', Package::STATUS_IN_TRANSIT)
                  ->whereNotNull('estimated_delivery')
                  ->where('estimated_delivery', '<', now());
        })->orWhere(function ($query) {
            // Failed delivery attempts
            $query->where('package_status', Package::STATUS_FAILED);
        })->with(['customer', 'delivery', 'assignment'])
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
                          ->with('customer')
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
        })->with(['customer', 'delivery', 'assignment'])
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
                          ->with(['customer', 'delivery', 'assignment'])
                          ->orderBy('priority', 'desc')
                          ->get();
    }

    /**
     * Get package count by status for a period
     */
    public function getStatusCountByPeriod($startDate, $endDate)
    {
        return DB::table('package')
            ->select('package_status', DB::raw('COUNT(*) as count'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('package_status')
            ->pluck('count', 'package_status');
    }

    /**
     * Get top customers by package count
     */
    public function getTopCustomers($limit = 10)
    {
        return DB::table('package')
            ->select('customer_id', DB::raw('COUNT(*) as package_count'), DB::raw('SUM(shipping_cost) as total_revenue'))
            ->groupBy('customer_id')
            ->orderBy('package_count', 'desc')
            ->limit($limit)
            ->get();
    }
}