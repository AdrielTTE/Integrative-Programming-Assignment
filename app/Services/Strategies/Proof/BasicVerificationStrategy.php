<?php

namespace App\Services\Strategies\Proof;

use App\Models\ProofOfDelivery;

class BasicVerificationStrategy implements VerificationStrategyInterface
{
    public function verify(ProofOfDelivery $proof): array
    {
        $details = [];
        $isValid = true;

        if (empty($proof->proof_url) && empty($proof->recipient_signature_name)) {
            $isValid = false;
            $details[] = 'Proof URL or recipient signature is missing.';
        }

        if ($proof->proof_type === 'SIGNATURE' && empty($proof->recipient_signature_name)) {
            $isValid = false;
            $details[] = 'Signature proof is missing the recipient name.';
        }

        if ($proof->proof_type === 'PHOTO' && empty($proof->proof_url)) {
            $isValid = false;
            $details[] = 'Photo proof is missing the image URL.';
        }
        
        $details[] = 'Timestamp: ' . $proof->timestamp_created;

        return [
            'is_valid' => $isValid,
            'details' => $details,
            'strategy' => 'Basic Verification'
        ];
    }
}