<?php

namespace App\Services\Api;

use App\Models\Package;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class PackageService
{
    public function getAll()
    {
        return Package::with(['user', 'delivery', 'assignment'])->get();
    }

    public function create(array $data)
    {
        $validator = Validator::make($data, [
            'package_id'         => 'required|string|unique:package,package_id',
            'user_id'            => 'required|string|exists:user,user_id',
            'tracking_number'    => 'required|string|unique:package,tracking_number',
            'package_weight'     => 'nullable|numeric|min:0',
            'package_dimensions' => 'nullable|string|max:100',
            'package_contents'   => 'nullable|string',
            'sender_address'     => 'required|string|max:255',
            'recipient_address'  => 'required|string|max:255',
            'package_status'     => 'required|string|max:20',
            'priority'           => 'nullable|string|max:20',
            'shipping_cost'      => 'nullable|numeric|min:0',
            'estimated_delivery' => 'nullable|date',
            'actual_delivery'    => 'nullable|date',
            'notes'              => 'nullable|string',
            'is_rated'           => 'boolean',
            'created_at'         => 'nullable|date',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return Package::create($validator->validated())
            ->load(['user', 'delivery', 'assignment']);
    }

    public function getById(string $id)
    {
        return Package::with(['user', 'delivery', 'assignment'])->findOrFail($id);
    }

    public function getPaginated(int $pageNo, int $perPage = 20)
    {
        return Package::with(['user', 'delivery', 'assignment'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $pageNo);
    }

    public function update(string $id, array $data)
    {
        $pkg = Package::findOrFail($id);

      $validator = Validator::make($data, [
    'user_id'            => 'sometimes|string|exists:user,user_id',
    'tracking_number'    => "sometimes|string|unique:package,tracking_number,{$id},package_id",
    'package_weight'     => 'nullable|numeric|min:0',
    'package_dimensions' => 'nullable|string|max:100',
    'package_contents'   => 'nullable|string',   // ✅ now truly optional
    'sender_address'     => 'nullable|string|max:255', // ✅ no longer required
    'recipient_address'  => 'nullable|string|max:255', // ✅ no longer required
    'package_status'     => 'sometimes|string|max:20',
    'priority'           => 'nullable|string|max:20',
    'shipping_cost'      => 'nullable|numeric|min:0',
    'estimated_delivery' => 'nullable|date',
    'actual_delivery'    => 'nullable|date',
    'notes'              => 'nullable|string',
    'is_rated'           => 'boolean',          // ✅ this will now pass
    'created_at'         => 'nullable|date',
]);




        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $pkg->update($validator->validated());

        return $pkg->load(['user', 'delivery', 'assignment']);
    }

    public function updateIsRated(string $packageId, bool $isRated)
{
    $package = Package::findOrFail($packageId);

    $package->is_rated = $isRated;
    $package->save();

    return $package->fresh();
}

    public function delete(string $id): void
    {
        $pkg = Package::findOrFail($id);
        $pkg->delete();
    }

    public function getUnassignedPackages()
    {
        return Package::with('user')
            ->whereIn('package_status', [Package::STATUS_PENDING, Package::STATUS_PROCESSING])
            ->whereDoesntHave('delivery')
            ->orderBy('created_at', 'asc')
            ->paginate(15);
    }

    public function getCountPackage()
    {
        $count = Package::count();

        return response()->json([
            'count' => $count
        ], 200);
    }

    public function getRecentPackages(int $noOfRecords): Collection
    {
        return Package::with('user')
            ->orderBy('created_at', 'desc')
            ->limit($noOfRecords)
            ->get();
    }

    public function getCountByStatus(string $status): Collection
    {
        if (strtolower($status) === 'all') {
            return Package::query()
                ->select('package_status', DB::raw('count(*) as count'))
                ->groupBy('package_status')
                ->get()
                ->collect();
        }

        $count = Package::where('package_status', $status)->count();

        return collect([
            ['package_status' => $status, 'count' => $count]
        ]);
    }

    public function getPackagesByStatus(string $status, int $page, int $pageSize, string $customerId): LengthAwarePaginator
    {
        $query = Package::query()
            ->where('user_id', $customerId);

        if (strtolower($status) !== 'all') {
            $query->where('package_status', strtolower($status));
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($pageSize, ['*'], 'page', $page);
    }
}
