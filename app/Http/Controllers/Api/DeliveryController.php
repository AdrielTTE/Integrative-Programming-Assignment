<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DeliveryController extends Controller
{
    // GET /api/deliveries
    public function getAll()
    {
        return response()->json(Delivery::all());
    }

    // POST /api/deliveries
    public function add(Request $request)
    {
        $validated = $request->validate([
            'delivery_id' => 'required|string|unique:delivery,delivery_id',
            'package_id' => 'required|string|exists:packages,package_id',
            'driver_id' => 'required|string|exists:delivery_drivers,driver_id',
            'vehicle_id' => 'required|string|exists:vehicles,vehicle_id',
            'route_id' => 'required|string|exists:routes,route_id',
            'pickup_time' => 'required|date',
            'estimated_delivery_time' => 'required|date|after_or_equal:pickup_time',
            'actual_delivery_time' => 'nullable|date',
            'delivery_status' => 'required|string',
            'delivery_cost' => 'required|numeric',
        ]);

        $delivery = Delivery::create($validated);
        return response()->json($delivery, Response::HTTP_CREATED);
    }

    // GET /api/deliveries/{delivery_id}
    public function get($delivery_id)
    {
        $delivery = Delivery::findOrFail($delivery_id);
        return response()->json($delivery);
    }

    public function getBatch(int $pageNo)
    {
        $perPage = 20;
        $delivery = Delivery::paginate($perPage, ['*'], 'page', $pageNo);
        return response()->json($delivery);
    }

    // PUT /api/deliveries/{delivery_id}
    public function update(Request $request, $delivery_id)
    {
        $delivery = Delivery::findOrFail($delivery_id);

        $validated = $request->validate([
            'package_id' => 'sometimes|required|string|exists:packages,package_id',
            'driver_id' => 'sometimes|required|string|exists:delivery_drivers,driver_id',
            'vehicle_id' => 'sometimes|required|string|exists:vehicles,vehicle_id',
            'route_id' => 'sometimes|required|string|exists:routes,route_id',
            'pickup_time' => 'sometimes|required|date',
            'estimated_delivery_time' => 'sometimes|required|date|after_or_equal:pickup_time',
            'actual_delivery_time' => 'nullable|date',
            'delivery_status' => 'sometimes|required|string',
            'delivery_cost' => 'sometimes|required|numeric',
        ]);

        $delivery->update($validated);
        return response()->json($delivery);
    }

    // DELETE /api/deliveries/{delivery_id}
    public function delete($delivery_id)
    {
        $delivery = Delivery::findOrFail($delivery_id);
        $delivery->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
