<?php

namespace App\Services\Api;

use App\Models\DeliveryDetails;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class DeliveryDetailsService
{
    public function getAll()
    {
        return DeliveryDetails::with(['delivery', 'hub'])->get();
    }

    public function create(array $data)
    {
        $validator = Validator::make($data, [
            'detail_id'         => 'required|string|unique:deliverydetails,detail_id',
            'delivery_id'       => 'required|string|exists:delivery,delivery_id',
            'hub_id'            => 'required|string|exists:logistichub,hub_id',
            'arrival_time'      => 'required|date',
            'departure_time'    => 'nullable|date|after_or_equal:arrival_time',
            'processing_status' => 'required|string',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return DeliveryDetails::create($validator->validated());
    }

    public function getById(string $id)
    {
        return DeliveryDetails::with(['delivery', 'hub'])->findOrFail($id);
    }

    public function getPaginated(int $pageNo, int $perPage = 20)
    {
        return DeliveryDetails::with(['delivery', 'hub'])
            ->paginate($perPage, ['*'], 'page', $pageNo);
    }

    public function update(string $id, array $data)
    {
        $detail = DeliveryDetails::findOrFail($id);

        $validator = Validator::make($data, [
            'delivery_id'       => 'sometimes|required|string|exists:delivery,delivery_id',
            'hub_id'            => 'sometimes|required|string|exists:logistichub,hub_id',
            'arrival_time'      => 'sometimes|required|date',
            'departure_time'    => 'nullable|date|after_or_equal:arrival_time',
            'processing_status' => 'sometimes|required|string',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $detail->update($validator->validated());

        return $detail->load(['delivery', 'hub']);
    }

    public function delete(string $id): void
    {
        $detail = DeliveryDetails::findOrFail($id);
        $detail->delete();
    }
}
