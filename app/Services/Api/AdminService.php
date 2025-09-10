<?php


namespace App\Services\Api;

use App\Models\Admin;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AdminService
{
    public function getAll()
    {
        return Admin::all();
    }

    public function create(array $data)
    {
        $validator = Validator::make($data, [
            'admin_id'     => 'required|string|unique:admin,admin_id',
            'employee_id'  => 'required|string',
            'department'   => 'required|string',
            'access_level' => 'required|string',
            'last_login'   => 'nullable|date',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return Admin::create($validator->validated());
    }

    public function getById(string $adminId)
    {
        return Admin::findOrFail($adminId);
    }

    public function getBatch(int $pageNo, int $perPage = 20)
    {
        return Admin::paginate($perPage, ['*'], 'page', $pageNo);
    }

    public function update(string $adminId, array $data)
    {
        $admin = Admin::findOrFail($adminId);

        $validator = Validator::make($data, [
            'employee_id'  => 'sometimes|required|string|exists:employees,employee_id',
            'department'   => 'sometimes|required|string',
            'access_level' => 'sometimes|required|string',
            'last_login'   => 'nullable|date',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $admin->update($validator->validated());
        return $admin;
    }

    public function delete(string $adminId): void
    {
        $admin = Admin::findOrFail($adminId);
        $admin->delete();
    }
}
