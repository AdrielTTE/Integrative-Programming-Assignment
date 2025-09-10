<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Api\DeliveryService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DeliveryController extends Controller
{
    protected $deliveryService;

    public function __construct(DeliveryService $deliveryService)
    {
        $this->deliveryService = $deliveryService;
    }

    public function getAll()
    {
        return response()->json($this->deliveryService->getAll());
    }

    public function add(Request $request)
    {
        $delivery = $this->deliveryService->create($request->all());
        return response()->json($delivery, Response::HTTP_CREATED);
    }

    public function get($delivery_id)
    {
        return response()->json($this->deliveryService->getById($delivery_id));
    }

    public function getBatch(int $pageNo)
    {
        return response()->json($this->deliveryService->getPaginated($pageNo));
    }

    public function update(Request $request, $delivery_id)
    {
        $delivery = $this->deliveryService->update($delivery_id, $request->all());
        return response()->json($delivery);
    }

    public function delete($delivery_id)
    {
        $this->deliveryService->delete($delivery_id);
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}

