<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Api\RouteService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RouteController extends Controller
{
    protected $routeService;

    public function __construct(RouteService $routeService)
    {
        $this->routeService = $routeService;
    }

    public function getAll()
    {
        return response()->json($this->routeService->getAll());
    }

    public function add(Request $request)
    {
        $route = $this->routeService->create($request->all());
        return response()->json($route, Response::HTTP_CREATED);
    }

    public function get($route_id)
    {
        return response()->json($this->routeService->getById($route_id));
    }

    public function getBatch(int $pageNo)
    {
        return response()->json($this->routeService->getPaginated($pageNo));
    }

    public function update(Request $request, $route_id)
    {
        $route = $this->routeService->update($route_id, $request->all());
        return response()->json($route);
    }

    public function delete($route_id)
    {
        $this->routeService->delete($route_id);
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
