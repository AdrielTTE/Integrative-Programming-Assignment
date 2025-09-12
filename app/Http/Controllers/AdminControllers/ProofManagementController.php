<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use App\Services\ProofService;
use Illuminate\Http\Request;

class ProofManagementController extends Controller
{
    public function __construct(protected ProofService $proofService)
    {
    }

    public function index()
    {
        $proofs = $this->proofService->getProofsAwaitingVerification();
        return view('admin.proof.index', compact('proofs'));
    }

    public function show(string $proofId)
    {
        $proof = $this->proofService->getProofForAdmin($proofId);
        $verificationDetails = $this->proofService->verifyProof($proof);
        return view('admin.proof.show', compact('proof', 'verificationDetails'));
    }

    public function updateStatus(Request $request, string $proofId)
    {
        $request->validate([
            'action' => 'required|string|in:approve,reject,resubmit',
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        $action = $request->input('action');
        $reason = $request->input('rejection_reason');

        try {
            $message = $this->proofService->processVerification($proofId, $action, $reason);
            if ($action === 'approve') {
                return redirect()->route('admin.proof.history')->with('success', $message);
            }
            return redirect()->route('admin.proof.index')->with('success', $message);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to process proof: ' . $e->getMessage());
        }
    }

    public function history()
    {
        $proofs = $this->proofService->getAllProofsPaginated();
        return view('admin.proof.history', compact('proofs'));
    }
}