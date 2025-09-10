<?php

namespace App\Services\Api;

use App\Models\Route as TransportRoute;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class RouteService
{
    public function getAll()
    {
        return TransportRoute::orderBy('route_name')->get();
    }

    public function create(array $data)
    {
        $validator = Validator::make($data, [
            'route_id'                   => 'required|string|unique:route,route_id',
            'route_name'                 => 'required|string|max:150',
            'start_location'             => 'required|string|max:150',
            'end_location'               => 'required|string|max:150',
            'estimated_duration_minutes' => 'nullable|integer|min:0',
            'distance_km'                => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return TransportRoute::create($validator->validated());
    }

    public function getById(string $id)
    {
        return TransportRoute::findOrFail($id);
    }

    public function getPaginated(int $pageNo, int $perPage = 20)
    {
        return TransportRoute::orderBy('route_name')
            ->paginate($perPage, ['*'], 'page', $pageNo);
    }

    public function update(string $id, array $data)
    {
        $route = TransportRoute::findOrFail($id);

        $validator = Validator::make($data, [
            'route_name'                 => 'sometimes|required|string|max:150',
            'start_location'             => 'sometimes|required|string|max:150',
            'end_location'               => 'sometimes|required|string|max:150',
            'estimated_duration_minutes' => 'nullable|integer|min:0',
            'distance_km'                => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $route->update($validator->validated());
        return $route;
    }

    public function delete(string $id): void
    {
        $route = TransportRoute::findOrFail($id);
        $route->delete();
    }
}
