<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeliveryDetails;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DeliveryDetailsController extends Controller
{
    // GET /api/admin/delivery-details
    public function getAll()
    {
        // return response()->json(DeliveryDetails::all());
        return response()->json(
            DeliveryDetails::with(['delivery','hub'])->get() // include relations (nice for admin UI)
        );
    }

    // POST /api/admin/delivery-details
    public function add(Request $request)
    {
        $validated = $request->validate([
            'detail_id'         => 'required|string|unique:deliverydetails,detail_id',
            'delivery_id'       => 'required|string|exists:delivery,delivery_id',   // adjust table if yours differs
            'hub_id'            => 'required|string|exists:logistichub,hub_id',     // adjust to your actual hub table
            'arrival_time'      => 'required|date',
            'departure_time'    => 'nullable|date|after_or_equal:arrival_time',
            'processing_status' => 'required|string',
        ]);

        $detail = DeliveryDetails::create($validated);
        return response()->json($detail, Response::HTTP_CREATED);
    }

    // GET /api/admin/delivery-details/{detail_id}
    public function get($detail_id)
    {
        $detail = DeliveryDetails::with(['delivery','hub'])->findOrFail($detail_id);
        return response()->json($detail);
    }

    // GET /api/admin/delivery-details/page/{pageNo}
    public function getBatch(int $pageNo)
    {
        $perPage = 20;
        $details = DeliveryDetails::with(['delivery','hub'])
            ->paginate($perPage, ['*'], 'page', $pageNo);
        return response()->json($details);
    }

    // PUT /api/admin/delivery-details/{detail_id}
    public function update(Request $request, $detail_id)
    {
        $detail = DeliveryDetails::findOrFail($detail_id);

        $validated = $request->validate([
            'delivery_id'       => 'sometimes|required|string|exists:delivery,delivery_id',
            'hub_id'            => 'sometimes|required|string|exists:logistichub,hub_id',
            'arrival_time'      => 'sometimes|required|date',
            'departure_time'    => 'nullable|date|after_or_equal:arrival_time',
            'processing_status' => 'sometimes|required|string',
        ]);

        $detail->update($validated);
        return response()->json($detail->load(['delivery','hub']));
    }

    // DELETE /api/admin/delivery-details/{detail_id}
    public function delete($detail_id)
    {
        $detail = DeliveryDetails::findOrFail($detail_id);
        $detail->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
