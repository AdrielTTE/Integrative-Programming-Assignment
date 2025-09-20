<?php

namespace App\Services\Payment;

class CardPaymentGateway
{
    public function process($amount, $details)
    {
        // Simulate card payment processing
        // In real implementation, connect to payment gateway API
        
        // Security: Validate card details
        if (empty($details['card_number']) || empty($details['cvv'])) {
            return ['success' => false, 'error' => 'Invalid card details'];
        }
        
        // Simulate processing delay
        sleep(1);
        
        // 90% success rate simulation
        if (rand(1, 10) > 1) {
            return [
                'success' => true,
                'transaction_id' => 'TXN' . time()
            ];
        }
        
        return ['success' => false, 'error' => 'Card declined'];
    }
}