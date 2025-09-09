<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LogisticHub;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LogisticHubController extends Controller
{
    // GET /api/admin/logistic-hubs
    public function getAll()
    {
        return response()->json(LogisticHub::orderBy('hub_name')->get());
    }

    // POST /api/admin/logistic-hubs
    public function add(Request $request)
    {
        $validated = $request->validate([
            'hub_id'         => 'required|string|unique:logistichub,hub_id',
            'hub_name'       => 'required|string|max:150',
            'hub_address'    => 'nullable|string|max:255',
            'hub_capacity'   => 'nullable|integer|min:0',
            'hub_manager'    => 'nullable|string|max:150',
            'contact_number' => 'nullable|string|max:30', // tighten with regex if you want
        ]);

        $hub = LogisticHub::create($validated);
        return response()->json($hub, Response::HTTP_CREATED);
    }

    // GET /api/admin/logistic-hubs/{hub_id}
    public function get($hub_id)
    {
        $hub = LogisticHub::findOrFail($hub_id);
        return response()->json($hub);
    }

    // GET /api/admin/logistic-hubs/page/{pageNo}
    public function getBatch(int $pageNo)
    {
        $perPage = 20;
        $hubs = LogisticHub::orderBy('hub_name')->paginate($perPage, ['*'], 'page', $pageNo);
        return response()->json($hubs);
    }

    // PUT /api/admin/logistic-hubs/{hub_id}
    public function update(Request $request, $hub_id)
    {
        $hub = LogisticHub::findOrFail($hub_id);

        $validated = $request->validate([
            'hub_name'       => 'sometimes|required|string|max:150',
            'hub_address'    => 'nullable|string|max:255',
            'hub_capacity'   => 'nullable|integer|min:0',
            'hub_manager'    => 'nullable|string|max:150',
            'contact_number' => 'nullable|string|max:30',
        ]);

        $hub->update($validated);
        return response()->json($hub);
    }

    // DELETE /api/admin/logistic-hubs/{hub_id}
    public function delete($hub_id)
    {
        $hub = LogisticHub::findOrFail($hub_id);
        $hub->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
