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
            'package_weight'     => 'nullable|numeric',
            'package_dimensions' => 'nullable|string|max:100',
            'package_contents'   => 'nullable|string',
            'sender_address'     => 'required|string',
            'recipient_address'  => 'required|string',
            'package_status'     => 'required|string',
            'created_at'         => 'nullable|date',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return Package::create($validator->validated())->load(['user', 'delivery', 'assignment']);
    }

    public function getById(string $id)
    {
        return Package::with(['user', 'delivery', 'assignment'])->findOrFail($id);
    }

    public function getPaginated(int $pageNo, int $perPage = 20)
    {
        return Package::with(['user', 'delivery', 'assignment'])
            ->paginate($perPage, ['*'], 'page', $pageNo);
    }

    public function update(string $id, array $data)
    {
        $pkg = Package::findOrFail($id);

        $validator = Validator::make($data, [
            'user_id'            => 'sometimes|required|string|exists:user,user_id',
            'tracking_number'    => "sometimes|required|string|unique:package,tracking_number,{$id},package_id",
            'package_weight'     => 'nullable|numeric',
            'package_dimensions' => 'nullable|string|max:100',
            'package_contents'   => 'nullable|string',
            'sender_address'     => 'sometimes|required|string',
            'recipient_address'  => 'sometimes|required|string',
            'package_status'     => 'sometimes|required|string',
            'created_at'         => 'nullable|date',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $pkg->update($validator->validated());

        return $pkg->load(['user', 'delivery', 'assignment']);
    }

    public function delete(string $id): void
    {
        $pkg = Package::findOrFail($id);
        $pkg->delete();
    }

    // This is the new method containing the query logic
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



    public function getRecentPackages(int $noOfRecords):Collection
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
        ->where('customer_id', $customerId); // filter by customer_id

    if (strtolower($status) !== 'all') {
        $query->where('package_status', strtolower($status)); // match status
    }

    return $query->orderBy('created_at', 'desc')
                 ->paginate($pageSize, ['*'], 'page', $page);
}


}
