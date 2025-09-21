<?php

namespace App\Services\Payment;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Refund;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvoiceMail;

/**
 * InvoiceGenerator - Subsystem for generating invoices
 */
class InvoiceGenerator
{
    /**
     * Generate invoice for a payment
     */
    public function generate(Payment $payment): Invoice
    {
        $invoiceNumber = $this->generateInvoiceNumber();
        
        $invoice = Invoice::create([
            'invoice_id' => $this->generateInvoiceId(),
            'invoice_number' => $invoiceNumber,
            'payment_id' => $payment->payment_id,
            'user_id' => $payment->user_id,
            'package_id' => $payment->package_id,
            'amount' => $payment->amount,
            'tax_amount' => $payment->amount * 0.06,
            'total_amount' => $payment->amount * 1.06,
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'status' => 'issued',
            'invoice_data' => $this->prepareInvoiceData($payment)
        ]);
        
        return $invoice;
    }
    
    /**
     * Generate PDF version of invoice
     */
    public function generatePDF(Invoice $invoice): string
    {
        $data = [
            'invoice' => $invoice,
            'payment' => $invoice->payment,
            'package' => $invoice->package,
            'user' => $invoice->user
        ];
        
        $pdf = Pdf::loadView('invoices.pdf', $data);
        
        $filename = "invoice_{$invoice->invoice_number}.pdf";
        $path = storage_path("app/public/invoices/{$filename}");
        
        // Ensure directory exists
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }
        
        $pdf->save($path);
        
