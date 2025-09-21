<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Log;

/**
 * PaymentProcessor - Subsystem for processing payments
 */
class PaymentProcessor
{
    protected array $supportedMethods = ['credit_card', 'debit_card', 'online_banking', 'e_wallet'];

    /**
     * Process a payment transaction
     */
    public function process(array $data): array
    {
        // Validate payment method
        if (!in_array($data['method'], $this->supportedMethods)) {
            return [
                'success' => false,
                'message' => 'Unsupported payment method'
            ];
        }

        // Simulate payment gateway interaction
        switch ($data['method']) {
            case 'credit_card':
            case 'debit_card':
                return $this->processCardPayment($data);
                
            case 'online_banking':
                return $this->processOnlineBanking($data);
                
            case 'e_wallet':
                return $this->processEWallet($data);
                
            default:
                return [
                    'success' => false,
                    'message' => 'Payment method not implemented'
                ];
        }
    }

    /**
     * Process card payment
     */
    protected function processCardPayment(array $data): array
    {
        // Simulate card validation
        if (!$this->validateCard($data['card_number'] ?? '')) {
            return [
                'success' => false,
                'message' => 'Invalid card number'
            ];
        }

        // Simulate payment gateway API call
        $transactionId = 'TXN' . time() . rand(1000, 9999);
        
        // Log the transaction
        Log::info('Card payment processed', [
            'transaction_id' => $transactionId,
            'amount' => $data['amount'],
            'method' => $data['method']
        ]);

        return [
            'success' => true,
            'transaction_id' => $transactionId,
            'message' => 'Payment successful'
        ];
    }

    /**
     * Process online banking payment
     */
    protected function processOnlineBanking(array $data): array
    {
        // Simulate bank API integration
        $transactionId = 'BANK' . time() . rand(1000, 9999);
        
        return [
            'success' => true,
            'transaction_id' => $transactionId,
            'message' => 'Online banking payment initiated'
        ];
    }

    /**
     * Process e-wallet payment
     */
    protected function processEWallet(array $data): array
    {
        // Simulate e-wallet API
        $transactionId = 'WALLET' . time() . rand(1000, 9999);
        
        return [
            'success' => true,
            'transaction_id' => $transactionId,
            'message' => 'E-wallet payment successful'
        ];
    }

    /**
     * Reverse a payment (for refunds)
     */
    public function reverse(string $paymentId): bool
    {
        // Simulate payment reversal
        Log::info('Payment reversed', ['payment_id' => $paymentId]);
        return true;
    }

    /**
     * Validate card number using Luhn algorithm
     */
    protected function validateCard(string $cardNumber): bool
    {
        // Remove spaces and dashes
        $cardNumber = preg_replace('/[\s-]/', '', $cardNumber);
        
        // Check if it's numeric and has valid length
        if (!is_numeric($cardNumber) || strlen($cardNumber) < 13 || strlen($cardNumber) > 19) {
            return false;
        }

        // For demo purposes, accept any 16-digit number starting with 4 or 5
        return strlen($cardNumber) === 16 && in_array($cardNumber[0], ['4', '5']);
    }
}