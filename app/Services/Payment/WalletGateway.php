<?php

namespace App\Services\Payment;

class WalletGateway
{
    public function process($amount, $details)
    {
        // Simulate digital wallet processing
        return [
            'success' => true,
            'transaction_id' => 'DW' . time()
        ];
    }
}