        return $path;
    }
    
    /**
     * Email invoice to customer
     */
    public function emailToCustomer(Invoice $invoice): bool
    {
        try {
            $pdfPath = $this->generatePDF($invoice);
            
            Mail::to($invoice->user->email)
                ->send(new InvoiceMail($invoice, $pdfPath));
                
            $invoice->update(['sent_at' => now()]);
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to email invoice', [
                'invoice_id' => $invoice->invoice_id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    protected function generateInvoiceId(): string
    {
        do {
            $id = 'INV' . date('Ymd') . rand(1000, 9999);
        } while (Invoice::where('invoice_id', $id)->exists());
        
        return $id;
    }
    
    protected function generateInvoiceNumber(): string
    {
        $year = date('Y');
        $month = date('m');
        
        $lastInvoice = Invoice::whereYear('created_at', $year)
                              ->whereMonth('created_at', $month)
                              ->orderBy('invoice_number', 'desc')
                              ->first();
        
        if ($lastInvoice) {
            $lastNumber = intval(substr($lastInvoice->invoice_number, -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return sprintf('INV-%s%s-%04d', $year, $month, $newNumber);
    }
    
    protected function prepareInvoiceData(Payment $payment): array
    {
        return [
            'billing_address' => $payment->user->address ?? '',
            'items' => [
                [
                    'description' => "Delivery Service - Package #{$payment->package_id}",
                    'quantity' => 1,
                    'unit_price' => $payment->amount,
                    'total' => $payment->amount
                ]
            ],
            'subtotal' => $payment->amount,
            'tax_rate' => 0.06,
            'tax_amount' => $payment->amount * 0.06,
            'total' => $payment->amount * 1.06
        ];
    }
}

/**
 * RefundManager - Subsystem for managing refunds
 */
class RefundManager
{
    protected int $refundWindowDays = 7; // Refund allowed within 7 days
    
    /**
     * Check if payment is refundable
     */
    public function isRefundable(Payment $payment): bool
    {
        // Check if already refunded
        if ($payment->status === 'refunded') {
            return false;
        }
        
        // Check if within refund window
        $daysSincePayment = $payment->payment_date->diffInDays(now());
        if ($daysSincePayment > $this->refundWindowDays) {
            return false;
        }
        
        // Check package status - can't refund if delivered
        $package = $payment->package;
        if ($package && in_array(strtolower($package->package_status), ['delivered', 'in_transit'])) {
            return false;
        }
        
        // Check if refund already exists
        $existingRefund = Refund::where('payment_id', $payment->payment_id)
                                ->whereIn('status', ['pending', 'approved'])
                                ->exists();
        
        return !$existingRefund;
    }
    
    /**
     * Create refund request
     */
    public function createRefundRequest(Payment $payment, string $reason): Refund
    {
        $refund = Refund::create([
            'refund_id' => $this->generateRefundId(),
            'payment_id' => $payment->payment_id,
            'user_id' => $payment->user_id,
            'amount' => $payment->amount,
            'reason' => $reason,
            'status' => 'pending',
            'requested_at' => now()
        ]);
        
        // Notify admin about new refund request
        $this->notifyAdminAboutRefund($refund);
        
        return $refund;
    }
    
    /**
     * Approve refund request
     */
    public function approveRefund(Refund $refund, string $adminId): array
    {
        if ($refund->status !== 'pending') {
            return [
                'success' => false,
                'message' => 'Refund has already been processed'
            ];
        }
        
        $refund->update([
            'status' => 'approved',
            'processed_by' => $adminId,
            'processed_at' => now(),
            'admin_notes' => 'Refund approved'
        ]);
        
        // Notify customer about refund approval
        $this->notifyCustomerAboutRefund($refund, 'approved');
        
        return [
            'success' => true,
            'message' => 'Refund approved successfully'
        ];
    }
    
    /**
     * Reject refund request
     */
    public function rejectRefund(Refund $refund, string $adminId, string $reason = ''): array
    {
        if ($refund->status !== 'pending') {
            return [
                'success' => false,
                'message' => 'Refund has already been processed'
            ];
        }
        
        $refund->update([
            'status' => 'rejected',
            'processed_by' => $adminId,
            'processed_at' => now(),
            'admin_notes' => $reason ?: 'Refund request does not meet criteria'
        ]);
        
        // Notify customer about refund rejection
        $this->notifyCustomerAboutRefund($refund, 'rejected');
        
        return [
            'success' => true,
            'message' => 'Refund rejected'
        ];
    }
    
    protected function generateRefundId(): string
    {
        do {
            $id = 'REF' . date('Ymd') . rand(1000, 9999);
        } while (Refund::where('refund_id', $id)->exists());
        
        return $id;
    }
    
    protected function notifyAdminAboutRefund(Refund $refund): void
    {
        // Implementation for admin notification
        \Log::info('New refund request', ['refund_id' => $refund->refund_id]);
    }
    
    protected function notifyCustomerAboutRefund(Refund $refund, string $status): void
    {
        // Implementation for customer notification
        \Log::info("Refund {$status}", ['refund_id' => $refund->refund_id]);
    }
}

/**
 * BillingHistoryService - Subsystem for billing history
 */
class BillingHistoryService
{
    /**
     * Record billing transaction
     */
    public function record(Payment $payment, Invoice $invoice): void
    {
        // Log billing history (could be separate table)
        \Log::info('Billing recorded', [
            'payment_id' => $payment->payment_id,
            'invoice_id' => $invoice->invoice_id
        ]);
    }
    
    /**
     * Get customer billing history
     */
    public function getForCustomer(string $userId, array $filters = []): array
    {
        $query = Payment::where('user_id', $userId)
                        ->with(['package', 'invoice', 'refund']);
        
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (!empty($filters['date_from'])) {
            $query->whereDate('payment_date', '>=', $filters['date_from']);
        }
        
        if (!empty($filters['date_to'])) {
            $query->whereDate('payment_date', '<=', $filters['date_to']);
        }
        
        $payments = $query->orderBy('payment_date', 'desc')->get();
        
        return $payments->map(function ($payment) {
            return [
                'payment_id' => $payment->payment_id,
                'package_id' => $payment->package_id,
                'amount' => $payment->amount,
                'status' => $payment->status,
                'payment_date' => $payment->payment_date,
                'payment_method' => $payment->payment_method,
                'invoice_available' => $payment->invoice ? true : false,
                'refund_status' => $payment->refund ? $payment->refund->status : null
            ];
        })->toArray();
    }
}

/**
 * PaymentReportService - Subsystem for generating reports
 */
class PaymentReportService
{
    public function getRevenueSummary(array $params): array
    {
        $startDate = $params['start_date'] ?? now()->startOfMonth();
        $endDate = $params['end_date'] ?? now()->endOfMonth();
        
        return [
            'total_revenue' => Payment::whereBetween('payment_date', [$startDate, $endDate])
                                      ->where('status', 'completed')
                                      ->sum('amount'),
            'total_refunded' => Refund::whereBetween('processed_at', [$startDate, $endDate])
                                      ->where('status', 'approved')
                                      ->sum('amount'),
            'net_revenue' => Payment::whereBetween('payment_date', [$startDate, $endDate])
                                    ->where('status', 'completed')
                                    ->sum('amount') - 
                           Refund::whereBetween('processed_at', [$startDate, $endDate])
                                 ->where('status', 'approved')
                                 ->sum('amount'),
            'transaction_count' => Payment::whereBetween('payment_date', [$startDate, $endDate])
                                          ->count()
        ];
    }
    
    public function getPaymentMethodsBreakdown(array $params): array
    {
        return Payment::select('payment_method')
                      ->selectRaw('COUNT(*) as count')
                      ->selectRaw('SUM(amount) as total')
                      ->where('status', 'completed')
                      ->groupBy('payment_method')
                      ->get()
                      ->toArray();
    }
    
    public function getRefundAnalysis(array $params): array
    {
        return [
            'total_refunds' => Refund::count(),
            'approved_refunds' => Refund::where('status', 'approved')->count(),
            'rejected_refunds' => Refund::where('status', 'rejected')->count(),
            'pending_refunds' => Refund::where('status', 'pending')->count(),
            'total_refunded_amount' => Refund::where('status', 'approved')->sum('amount'),
            'average_refund_amount' => Refund::where('status', 'approved')->avg('amount')
        ];
    }
    
    public function getUnpaidTransactions(): array
    {
        return Payment::where('status', 'pending')
                      ->with(['user', 'package'])
                      ->get()
                      ->toArray();
    }
    
    public function getCustomerSpendingReport(array $params): array
    {
        return Payment::select('user_id')
                      ->selectRaw('COUNT(*) as transaction_count')
                      ->selectRaw('SUM(amount) as total_spent')
                      ->selectRaw('AVG(amount) as average_transaction')
                      ->where('status', 'completed')
                      ->groupBy('user_id')
                      ->orderByDesc('total_spent')
                      ->limit(20)
                      ->with('user')
                      ->get()
                      ->toArray();
    }
    
    public function getGeneralReport(array $params): array
    {
        return [
            'revenue' => $this->getRevenueSummary($params),
            'payment_methods' => $this->getPaymentMethodsBreakdown($params),
            'refunds' => $this->getRefundAnalysis($params)
        ];
    }
}