<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Api\ProofOfDeliveryService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProofOfDeliveryController extends Controller
{
    protected $proofOfDeliveryService;

    public function __construct(ProofOfDeliveryService $proofOfDeliveryService)
    {
        $this->proofOfDeliveryService = $proofOfDeliveryService;
    }

    public function getAll()
    {
        return response()->json($this->proofOfDeliveryService->getAll());
    }

    public function add(Request $request)
    {
        $pod = $this->proofOfDeliveryService->create($request->all());
        return response()->json($pod, Response::HTTP_CREATED);
    }

    public function get($proof_id)
    {
        return response()->json($this->proofOfDeliveryService->getById($proof_id));
    }

    public function getBatch(int $pageNo)
    {
        return response()->json($this->proofOfDeliveryService->getPaginated($pageNo));
    }

    public function update(Request $request, $proof_id)
    {
        $pod = $this->proofOfDeliveryService->update($proof_id, $request->all());
        return response()->json($pod);
    }

    public function delete($proof_id)
    {
        $this->proofOfDeliveryService->delete($proof_id);
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
