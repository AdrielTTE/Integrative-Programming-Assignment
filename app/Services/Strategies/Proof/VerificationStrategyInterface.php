<?php

namespace App\Services\Strategies\Proof;

use App\Models\ProofOfDelivery;

interface VerificationStrategyInterface
{
    /**
     * Verify the proof of delivery.
     *
     * @param ProofOfDelivery $proof The proof to verify.
     * @return array An array with verification status and details.
     */
    public function verify(ProofOfDelivery $proof): array;
}