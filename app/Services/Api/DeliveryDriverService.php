<?php

namespace App\Services\Api;

use App\Models\DeliveryDriver;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class DeliveryDriverService
{
    public function getAll()
    {
        return DeliveryDriver::all();
    }

    public function create(array $data)
    {
        $validator = Validator::make($data, [
            'driver_id'       => 'required|string|unique:deliverydriver,driver_id',
            'first_name'      => 'required|string|max:100',
            'last_name'       => 'required|string|max:100',
            'license_number'  => 'required|string|max:100|unique:deliverydriver,license_number',
            'hire_date'       => 'required|date',
            'driver_status'   => 'required|string',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return DeliveryDriver::create($validator->validated());
    }

    public function getById(string $id)
    {
        return DeliveryDriver::findOrFail($id);
    }

    public function getPaginated(int $pageNo, int $perPage = 20)
    {
        return DeliveryDriver::paginate($perPage, ['*'], 'page', $pageNo);
    }

    public function update(string $id, array $data)
    {
        $driver = DeliveryDriver::findOrFail($id);

        $validator = Validator::make($data, [
            'first_name'      => 'sometimes|required|string|max:100',
            'last_name'       => 'sometimes|required|string|max:100',
            'license_number'  => "sometimes|required|string|max:100|unique:deliverydriver,license_number,{$id},driver_id",
            'hire_date'       => 'sometimes|required|date',
            'driver_status'   => 'sometimes|required|string',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $driver->update($validator->validated());
        return $driver;
    }

    public function delete(string $id): void
    {
        $driver = DeliveryDriver::findOrFail($id);
        $driver->delete();
    }
}
