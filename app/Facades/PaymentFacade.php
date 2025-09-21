<?php

namespace App\Facades;

use App\Services\Payment\PaymentProcessor;
use App\Services\Payment\InvoiceGenerator;
use App\Services\Payment\RefundManager;
use App\Services\Payment\BillingHistoryService;
use App\Services\Payment\PaymentReportService;
use App\Models\Payment;
use App\Models\Package;
use App\Models\Invoice;
use App\Models\Refund;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * PaymentFacade - Implements Facade Design Pattern
 */
class PaymentFacade
{
    protected PaymentProcessor $paymentProcessor;
    protected InvoiceGenerator $invoiceGenerator;
    protected RefundManager $refundManager;
    protected BillingHistoryService $billingHistory;
    protected PaymentReportService $reportService;

    public function __construct()
    {
        $this->paymentProcessor = new PaymentProcessor();
        $this->invoiceGenerator = new InvoiceGenerator();
        $this->refundManager = new RefundManager();
        $this->billingHistory = new BillingHistoryService();
        $this->reportService = new PaymentReportService();
    }

    /**
     * Process a payment for a package delivery
     * Facade method that coordinates multiple subsystems
     */
    public function processPayment(string $packageId, array $paymentData): array
    {
        DB::beginTransaction();
        
        try {
            // Step 1: Validate package and calculate cost
            $package = Package::findOrFail($packageId);
            $amount = $this->calculateTotalCost($package);
            
            // Step 2: Process payment through payment processor
            $paymentResult = $this->paymentProcessor->process([
                'amount' => $amount,
                'method' => $paymentData['payment_method'],
                'card_number' => $paymentData['card_number'] ?? null,
                'customer_id' => $package->user_id,
                'package_id' => $packageId
            ]);
            
            if (!$paymentResult['success']) {
                throw new \Exception($paymentResult['message']);
            }
            
            // Step 3: Create payment record
            $payment = Payment::create([
                'payment_id' => $this->generatePaymentId(),
                'package_id' => $packageId,
                'user_id' => $package->user_id,
                'amount' => $amount,
                'payment_method' => $paymentData['payment_method'],
                'transaction_id' => $paymentResult['transaction_id'],
                'status' => 'completed',
                'payment_date' => now()
            ]);
            
            // Step 4: Generate invoice automatically
            $invoice = $this->invoiceGenerator->generate($payment);
            
            // Step 5: Update package payment status
            $package->payment_status = 'paid';
            $package->save();
            
            // Step 6: Record in billing history
            $this->billingHistory->record($payment, $invoice);
            
            DB::commit();
            
            return [
                'success' => true,
                'payment_id' => $payment->payment_id,
                'invoice_id' => $invoice->invoice_id,
                'amount' => $amount,
                'message' => 'Payment processed successfully'
            ];
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Payment processing failed', [
                'package_id' => $packageId,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Payment failed: ' . $e->getMessage()
            ];
        }
    }

    /**
    * Integration with Package Service
    */
    public function integrateWithPackageService(string $packageId, string $paymentId): bool
    {
        try {
            // Use PackageService to mark package as paid
            $packageService = app(PackageService::class);
            
            return $packageService->markAsPaid($packageId, $paymentId);
            
        } catch (\Exception $e) {
            Log::error('Failed to integrate payment with package service', [
                'package_id' => $packageId,
                'payment_id' => $paymentId,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Get payment status for a package (consumed by Package Module)
     */
    public function getPackagePaymentStatus(string $packageId): array
    {
        $payment = Payment::where('package_id', $packageId)->first();
        
        if (!$payment) {
            return [
                'has_payment' => false,
                'payment_status' => 'unpaid',
                'payment_required' => true
            ];
        }
        
        return [
            'has_payment' => true,
            'payment_id' => $payment->payment_id,
            'payment_status' => $payment->status,
            'amount' => $payment->amount,
            'payment_date' => $payment->payment_date,
            'payment_method' => $payment->payment_method,
            'can_refund' => $payment->is_refundable,
            'payment_required' => false
        ];
    }

    /**
     * Generate payment URL for a package (consumed by Package Module)
     */
    public function generatePaymentUrl(string $packageId): string
    {
        return url("/customer/payment/package/{$packageId}");
    }

    /**
     * Check if refund is available for package (consumed by Package Module)
     */
    public function isRefundAvailable(string $packageId): bool
    {
        $payment = Payment::where('package_id', $packageId)
                        ->where('status', 'completed')
                        ->first();
        
        if (!$payment) {
            return false;
        }
        
        return $this->refundManager->isRefundable($payment);
    }

    /**
     * Request a refund for a payment
     */
    public function requestRefund(string $paymentId, string $reason): array
    {
        try {
            $payment = Payment::findOrFail($paymentId);
            
            // Check if refund is allowed
            if (!$this->refundManager->isRefundable($payment)) {
                return [
                    'success' => false,
                    'message' => 'This payment is not eligible for refund'
                ];
            }
            
            // Create refund request
            $refund = $this->refundManager->createRefundRequest($payment, $reason);
            
            return [
                'success' => true,
                'refund_id' => $refund->refund_id,
                'status' => $refund->status,
                'message' => 'Refund request submitted successfully'
            ];
            
        } catch (\Exception $e) {
            Log::error('Refund request failed', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Refund request failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Process refund (Admin action)
     */
    public function processRefund(string $refundId, string $action, string $adminId): array
    {
        DB::beginTransaction();
        
        try {
            $refund = Refund::findOrFail($refundId);
            
            if ($action === 'approve') {
                // Process the actual refund
                $result = $this->refundManager->approveRefund($refund, $adminId);
                
                // Reverse the payment
                if ($result['success']) {
                    $this->paymentProcessor->reverse($refund->payment_id);
                    
                    // Update payment status
                    $payment = Payment::find($refund->payment_id);
                    $payment->status = 'refunded';
                    $payment->save();

                    // Update package status to cancelled
                    $package = Package::find($payment->package_id);
                    $package->payment_status = 'refunded';
                    $package->package_status = 'cancelled';  // ADD THIS LINE
                    $package->save();
                }
            } else {
                $result = $this->refundManager->rejectRefund($refund, $adminId);
            }
            
            DB::commit();
            
            return $result;
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Refund processing failed', [
                'refund_id' => $refundId,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Refund processing failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get customer billing history
     */
    public function getCustomerBillingHistory(string $userId, array $filters = []): array
    {
        return $this->billingHistory->getForCustomer($userId, $filters);
    }

    /**
     * Get all payments (Admin)
     */
    public function getAllPayments(array $filters = []): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = Payment::with(['user', 'package']);
        
        // Apply filters
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (!empty($filters['customer_id'])) {
            $query->where('user_id', $filters['customer_id']);
        }
        
        if (!empty($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }
        
        if (!empty($filters['date_from'])) {
            $query->whereDate('payment_date', '>=', $filters['date_from']);
        }
        
        if (!empty($filters['date_to'])) {
            $query->whereDate('payment_date', '<=', $filters['date_to']);
        }
        
        return $query->orderBy('payment_date', 'desc')->paginate(20);
    }

    /**
     * Generate financial report
     */
    public function generateFinancialReport(string $type, array $params = []): array
    {
        switch ($type) {
            case 'revenue_summary':
                return $this->reportService->getRevenueSummary($params);
                
            case 'payment_methods':
                return $this->reportService->getPaymentMethodsBreakdown($params);
                
            case 'refund_analysis':
                return $this->reportService->getRefundAnalysis($params);
                
            case 'unpaid_transactions':
                return $this->reportService->getUnpaidTransactions();
                
            case 'customer_spending':
                return $this->reportService->getCustomerSpendingReport($params);
                
            default:
                return $this->reportService->getGeneralReport($params);
        }
    }

    /**
     * Generate and download invoice
     */
    public function generateInvoice(string $paymentId): array
    {
        try {
            $payment = Payment::with(['user', 'package'])->findOrFail($paymentId);
            
            // Check if invoice already exists
            $invoice = Invoice::where('payment_id', $paymentId)->first();
            
            if (!$invoice) {
                $invoice = $this->invoiceGenerator->generate($payment);
            }
            
            // Generate PDF
            $pdfPath = $this->invoiceGenerator->generatePDF($invoice);
            
            return [
                'success' => true,
                'invoice_id' => $invoice->invoice_id,
                'pdf_path' => $pdfPath,
                'invoice_data' => $invoice
            ];
            
        } catch (\Exception $e) {
            Log::error('Invoice generation failed', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Invoice generation failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Email invoice to customer
     */
    public function emailInvoice(string $invoiceId): bool
    {
        try {
            $invoice = Invoice::with(['payment.user'])->findOrFail($invoiceId);
            return $this->invoiceGenerator->emailToCustomer($invoice);
        } catch (\Exception $e) {
            Log::error('Failed to email invoice', [
                'invoice_id' => $invoiceId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Calculate total cost including taxes and fees
     */
    protected function calculateTotalCost(Package $package): float
    {
        $baseCost = $package->shipping_cost;
        $tax = $baseCost * 0.06; // 6% tax
        $serviceFee = 2.00; // Fixed service fee
        
        return round($baseCost + $tax + $serviceFee, 2);
    }

    /**
     * Generate unique payment ID
     */
    protected function generatePaymentId(): string
    {
        do {
            $id = 'PAY' . date('Ymd') . rand(1000, 9999);
        } while (Payment::where('payment_id', $id)->exists());
        
        return $id;
    }

    /**
     * Get payment statistics for dashboard
     */
    public function getPaymentStatistics(): array
    {
        return [
            'total_revenue' => Payment::where('status', 'completed')->sum('amount'),
            'pending_payments' => Payment::where('status', 'pending')->count(),
            'completed_today' => Payment::whereDate('payment_date', today())
                                       ->where('status', 'completed')
                                       ->sum('amount'),
            'refunds_pending' => Refund::where('status', 'pending')->count(),
            'average_transaction' => Payment::where('status', 'completed')->avg('amount'),
            'most_used_method' => Payment::select('payment_method')
                                        ->selectRaw('count(*) as count')
                                        ->groupBy('payment_method')
                                        ->orderByDesc('count')
                                        ->first()
        ];
    }
}