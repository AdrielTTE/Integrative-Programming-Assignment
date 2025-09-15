<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProofOfDelivery;
use App\Models\Delivery; 
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

    public function getAll(Request $request)
    {
        $query = ProofOfDelivery::query()->with('delivery.package');

        // Check for the specific status filter sent by the ProofService
        if ($request->input('status') === 'awaiting_verification') {
            $query->whereIn('verification_status', ['PENDING', 'NEEDS_RESUBMISSION', 'REJECTED']);
        }

        // Always paginate the results, as the service layer expects this structure
        $proofs = $query->orderBy('timestamp_created', 'desc')->paginate(15);

        return response()->json($proofs);
    }

    public function add(Request $request)
    {
        $pod = $this->proofOfDeliveryService->create($request->all());
        return response()->json($pod, Response::HTTP_CREATED);
    }
    public function getHistory()
    {
        $proofs = ProofOfDelivery::with(['verifier', 'delivery.package'])
            ->orderBy('verified_at', 'desc')
            ->orderBy('timestamp_created', 'desc')
            ->paginate(20); // Using the same pagination count as your original service

        return response()->json($proofs);
    }

    public function get($proof_id)
    {
        return response()->json($this->proofOfDeliveryService->getById($proof_id));
    }

    public function getBatch(int $pageNo)
    {
        return response()->json($this->proofOfDeliveryService->getPaginated($pageNo));
    }
    /**
     * Handles a report submitted by a customer for a specific proof of delivery.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $proof_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function customerReport(Request $request, string $proof_id)
    {
        $validated = $request->validate([
            'reason' => 'required|string|min:10|max:1000',
            'customer_id' => 'required|string|exists:user,user_id',
        ]);

        $proof = ProofOfDelivery::with('delivery.package')->findOrFail($proof_id);

        if ($proof->delivery->package->customer_id !== $validated['customer_id']) {
            
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $proof->notes = "Customer Report (" . $validated['customer_id'] . "): " . $validated['reason'];
        $proof->verification_status = 'NEEDS_RESUBMISSION'; // Set the status
        $proof->save();
        return response()->json([
            'message' => 'Report submitted successfully. The proof is now awaiting admin review.'
        ]);
    }
    public function processVerification(Request $request, string $proof_id)
    {
        $request->validate([
            'action' => 'required|string|in:approve,reject,resubmit',
            'reason' => 'nullable|string|max:500',
            'admin_id' => 'required|string|exists:user,user_id',
        ]);

        $proof = ProofOfDelivery::with('delivery')->findOrFail($proof_id);
        $delivery = $proof->delivery;

        if (!$delivery) {
            return response()->json(['message' => 'Associated delivery not found for this proof.'], 404);
        }

        $action = $request->input('action');
        $reason = $request->input('reason');
        $message = '';

        switch ($action) {
            case 'approve':
                $proof->verification_status = 'APPROVED';
                $proof->notes = 'Proof approved on ' . now();
                $delivery->delivery_status = 'DELIVERED';
                $delivery->actual_delivery_time = now();
                $message = 'Proof has been approved and delivery marked as complete.';
                break;
            case 'reject':
                $proof->verification_status = 'REJECTED';
                $proof->notes = 'Proof REJECTED. Reason: ' . ($reason ?: 'Not specified.');
                $delivery->delivery_status = 'FAILED';
                $message = 'Proof has been rejected and delivery marked as failed.';
                break;
            case 'resubmit':
                $proof->verification_status = 'NEEDS_RESUBMISSION';
                $proof->notes = 'PROOF RESUBMISSION REQUESTED. Reason: ' . ($reason ?: 'Not specified.');
                $message = 'Proof has been rejected and a resubmission has been requested.';
                break;
            default:
                 return response()->json(['message' => 'Invalid verification action specified.'], 400);
        }

        $proof->verified_at = now();
        $proof->verified_by = $request->input('admin_id');
        $proof->save();
        $delivery->save();

        return response()->json(['message' => $message]);
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
