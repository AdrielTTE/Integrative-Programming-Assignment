<?php

namespace App\Services\Api;

use App\Models\Delivery;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DeliveryService
{
    public function getAll()
    {
        return Delivery::all();
    }

    public function create(array $data)
    {
        $validator = Validator::make($data, [
            'delivery_id'             => 'required|string|unique:delivery,delivery_id',
            'package_id'              => 'required|string|exists:packages,package_id',
            'driver_id'               => 'required|string|exists:delivery_drivers,driver_id',
            'vehicle_id'              => 'required|string|exists:vehicles,vehicle_id',
            'route_id'                => 'required|string|exists:routes,route_id',
            'pickup_time'             => 'required|date',
            'estimated_delivery_time' => 'required|date|after_or_equal:pickup_time',
            'actual_delivery_time'    => 'nullable|date',
            'delivery_status'         => 'required|string',
            'delivery_cost'           => 'required|numeric',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return Delivery::create($validator->validated());
    }

    public function getById(string $id)
    {
        return Delivery::findOrFail($id);
    }

    public function getPaginated(int $pageNo, int $perPage = 20)
    {
        return Delivery::paginate($perPage, ['*'], 'page', $pageNo);
    }

    public function update(string $id, array $data)
    {
        $delivery = Delivery::findOrFail($id);

        $validator = Validator::make($data, [
            'package_id'              => 'sometimes|required|string|exists:packages,package_id',
            'driver_id'               => 'sometimes|required|string|exists:delivery_drivers,driver_id',
            'vehicle_id'              => 'sometimes|required|string|exists:vehicles,vehicle_id',
            'route_id'                => 'sometimes|required|string|exists:routes,route_id',
            'pickup_time'             => 'sometimes|required|date',
            'estimated_delivery_time' => 'sometimes|required|date|after_or_equal:pickup_time',
            'actual_delivery_time'    => 'nullable|date',
            'delivery_status'         => 'sometimes|required|string',
            'delivery_cost'           => 'sometimes|required|numeric',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $delivery->update($validator->validated());
        return $delivery;
    }

    public function delete(string $id): void
    {
        $delivery = Delivery::findOrFail($id);
        $delivery->delete();
    }

    public function getCountDeliveries(): int
    {
        return Delivery::count();
    }

    public function getCountByStatus(string $status): Collection{
        if (strtolower($status) === 'all') {
        return Delivery::query()
            ->select('delivery_status', DB::raw('count(*) as count'))
            ->groupBy('delivery_status')
            ->get()
            ->collect();
    }
     $count = Delivery::where('delivery_status', $status)->count();

    return collect([
        ['delivery_status' => $status, 'count' => $count]
    ]);
    }
}
