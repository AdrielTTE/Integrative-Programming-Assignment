<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Api\LogisticHubService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LogisticHubController extends Controller
{
    protected $logisticHubService;

    public function __construct(LogisticHubService $logisticHubService)
    {
        $this->logisticHubService = $logisticHubService;
    }

    public function getAll()
    {
        return response()->json($this->logisticHubService->getAll());
    }

    public function add(Request $request)
    {
        $hub = $this->logisticHubService->create($request->all());
        return response()->json($hub, Response::HTTP_CREATED);
    }

    public function get($hub_id)
    {
        return response()->json($this->logisticHubService->getById($hub_id));
    }

    public function getBatch(int $pageNo)
    {
        return response()->json($this->logisticHubService->getPaginated($pageNo));
    }

    public function update(Request $request, $hub_id)
    {
        $hub = $this->logisticHubService->update($hub_id, $request->all());
        return response()->json($hub);
    }

    public function delete($hub_id)
    {
        $this->logisticHubService->delete($hub_id);
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
