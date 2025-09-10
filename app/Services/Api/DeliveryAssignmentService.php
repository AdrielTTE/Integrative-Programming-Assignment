<?php


namespace App\Services\Api;

use App\Models\DeliveryAssignment;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class DeliveryAssignmentService
{
    public function getAll()
    {
        return DeliveryAssignment::all();
        // Or: return DeliveryAssignment::with(['admin','package','driver'])->get();
    }

    public function create(array $data)
    {
        $validator = Validator::make($data, [
            'assignment_id'     => 'required|string|unique:deliveryassignment,assignment_id',
            'admin_id'          => 'required|string|exists:admin,admin_id',
            'package_id'        => 'required|string|exists:packages,package_id',
            'driver_id'         => 'required|string|exists:delivery_drivers,driver_id',
            'assigned_date'     => 'required|date',
            'assignment_status' => 'required|string',
            'notes'             => 'nullable|string',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return DeliveryAssignment::create($validator->validated());
    }

    public function getById(string $id)
    {
        return DeliveryAssignment::findOrFail($id);
        // Or: return DeliveryAssignment::with(['admin','package','driver'])->findOrFail($id);
    }

    public function getPaginated(int $pageNo, int $perPage = 20)
    {
        return DeliveryAssignment::paginate($perPage, ['*'], 'page', $pageNo);
    }

    public function update(string $id, array $data)
    {
        $assignment = DeliveryAssignment::findOrFail($id);

        $validator = Validator::make($data, [
            'admin_id'          => 'sometimes|required|string|exists:admin,admin_id',
            'package_id'        => 'sometimes|required|string|exists:packages,package_id',
            'driver_id'         => 'sometimes|required|string|exists:delivery_drivers,driver_id',
            'assigned_date'     => 'sometimes|required|date',
            'assignment_status' => 'sometimes|required|string',
            'notes'             => 'nullable|string',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $assignment->update($validator->validated());
        return $assignment;
    }

    public function delete(string $id): void
    {
        $assignment = DeliveryAssignment::findOrFail($id);
        $assignment->delete();
    }
}
