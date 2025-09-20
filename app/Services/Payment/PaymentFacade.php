<?php

namespace App\Services\Payment;

use App\Models\Payment;
use App\Models\Refund;
use DB;
use Log;

/**
 * FACADE DESIGN PATTERN
 * Simplifies complex payment operations
 */
class PaymentFacade
{
    private $cardGateway;
    private $paypalGateway;
    private $walletGateway;

    public function __construct()
    {
        $this->cardGateway = new CardPaymentGateway();
        $this->paypalGateway = new PayPalGateway();
        $this->walletGateway = new WalletGateway();
    }

    public function processPayment($method, $amount, $details)
    {
        try {
            // Select gateway based on method
            switch($method) {
                case 'card':
                    $gateway = $this->cardGateway;
                    break;
                case 'paypal':
                    $gateway = $this->paypalGateway;
                    break;
                case 'wallet':
                    $gateway = $this->walletGateway;
                    break;
                default:
                    throw new \Exception('Invalid payment method');
            }

            // Process through selected gateway
            return $gateway->process($amount, $details);
            
        } catch (\Exception $e) {
            Log::error('Payment error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function processRefund($paymentId, $amount, $reason)
    {
        try {
            $payment = Payment::find($paymentId);
            if (!$payment) {
                throw new \Exception('Payment not found');
            }

            if ($amount > $payment->amount) {
                throw new \Exception('Refund exceeds payment amount');
            }

            // Create refund record
            $refund = new Refund();
            $refund->payment_id = $paymentId;
            $refund->package_id = $payment->package_id;
            $refund->user_id = $payment->user_id;
            $refund->refund_amount = $amount;
            $refund->reason = $reason;
            $refund->status = 'pending';
            $refund->save();

            return ['success' => true, 'refund_id' => $refund->refund_id];
            
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}