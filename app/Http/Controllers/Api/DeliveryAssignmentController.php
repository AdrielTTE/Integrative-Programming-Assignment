<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Api\DeliveryAssignmentService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DeliveryAssignmentController extends Controller
{
    protected $assignmentService;

    public function __construct(DeliveryAssignmentService $assignmentService)
    {
        $this->assignmentService = $assignmentService;
    }

    public function getAll()
    {
        return response()->json($this->assignmentService->getAll());
    }

    public function add(Request $request)
    {
        $assignment = $this->assignmentService->create($request->all());
        return response()->json($assignment, Response::HTTP_CREATED);
    }

    public function get($assignment_id)
    {
        return response()->json($this->assignmentService->getById($assignment_id));
    }

    public function getBatch(int $pageNo)
    {
        return response()->json($this->assignmentService->getPaginated($pageNo));
    }

    public function update(Request $request, $assignment_id)
    {
        $assignment = $this->assignmentService->update($assignment_id, $request->all());
        return response()->json($assignment);
    }

    public function delete($assignment_id)
    {
        $this->assignmentService->delete($assignment_id);
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
