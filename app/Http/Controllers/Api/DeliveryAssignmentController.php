<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeliveryAssignment;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DeliveryAssignmentController extends Controller
{
    // GET /api/admin/delivery-assignments
    public function getAll()
    {
        return response()->json(DeliveryAssignment::all());
        // Or include relations:
        // return response()->json(DeliveryAssignment::with(['admin','package','driver'])->get());
    }

    // POST /api/admin/delivery-assignments
    public function add(Request $request)
    {
        $validated = $request->validate([
            'assignment_id'     => 'required|string|unique:deliveryassignment,assignment_id',
            'admin_id'          => 'required|string|exists:admin,admin_id',
            'package_id'        => 'required|string|exists:packages,package_id',
            'driver_id'         => 'required|string|exists:delivery_drivers,driver_id',
            'assigned_date'     => 'required|date',
            'assignment_status' => 'required|string',
            'notes'             => 'nullable|string',
        ]);

        $assignment = DeliveryAssignment::create($validated);
        return response()->json($assignment, Response::HTTP_CREATED);
    }

    // GET /api/admin/delivery-assignments/{assignment_id}
    public function get($assignment_id)
    {
        $assignment = DeliveryAssignment::findOrFail($assignment_id);
        return response()->json($assignment);
        // with relations:
        // return response()->json(DeliveryAssignment::with(['admin','package','driver'])->findOrFail($assignment_id));
    }

    // GET /api/admin/delivery-assignments/page/{pageNo}
    public function getBatch(int $pageNo)
    {
        $perPage = 20;
        $assignments = DeliveryAssignment::paginate($perPage, ['*'], 'page', $pageNo);
        return response()->json($assignments);
    }

    // PUT /api/admin/delivery-assignments/{assignment_id}
    public function update(Request $request, $assignment_id)
    {
        $assignment = DeliveryAssignment::findOrFail($assignment_id);

        $validated = $request->validate([
            'admin_id'          => 'sometimes|required|string|exists:admin,admin_id',
            'package_id'        => 'sometimes|required|string|exists:packages,package_id',
            'driver_id'         => 'sometimes|required|string|exists:delivery_drivers,driver_id',
            'assigned_date'     => 'sometimes|required|date',
            'assignment_status' => 'sometimes|required|string',
            'notes'             => 'nullable|string',
        ]);

        $assignment->update($validated);
        return response()->json($assignment);
    }

    // DELETE /api/admin/delivery-assignments/{assignment_id}
    public function delete($assignment_id)
    {
        $assignment = DeliveryAssignment::findOrFail($assignment_id);
        $assignment->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
