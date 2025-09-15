<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Api\VehicleService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class VehicleController extends Controller
{
    protected $vehicleService;

    public function __construct(VehicleService $vehicleService)
    {
        $this->vehicleService = $vehicleService;
    }

    public function getAll()
    {
        return response()->json($this->vehicleService->getAll());
    }

    public function add(Request $request)
    {
        $vehicle = $this->vehicleService->create($request->all());
        return response()->json($vehicle, Response::HTTP_CREATED);
    }

    public function get($vehicle_id)
    {
        return response()->json($this->vehicleService->getById($vehicle_id));
    }

    public function getBatch(int $pageNo)
    {
        return response()->json($this->vehicleService->getPaginated($pageNo));
    }

    public function update(Request $request, $vehicle_id)
    {
        $vehicle = $this->vehicleService->update($vehicle_id, $request->all());
        return response()->json($vehicle);
    }

    public function delete($vehicle_id)
    {
        $this->vehicleService->delete($vehicle_id);
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    public function getCountByStatus(string $status){
        return response()->json($this->vehicleService->getCountByStatus($status));
    }
}
