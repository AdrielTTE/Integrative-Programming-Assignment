<?php

namespace App\Services\Api;

use App\Models\ProofOfDelivery;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ProofOfDeliveryService
{
    public function getAll()
    {
        return ProofOfDelivery::with('delivery')->get();
    }

    public function create(array $data)
    {
        $validator = Validator::make($data, [
            'proof_id'                 => 'required|string|unique:proofofdelivery,proof_id',
            'delivery_id'              => 'required|string|exists:delivery,delivery_id',
            'proof_type'               => 'required|string|max:100',
            'proof_url'                => 'nullable|url|max:2048',
            'recipient_signature_name' => 'nullable|string|max:150',
            'timestamp_created'        => 'nullable|date',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return ProofOfDelivery::create($validator->validated())->load('delivery');
    }

    public function getById(string $id)
    {
        return ProofOfDelivery::with('delivery')->findOrFail($id);
    }

    public function getPaginated(int $pageNo, int $perPage = 20)
    {
        return ProofOfDelivery::with('delivery')->paginate($perPage, ['*'], 'page', $pageNo);
    }

    public function update(string $id, array $data)
    {
        $pod = ProofOfDelivery::findOrFail($id);

        $validator = Validator::make($data, [
            'delivery_id'              => 'sometimes|required|string|exists:delivery,delivery_id',
            'proof_type'               => 'sometimes|required|string|max:100',
            'proof_url'                => 'nullable|url|max:2048',
            'recipient_signature_name' => 'nullable|string|max:150',
            'timestamp_created'        => 'nullable|date',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $pod->update($validator->validated());

        return $pod->load('delivery');
    }

    public function delete(string $id): void
    {
        $pod = ProofOfDelivery::findOrFail($id);
        $pod->delete();
    }
}
