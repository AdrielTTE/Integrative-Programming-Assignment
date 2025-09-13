<?php

namespace App\Services;

use App\Models\Delivery;
use App\Models\ProofOfDelivery;
use App\Services\Strategies\Proof\VerificationStrategyInterface;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Auth\Access\AuthorizationException;

class ProofService
{
    protected VerificationStrategyInterface $verificationStrategy;

    public function __construct(VerificationStrategyInterface $verificationStrategy)
    {
        $this->verificationStrategy = $verificationStrategy;
    }

    public function getProofForAdmin(string $proofId): ProofOfDelivery
    {
        return ProofOfDelivery::with('delivery.package.customer', 'verifier')->findOrFail($proofId);
    }

    public function verifyProof(ProofOfDelivery $proof): array
    {
        return $this->verificationStrategy->verify($proof);
    }

    public function getProofsAwaitingVerification()
    {
        return ProofOfDelivery::with('delivery.package')
            ->whereIn('verification_status', ['PENDING', 'NEEDS_RESUBMISSION'])
            ->orderBy('timestamp_created', 'desc')
            ->paginate(15);
    }

    public function getAllProofsPaginated()
    {
        return ProofOfDelivery::with(['verifier', 'delivery.package'])
            ->orderBy('verified_at', 'desc')
            ->orderBy('timestamp_created', 'desc')
            ->paginate(20);
    }

    public function processVerification(string $proofId, string $action, ?string $reason = null): string
    {
        Auth::loginUsingId('AD001');
        $adminId = Auth::id();
        $proof = ProofOfDelivery::with('delivery')->findOrFail($proofId);
        $delivery = $proof->delivery;
        if (!$delivery) {
            throw new \Exception('Associated delivery not found for this proof.');
        }
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
                throw new \Exception('Invalid verification action specified.');
        }
        $proof->verified_at = now();
        $proof->verified_by = $adminId;
        $proof->save();
        $delivery->save();
        Auth::logout();
        return $message;
    }

    public function getProofByPackageId(string $packageId)
    {
        return ProofOfDelivery::whereHas('delivery', function ($query) use ($packageId) {
            $query->where('package_id', $packageId);
        })->first();
    }

    public function getProofMetadata(ProofOfDelivery $proof): array
    {
        if ($proof->proof_type === 'PHOTO') {
            return [
                'Capture Device' => 'MobileApp v2.1 (Android)',
                'GPS Coordinates' => '3.1390° N, 101.6869° E (Simulated)',
                'Device IP Address' => '101.32.115.45 (Simulated)',
            ];
        }
        return [];
    }

    public function getProofsForCustomer()
    {
        $customerId = Auth::id() ?? 'C004'; // Hardcoded for testing

        // This query finds proofs where the delivery's package belongs to the customer
        return ProofOfDelivery::with(['delivery.package'])
            ->whereHas('delivery.package', function ($query) use ($customerId) {
                $query->where('customer_id', $customerId);
            })
            ->orderBy('timestamp_created', 'desc')
            ->paginate(10);
    }
    
    public function saveCustomerReport(string $proofId, string $reason): ProofOfDelivery
    {
        $proof = ProofOfDelivery::findOrFail($proofId);
        $customerId = Auth::id() ?? 'C001';
        $isOwner = $proof->delivery->package->customer_id === $customerId;
        if (!$isOwner) {
            throw new AuthorizationException('You are not authorized to report this proof.');
        }
        $proof->notes = "Customer Report ({$customerId}): " . $reason;
        $proof->verification_status = 'NEEDS_RESUBMISSION';
        $proof->save();
        return $proof;
    }
}