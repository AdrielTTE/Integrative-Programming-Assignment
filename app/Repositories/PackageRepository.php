<?php

namespace App\Repositories;

use App\Models\Package;
use Illuminate\Support\Facades\DB;

class PackageRepository
{
    protected $model;

    public function __construct()
    {
        $this->model = new Package();
    }

    public function all()
    {
        return $this->model->with(['user', 'delivery', 'assignment'])->get();
    }

    public function findWithRelations($id)
    {
        return $this->model->with(['user', 'delivery', 'assignment'])
                          ->where('package_id', $id)
                          ->first();
    }

    public function find($id)
    {
        return $this->model->where('package_id', $id)->first();
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $package = $this->find($id);
        if ($package) {
            $package->update($data);
            return $package->fresh();
        }
        return null;
    }

    public function delete($id)
    {
        $package = $this->find($id);
        if ($package) {
            return $package->delete();
        }
        return false;
    }

    public function search(array $criteria)
    {
        $query = $this->model->query();

        if (!empty($criteria['tracking_number'])) {
            $query->where('tracking_number', 'like', '%' . $criteria['tracking_number'] . '%');
        }

        if (!empty($criteria['user_id'])) {
            $query->where('user_id', $criteria['user_id']);
        }

        if (!empty($criteria['package_status'])) {
            $query->where('package_status', $criteria['package_status']);
        }

        if (!empty($criteria['priority'])) {
            $query->where('priority', $criteria['priority']);
        }

        $query->with(['user', 'delivery', 'assignment']);

        $sortBy = $criteria['sort_by'] ?? 'created_at';
        $sortOrder = $criteria['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        if (isset($criteria['paginate']) && $criteria['paginate']) {
            return $query->paginate($criteria['per_page'] ?? 15);
        }

        return $query->get();
    }

    public function getByStatus($status)
    {
        return $this->model->where('package_status', $status)
                          ->with(['user', 'delivery', 'assignment'])
                          ->get();
    }

    public function getDashboardStats()
    {
        return DB::table('package')
            ->select('package_status', DB::raw('count(*) as count'))
            ->groupBy('package_status')
            ->get();
    }

    public function getPackagesRequiringAttention()
    {
        return $this->model->where(function ($query) {
            $query->where('package_status', Package::STATUS_FAILED)
                  ->orWhere('estimated_delivery', '<', now());
        })
        ->with(['user', 'delivery', 'assignment'])
        ->get();
    }

    public function getUnassignedPackages()
    {
        return $this->model->whereDoesntHave('assignment')
                          ->whereIn('package_status', [
                              Package::STATUS_PENDING,
                              Package::STATUS_PROCESSING
                          ])
                          ->with('user')
                          ->orderBy('priority', 'desc')
                          ->orderBy('created_at', 'asc')
                          ->get();
    }

    public function batchUpdate(array $ids, array $data)
    {
        return $this->model->whereIn('package_id', $ids)->update($data);
    }
}
