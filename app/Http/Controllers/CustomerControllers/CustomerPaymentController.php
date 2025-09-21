<?php

namespace App\Http\Controllers\CustomerControllers;

use App\Http\Controllers\Controller;
use App\Facades\PaymentFacade;
use App\Models\Package;
use App\Models\Payment;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class CustomerPaymentController extends Controller
{
    protected PaymentFacade $paymentFacade;
    
    public function __construct()
    {
        $this->paymentFacade = new PaymentFacade();
    }
    
    /**
     * Show payment page for a package
     */
    public function showPaymentPage(string $packageId)
    {
        $package = Package::where('package_id', $packageId)
                         ->where('user_id', Auth::id())
                         ->firstOrFail();
        
        // Check if already paid
        if ($package->payment_status === 'paid') {
            return redirect()->route('customer.billing.history')
                           ->with('info', 'This package has already been paid.');
        }
        
        // Calculate costs
        $baseCost = $package->shipping_cost;
        $tax = $baseCost * 0.06; // 6% GST
        $serviceFee = 2.00;
        $totalCost = $baseCost + $tax + $serviceFee;
        
        $paymentMethods = [
            'credit_card' => 'Credit Card',
            'debit_card' => 'Debit Card',
            'online_banking' => 'Online Banking',
            'e_wallet' => 'E-Wallet'
        ];
        
        return view('customer.payment.make-payment', compact(
            'package',
            'baseCost',
            'tax',
            'serviceFee',
            'totalCost',
            'paymentMethods'
        ));
    }
    
    /**
     * Process payment
     */
    public function processPayment(Request $request, string $packageId)
    {
        $validated = $request->validate([
            'payment_method' => 'required|in:credit_card,debit_card,online_banking,e_wallet',
            'card_number' => 'required_if:payment_method,credit_card,debit_card|nullable|digits:16',
            'card_name' => 'required_if:payment_method,credit_card,debit_card|nullable|string',
            'card_expiry' => 'required_if:payment_method,credit_card,debit_card|nullable|regex:/^\d{2}\/\d{2}$/',
            'card_cvv' => 'required_if:payment_method,credit_card,debit_card|nullable|digits:3',
            'bank_name' => 'required_if:payment_method,online_banking|nullable|string',
            'wallet_provider' => 'required_if:payment_method,e_wallet|nullable|string'
        ]);
        
        try {
            // Verify package ownership
            $package = Package::where('package_id', $packageId)
                             ->where('user_id', Auth::id())
                             ->firstOrFail();
            
            if ($package->payment_status === 'paid') {
                return back()->with('error', 'This package has already been paid.');
            }
            
            // Process payment through facade
            $result = $this->paymentFacade->processPayment($packageId, $validated);
            
            if ($result['success']) {
                return redirect()->route('customer.payment.success', $result['payment_id'])
                               ->with('success', 'Payment successful! Your package will be processed shortly.');
            } else {
                return back()->with('error', $result['message'])->withInput();
            }
            
        } catch (\Exception $e) {
            Log::error('Payment processing error', [
                'user_id' => Auth::id(),
                'package_id' => $packageId,
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', 'Payment processing failed. Please try again.')->withInput();
        }
    }
    
    /**
     * Payment success page
     */
    public function paymentSuccess(string $paymentId)
    {
        $payment = Payment::where('payment_id', $paymentId)
                         ->where('user_id', Auth::id())
                         ->with(['package', 'invoice'])
                         ->firstOrFail();
        
        return view('customer.payment.success', compact('payment'));
    }
    
    /**
     * View billing history
     */
    public function billingHistory(Request $request)
    {
        $filters = $request->only(['status', 'date_from', 'date_to']);
        
        $history = $this->paymentFacade->getCustomerBillingHistory(Auth::id(), $filters);
        
        $payments = Payment::where('user_id', Auth::id())
                          ->with(['package', 'invoice', 'refund'])
                          ->when($request->status, function($query, $status) {
                              return $query->where('status', $status);
                          })
                          ->when($request->date_from, function($query, $date) {
                              return $query->whereDate('payment_date', '>=', $date);
                          })
                          ->when($request->date_to, function($query, $date) {
                              return $query->whereDate('payment_date', '<=', $date);
                          })
                          ->orderBy('payment_date', 'desc')
                          ->paginate(15);
        
        return view('customer.payment.billing-history', compact('payments'));
    }
    
    /**
     * Download invoice/receipt
     */
    public function downloadInvoice(string $paymentId)
    {
        $payment = Payment::where('payment_id', $paymentId)
                         ->where('user_id', Auth::id())
                         ->firstOrFail();
        
        $result = $this->paymentFacade->generateInvoice($paymentId);
        
        if ($result['success']) {
            return response()->download($result['pdf_path']);
        }
        
        return back()->with('error', 'Unable to generate invoice. Please try again.');
    }
    
    /**
     * Request refund page
     */
    public function showRefundRequest(string $paymentId)
    {
        $payment = Payment::where('payment_id', $paymentId)
                         ->where('user_id', Auth::id())
                         ->with(['package', 'refund'])
                         ->firstOrFail();
        
        // Check if refund already exists
        if ($payment->refund) {
            return redirect()->route('customer.refund.status', $payment->refund->refund_id);
        }
        
        // Check if eligible for refund
        if (!$payment->is_refundable) {
            return back()->with('error', 'This payment is not eligible for refund.');
        }
        
        $refundReasons = [
            'Package cancelled' => 'Package was cancelled before delivery',
            'Duplicate payment' => 'Payment was made twice by mistake',
            'Service not provided' => 'Delivery service was not provided',
            'Wrong amount charged' => 'Incorrect amount was charged',
            'Other' => 'Other reason (please specify)'
        ];
        
        return view('customer.payment.refund-request', compact('payment', 'refundReasons'));
    }
    
    /**
     * Submit refund request
     */
    public function submitRefund(Request $request, string $paymentId)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
            'additional_info' => 'nullable|string|max:1000'
        ]);
        
        $payment = Payment::where('payment_id', $paymentId)
                         ->where('user_id', Auth::id())
                         ->firstOrFail();
        
        if ($payment->refund) {
            return back()->with('error', 'A refund request already exists for this payment.');
        }
        
        $reason = $validated['reason'];
        if (!empty($validated['additional_info'])) {
            $reason .= "\n\nAdditional Information: " . $validated['additional_info'];
        }
        
        $result = $this->paymentFacade->requestRefund($paymentId, $reason);
        
        if ($result['success']) {
            return redirect()->route('customer.refund.status', $result['refund_id'])
                           ->with('success', 'Refund request submitted successfully. We will review it within 2-3 business days.');
        }
        
        return back()->with('error', $result['message']);
    }
    
    /**
     * View refund status
     */
    public function refundStatus(string $refundId)
    {
        $refund = \App\Models\Refund::where('refund_id', $refundId)
                                    ->where('user_id', Auth::id())
                                    ->with(['payment.package'])
                                    ->firstOrFail();
        
        return view('customer.payment.refund-status', compact('refund'));
    }
    
    /**
     * Generate receipt PDF
     */
    public function generateReceipt(string $paymentId)
    {
        $payment = Payment::where('payment_id', $paymentId)
                         ->where('user_id', Auth::id())
                         ->with(['package', 'user'])
                         ->firstOrFail();
        
        $pdf = Pdf::loadView('customer.payment.receipt', compact('payment'));
        
        return $pdf->download("receipt_{$payment->payment_id}.pdf");
    }
}