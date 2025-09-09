<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AdminController extends Controller
{
    // GET /api/admin/admins
    public function getAll()
    {
        return response()->json(Admin::all());
    }

    // POST /api/admin/admins
    public function add(Request $request)
    {
        $validated = $request->validate([
            'admin_id'     => 'required|string|unique:admin,admin_id',
            'employee_id'  => 'required|string',
            'department'   => 'required|string',
            'access_level' => 'required|string',
            'last_login'   => 'nullable|date',
        ]);

        $admin = Admin::create($validated);
        return response()->json($admin, Response::HTTP_CREATED);
    }

    // GET /api/admin/admins/{admin_id}
    public function get($admin_id)
    {
        $admin = Admin::findOrFail($admin_id);
        return response()->json($admin);
    }

    // GET /api/admin/admins/page/{pageNo}
    public function getBatch(int $pageNo)
    {
        $perPage = 20;
        $admins = Admin::paginate($perPage, ['*'], 'page', $pageNo);
        return response()->json($admins);
    }

    // PUT /api/admin/admins/{admin_id}
    public function update(Request $request, $admin_id)
    {
        $admin = Admin::findOrFail($admin_id);

        $validated = $request->validate([
            // ⬇️ adjust if different employees table/column
            'employee_id'  => 'sometimes|required|string|exists:employees,employee_id',
            'department'   => 'sometimes|required|string',
            'access_level' => 'sometimes|required|string',
            'last_login'   => 'nullable|date',
        ]);

        $admin->update($validated);
        return response()->json($admin);
    }

    // DELETE /api/admin/admins/{admin_id}
    public function delete($admin_id)
    {
        $admin = Admin::findOrFail($admin_id);
        $admin->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
