<?php

namespace App\Services\Payment;

class PayPalGateway
{
    public function process($amount, $details)
    {
        // Simulate PayPal processing
        return [
            'success' => true,
            'transaction_id' => 'PP' . time()
        ];
    }
}