<?php

namespace App\Services\Api;

use App\Models\Package;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PackageService
{
    public function getAll()
    {
        return Package::with(['customer', 'delivery', 'assignment'])->get();
    }

    public function create(array $data)
    {
        $validator = Validator::make($data, [
            'package_id'         => 'required|string|unique:package,package_id',
            'customer_id'        => 'required|string|exists:customer,customer_id',
            'tracking_number'    => 'required|string|unique:package,tracking_number',
            'package_weight'     => 'nullable|numeric',
            'package_dimensions' => 'nullable|string|max:100',
            'package_contents'   => 'nullable|string',
            'sender_address'     => 'required|string',
            'recipient_address'  => 'required|string',
            'package_status'     => 'required|string',
            'created_at'         => 'nullable|date',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return Package::create($validator->validated())->load(['customer', 'delivery', 'assignment']);
    }

    public function getById(string $id)
    {
        return Package::with(['customer', 'delivery', 'assignment'])->findOrFail($id);
    }

    public function getPaginated(int $pageNo, int $perPage = 20)
    {
        return Package::with(['customer', 'delivery', 'assignment'])
            ->paginate($perPage, ['*'], 'page', $pageNo);
    }

    public function update(string $id, array $data)
    {
        $pkg = Package::findOrFail($id);

        $validator = Validator::make($data, [
            'customer_id'        => 'sometimes|required|string|exists:customer,customer_id',
            'tracking_number'    => "sometimes|required|string|unique:package,tracking_number,{$id},package_id",
            'package_weight'     => 'nullable|numeric',
            'package_dimensions' => 'nullable|string|max:100',
            'package_contents'   => 'nullable|string',
            'sender_address'     => 'sometimes|required|string',
            'recipient_address'  => 'sometimes|required|string',
            'package_status'     => 'sometimes|required|string',
            'created_at'         => 'nullable|date',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $pkg->update($validator->validated());

        return $pkg->load(['customer', 'delivery', 'assignment']);
    }

    public function delete(string $id): void
    {
        $pkg = Package::findOrFail($id);
        $pkg->delete();
    }
}
