<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProofOfDelivery;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProofOfDeliveryController extends Controller
{
    // GET /api/admin/proofs-of-delivery
    public function getAll()
    {
        return response()->json(
            ProofOfDelivery::with('delivery')->get()
        );
    }

    // POST /api/admin/proofs-of-delivery
    public function add(Request $request)
    {
        $validated = $request->validate([
            'proof_id'                => 'required|string|unique:proofofdelivery,proof_id',
            'delivery_id'             => 'required|string|exists:delivery,delivery_id',
            'proof_type'              => 'required|string|max:100',
            'proof_url'               => 'nullable|url|max:2048',
            'recipient_signature_name'=> 'nullable|string|max:150',
            'timestamp_created'       => 'nullable|date',
        ]);

        $pod = ProofOfDelivery::create($validated);
        return response()->json($pod->load('delivery'), Response::HTTP_CREATED);
    }

    // GET /api/admin/proofs-of-delivery/{proof_id}
    public function get($proof_id)
    {
        $pod = ProofOfDelivery::with('delivery')->findOrFail($proof_id);
        return response()->json($pod);
    }

    // GET /api/admin/proofs-of-delivery/page/{pageNo}
    public function getBatch(int $pageNo)
    {
        $perPage = 20;
        $pods = ProofOfDelivery::with('delivery')
            ->paginate($perPage, ['*'], 'page', $pageNo);
        return response()->json($pods);
    }

    // PUT /api/admin/proofs-of-delivery/{proof_id}
    public function update(Request $request, $proof_id)
    {
        $pod = ProofOfDelivery::findOrFail($proof_id);

        $validated = $request->validate([
            'delivery_id'             => 'sometimes|required|string|exists:delivery,delivery_id',
            'proof_type'              => 'sometimes|required|string|max:100',
            'proof_url'               => 'nullable|url|max:2048',
            'recipient_signature_name'=> 'nullable|string|max:150',
            'timestamp_created'       => 'nullable|date',
        ]);

        $pod->update($validated);
        return response()->json($pod->load('delivery'));
    }

    // DELETE /api/admin/proofs-of-delivery/{proof_id}
    public function delete($proof_id)
    {
        $pod = ProofOfDelivery::findOrFail($proof_id);
        $pod->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
