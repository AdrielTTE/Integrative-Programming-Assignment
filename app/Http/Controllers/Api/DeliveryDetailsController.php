<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Api\DeliveryDetailsService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DeliveryDetailsController extends Controller
{
    protected $deliveryDetailsService;

    public function __construct(DeliveryDetailsService $deliveryDetailsService)
    {
        $this->deliveryDetailsService = $deliveryDetailsService;
    }

    public function getAll()
    {
        return response()->json($this->deliveryDetailsService->getAll());
    }

    public function add(Request $request)
    {
        $detail = $this->deliveryDetailsService->create($request->all());
        return response()->json($detail, Response::HTTP_CREATED);
    }

    public function get($detail_id)
    {
        return response()->json($this->deliveryDetailsService->getById($detail_id));
    }

    public function getBatch(int $pageNo)
    {
        return response()->json($this->deliveryDetailsService->getPaginated($pageNo));
    }

    public function update(Request $request, $detail_id)
    {
        $detail = $this->deliveryDetailsService->update($detail_id, $request->all());
        return response()->json($detail);
    }

    public function delete($detail_id)
    {
        $this->deliveryDetailsService->delete($detail_id);
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
