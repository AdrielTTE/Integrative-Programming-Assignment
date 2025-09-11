<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Api\DeliveryDriverService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DeliveryDriverController extends Controller
{
    protected $deliveryDriverService;

    public function __construct(DeliveryDriverService $deliveryDriverService)
    {
        $this->deliveryDriverService = $deliveryDriverService;
    }

    public function getAll()
    {
        return response()->json($this->deliveryDriverService->getAll());
    }

    public function add(Request $request)
    {
        $driver = $this->deliveryDriverService->create($request->all());
        return response()->json($driver, Response::HTTP_CREATED);
    }

    public function get($driver_id)
    {
        return response()->json($this->deliveryDriverService->getById($driver_id));
    }

    public function getBatch(int $pageNo, int $pageSize, string $status)
    {
        return response()->json($this->deliveryDriverService->getBatch($pageNo, $pageSize, $status));
    }

    public function update(Request $request, $driver_id)
    {
        $driver = $this->deliveryDriverService->update($driver_id, $request->all());
        return response()->json($driver);
    }

    public function delete($driver_id)
    {
        $this->deliveryDriverService->delete($driver_id);
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
