<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeliveryDriver;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DeliveryDriverController extends Controller
{
    // GET /api/admin/delivery-drivers
    public function getAll()
    {
        return response()->json(DeliveryDriver::all());
    }

    // POST /api/admin/delivery-drivers
    public function add(Request $request)
    {
        $validated = $request->validate([
            'driver_id'       => 'required|string|unique:deliverydriver,driver_id',
            'first_name'      => 'required|string|max:100',
            'last_name'       => 'required|string|max:100',
            'license_number'  => 'required|string|max:100|unique:deliverydriver,license_number',
            'hire_date'       => 'required|date',
            'driver_status'   => 'required|string',
        ]);

        $driver = DeliveryDriver::create($validated);
        return response()->json($driver, Response::HTTP_CREATED);
    }

    // GET /api/admin/delivery-drivers/{driver_id}
    public function get($driver_id)
    {
        $driver = DeliveryDriver::findOrFail($driver_id);
        return response()->json($driver);
    }

    // GET /api/admin/delivery-drivers/page/{pageNo}
    public function getBatch(int $pageNo)
    {
        $perPage  = 20;
        $drivers = DeliveryDriver::paginate($perPage, ['*'], 'page', $pageNo);
        return response()->json($drivers);
    }

    // PUT /api/admin/delivery-drivers/{driver_id}
    public function update(Request $request, $driver_id)
    {
        $driver = DeliveryDriver::findOrFail($driver_id);

        $validated = $request->validate([
            'first_name'      => 'sometimes|required|string|max:100',
            'last_name'       => 'sometimes|required|string|max:100',
            // allow keeping the same license_number on this driver
            'license_number'  => "sometimes|required|string|max:100|unique:deliverydriver,license_number,{$driver_id},driver_id",
            'hire_date'       => 'sometimes|required|date',
            'driver_status'   => 'sometimes|required|string',
        ]);

        $driver->update($validated);
        return response()->json($driver);
    }

    // DELETE /api/admin/delivery-drivers/{driver_id}
    public function delete($driver_id)
    {
        $driver = DeliveryDriver::findOrFail($driver_id);
        $driver->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
