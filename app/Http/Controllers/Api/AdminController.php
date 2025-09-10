<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Api\AdminService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AdminController extends Controller
{
    protected $adminService;

    public function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
    }

    public function getAll()
    {
        return response()->json($this->adminService->getAll());
    }

    public function add(Request $request)
    {
        $admin = $this->adminService->create($request->all());
        return response()->json($admin, Response::HTTP_CREATED);
    }

    public function get($admin_id)
    {
        return response()->json($this->adminService->getById($admin_id));
    }

    public function getBatch(int $pageNo)
    {
        return response()->json($this->adminService->getBatch($pageNo));
    }

    public function update(Request $request, $admin_id)
    {
        $admin = $this->adminService->update($admin_id, $request->all());
        return response()->json($admin);
    }

    public function delete($admin_id)
    {
        $this->adminService->delete($admin_id);
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
