<?php

namespace App\Services\Api;

use App\Models\Vehicle;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class VehicleService
{
    public function getAll()
    {
        return Vehicle::orderBy('vehicle_id')->get();
    }

    public function create(array $data)
    {
        $validator = Validator::make($data, [
            'vehicle_id'            => 'required|string|unique:vehicle,vehicle_id',
            'vehicle_type'          => 'required|string|max:100',
            'vehicle_capacity'      => 'nullable|numeric|min:0',
            'vehicle_status'        => 'required|string|max:50',
            'last_maintenance_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return Vehicle::create($validator->validated());
    }

    public function getById(string $id)
    {
        return Vehicle::findOrFail($id);
    }

    public function getPaginated(int $pageNo, int $perPage = 20)
    {
        return Vehicle::orderBy('vehicle_id')
            ->paginate($perPage, ['*'], 'page', $pageNo);
    }

    public function update(string $id, array $data)
    {
        $vehicle = Vehicle::findOrFail($id);

        $validator = Validator::make($data, [
            'vehicle_type'          => 'sometimes|required|string|max:100',
            'vehicle_capacity'      => 'nullable|numeric|min:0',
            'vehicle_status'        => 'sometimes|required|string|max:50',
            'last_maintenance_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $vehicle->update($validator->validated());
        return $vehicle;
    }

    public function delete(string $id): void
    {
        $vehicle = Vehicle::findOrFail($id);
        $vehicle->delete();
    }

     public function getCountByStatus(string $status): Collection{
        if (strtolower($status) === 'all') {
        return Vehicle::query()
            ->select('vehicle_status', DB::raw('count(*) as count'))
            ->groupBy('vehicle_status')
            ->get()
            ->collect();
    }
     $count = Vehicle::where('vehicle_status', $status)->count();

    return collect([
        ['vehicle_status' => $status, 'count' => $count]
    ]);
    }
}
