<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Route as TransportRoute; // <-- alias the model
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RouteController extends Controller
{
    // GET /api/admin/routes
    public function getAll()
    {
        return response()->json(TransportRoute::orderBy('route_name')->get());
    }

    // POST /api/admin/routes
    public function add(Request $request)
    {
        $validated = $request->validate([
            'route_id'                   => 'required|string|unique:route,route_id',
            'route_name'                 => 'required|string|max:150',
            'start_location'             => 'required|string|max:150',
            'end_location'               => 'required|string|max:150',
            'estimated_duration_minutes' => 'nullable|integer|min:0',
            'distance_km'                => 'nullable|numeric|min:0',
        ]);

        $route = TransportRoute::create($validated);
        return response()->json($route, Response::HTTP_CREATED);
    }

    // GET /api/admin/routes/{route_id}
    public function get($route_id)
    {
        $route = TransportRoute::findOrFail($route_id);
        return response()->json($route);
    }

    // GET /api/admin/routes/page/{pageNo}
    public function getBatch(int $pageNo)
    {
        $perPage = 20;
        $routes = TransportRoute::orderBy('route_name')
            ->paginate($perPage, ['*'], 'page', $pageNo);
        return response()->json($routes);
    }

    // PUT /api/admin/routes/{route_id}
    public function update(Request $request, $route_id)
    {
        $route = TransportRoute::findOrFail($route_id);

        $validated = $request->validate([
            'route_name'                 => 'sometimes|required|string|max:150',
            'start_location'             => 'sometimes|required|string|max:150',
            'end_location'               => 'sometimes|required|string|max:150',
            'estimated_duration_minutes' => 'nullable|integer|min:0',
            'distance_km'                => 'nullable|numeric|min:0',
        ]);

        $route->update($validated);
        return response()->json($route);
    }

    // DELETE /api/admin/routes/{route_id}
    public function delete($route_id)
    {
        $route = TransportRoute::findOrFail($route_id);
        $route->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
