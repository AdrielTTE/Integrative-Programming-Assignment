<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class VehicleController extends Controller
{
    // GET /api/admin/vehicles
    public function getAll()
    {
        return response()->json(
            Vehicle::orderBy('vehicle_id')->get()
        );
    }

    // POST /api/admin/vehicles
    public function add(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id'            => 'required|string|unique:vehicle,vehicle_id',
            'vehicle_type'          => 'required|string|max:100',
            'vehicle_capacity'      => 'nullable|numeric|min:0',
            'vehicle_status'        => 'required|string|max:50',
            'last_maintenance_date' => 'nullable|date',
        ]);

        $vehicle = Vehicle::create($validated);
        return response()->json($vehicle, Response::HTTP_CREATED);
    }

    // GET /api/admin/vehicles/{vehicle_id}
    public function get($vehicle_id)
    {
        $vehicle = Vehicle::findOrFail($vehicle_id);
        return response()->json($vehicle);
    }

    // GET /api/admin/vehicles/page/{pageNo}
    public function getBatch(int $pageNo)
    {
        $perPage = 20;
        $vehicles = Vehicle::orderBy('vehicle_id')
            ->paginate($perPage, ['*'], 'page', $pageNo);

        return response()->json($vehicles);
    }

    // PUT /api/admin/vehicles/{vehicle_id}
    public function update(Request $request, $vehicle_id)
    {
        $vehicle = Vehicle::findOrFail($vehicle_id);

        $validated = $request->validate([
            'vehicle_type'          => 'sometimes|required|string|max:100',
            'vehicle_capacity'      => 'nullable|numeric|min:0',
            'vehicle_status'        => 'sometimes|required|string|max:50',
            'last_maintenance_date' => 'nullable|date',
        ]);

        $vehicle->update($validated);
        return response()->json($vehicle);
    }

    // DELETE /api/admin/vehicles/{vehicle_id}
    public function delete($vehicle_id)
    {
        $vehicle = Vehicle::findOrFail($vehicle_id);
        $vehicle->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
