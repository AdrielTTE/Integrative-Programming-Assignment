<?php

namespace App\Services\Api;

use App\Models\LogisticHub;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class LogisticHubService
{
    public function getAll()
    {
        return LogisticHub::orderBy('hub_name')->get();
    }

    public function create(array $data)
    {
        $validator = Validator::make($data, [
            'hub_id'         => 'required|string|unique:logistichub,hub_id',
            'hub_name'       => 'required|string|max:150',
            'hub_address'    => 'nullable|string|max:255',
            'hub_capacity'   => 'nullable|integer|min:0',
            'hub_manager'    => 'nullable|string|max:150',
            'contact_number' => 'nullable|string|max:30',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return LogisticHub::create($validator->validated());
    }

    public function getById(string $id)
    {
        return LogisticHub::findOrFail($id);
    }

    public function getPaginated(int $pageNo, int $perPage = 20)
    {
        return LogisticHub::orderBy('hub_name')->paginate($perPage, ['*'], 'page', $pageNo);
    }

    public function update(string $id, array $data)
    {
        $hub = LogisticHub::findOrFail($id);

        $validator = Validator::make($data, [
            'hub_name'       => 'sometimes|required|string|max:150',
            'hub_address'    => 'nullable|string|max:255',
            'hub_capacity'   => 'nullable|integer|min:0',
            'hub_manager'    => 'nullable|string|max:150',
            'contact_number' => 'nullable|string|max:30',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $hub->update($validator->validated());
        return $hub;
    }

    public function delete(string $id): void
    {
        $hub = LogisticHub::findOrFail($id);
        $hub->delete();
    }
}
