<?php

namespace App\Http\Controllers\CustomerControllers;

use App\Http\Controllers\Controller;
use App\Facades\PaymentFacade;
use App\Models\Package;
use App\Models\Payment;
use App\Models\Refund;
use App\Services\PackageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Barryvdh\DomPDF\Facade\Pdf;

class CustomerPaymentController extends Controller
{
    protected PaymentFacade $paymentFacade;
    protected PackageService $packageService;
    
    public function __construct(PaymentFacade $paymentFacade, PackageService $packageService)
    {
        $this->paymentFacade = $paymentFacade;
        $this->packageService = $packageService;
    }
    
    /**
     * Process payment for an existing package
     * This method handles the route: /customer/payment/package/{packageId}
     */
    public function processPayment(Request $request, string $packageId)
{
    // Get payment method first
    $paymentMethod = $request->input('payment_method');
    
    // Build validation rules based on payment method
    $rules = [
        'payment_method' => 'required|in:credit_card,debit_card,online_banking,e_wallet',
    ];
    
    // Only require card details for card payments
    if (in_array($paymentMethod, ['credit_card', 'debit_card'])) {
        $rules['card_number'] = ['required', 'string', function ($attribute, $value, $fail) {
            $digitsOnly = preg_replace('/\D/', '', $value);
            if (strlen($digitsOnly) !== 16) {
                $fail('The card number must be exactly 16 digits.');
            }
        }];
        $rules['card_name'] = 'required|string|min:2';
        $rules['card_expiry'] = 'required|regex:/^\d{2}\/\d{2}$/';
        $rules['card_cvv'] = 'required|digits:3';
    }
    
    $validated = $request->validate($rules);
    
    try {
        // Get the package
        $package = Package::where('package_id', $packageId)
                        ->where('user_id', Auth::id())
                        ->firstOrFail();
        
        // Check if package is already paid
        if ($package->payment_status === 'paid') {
            return redirect()->route('customer.packages.show', $packageId)
                          ->with('info', 'This package has already been paid for.');
        }
        
        // Just use shipping cost as total (no tax or fees)
        $totalCost = $package->shipping_cost;
        
        // Generate payment IDs
        $paymentId = 'PAY' . time() . rand(1000, 9999);
        $transactionId = 'TXN' . time() . rand(1000, 9999);
        
        // Create payment record
        $paymentData = [
            'payment_id' => $paymentId,
            'package_id' => $package->package_id,
            'user_id' => Auth::id(),
            'amount' => $totalCost,
            'payment_method' => $validated['payment_method'],
            'transaction_id' => $transactionId,
            'status' => 'completed',
            'payment_date' => now()
        ];
        
        // Add payment method specific notes
        if (in_array($paymentMethod, ['credit_card', 'debit_card'])) {
            $cardNumber = preg_replace('/\s/', '', $validated['card_number']);
            $lastFour = substr($cardNumber, -4);
            $paymentData['notes'] = "Card ending in {$lastFour}";
        } elseif ($paymentMethod === 'online_banking') {
            $paymentData['notes'] = "Online Banking Payment";
        } elseif ($paymentMethod === 'e_wallet') {
            $paymentData['notes'] = "E-Wallet Payment";
        }
        
        $payment = Payment::create($paymentData);
        
        // Update package with payment info
        $package->update([
            'payment_status' => 'paid',
            'payment_id' => $paymentId,
            'package_status' => 'processing'
        ]);
        
        Log::info('Payment processed successfully', [
            'payment_id' => $paymentId,
            'package_id' => $packageId,
            'user_id' => Auth::id(),
            'payment_method' => $validated['payment_method'],
            'amount' => $totalCost
        ]);
        
        return redirect()->route('customer.payment.success', $paymentId)
                       ->with('success', 'Payment successful! Your package will be processed shortly.');
        
    } catch (\Exception $e) {
        Log::error('Payment processing error', [
            'package_id' => $packageId,
            'user_id' => Auth::id(),
            'error' => $e->getMessage()
        ]);
        
        return back()->with('error', 'Payment processing failed. Please try again.')->withInput();
    }
}
    
    /**
     * Store package data in session and redirect to payment
     */
    public function createAndPay(Request $request)
    {
        // Validate package data
        $validated = $request->validate([
            'package_contents' => 'required|string|max:500',
            'package_weight' => 'required|numeric|min:0.1|max:50',
            'package_dimensions' => 'nullable|string|max:50',
            'priority' => 'required|in:standard,express,urgent',
            'sender_address' => 'required|string|max:500',
            'recipient_address' => 'required|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ]);
        
        // Calculate cost using same logic as Package model
        $baseCost = 10.00;
        $weightCost = $validated['package_weight'] * 2.5;
        $priorityMultiplier = match($validated['priority']) {
            'express' => 1.5,
            'urgent' => 2.0,
            default => 1.0
        };
        $shippingCost = ($baseCost + $weightCost) * $priorityMultiplier;
        
        // Store in session for payment processing
        Session::put('pending_package', array_merge($validated, [
            'user_id' => Auth::id(),
            'shipping_cost' => $shippingCost
        ]));
        
        // Redirect to payment with session data
        return redirect()->route('customer.payment.showSessionPayment');
    }
    
    /**
     * Show payment page using session data
     */
    public function showSessionPayment()
{
    $packageData = Session::get('pending_package');
    
    if (!$packageData) {
        return redirect()->route('customer.packages.create')
                       ->with('error', 'No package data found. Please create your package again.');
    }
    
    // Just use the base cost as the total
    $baseCost = $packageData['shipping_cost'];
    
    $paymentMethods = [
        'credit_card' => 'Credit Card',
        'debit_card' => 'Debit Card',
        'online_banking' => 'Online Banking',
        'e_wallet' => 'E-Wallet'
    ];
    
    return view('customer.payment.session-payment', compact(
        'packageData',
        'baseCost',
        'paymentMethods'
    ));
}
    
    /**
     * Process payment and create package
     */
    public function processSessionPayment(Request $request)
{
    $packageData = Session::get('pending_package');
    
    if (!$packageData) {
        return redirect()->route('customer.packages.create')
                       ->with('error', 'Session expired. Please create your package again.');
    }
    
    // Get payment method first
    $paymentMethod = $request->input('payment_method');
    
    // Build validation rules based on payment method
    $rules = [
        'payment_method' => 'required|in:credit_card,debit_card,online_banking,e_wallet',
    ];
    
    // Only require card details for card payments
    if (in_array($paymentMethod, ['credit_card', 'debit_card'])) {
        $rules['card_number'] = ['required', 'string', function ($attribute, $value, $fail) {
            $digitsOnly = preg_replace('/\D/', '', $value);
            if (strlen($digitsOnly) !== 16) {
                $fail('The card number must be exactly 16 digits.');
            }
        }];
        $rules['card_name'] = 'required|string|min:2';
        $rules['card_expiry'] = 'required|regex:/^\d{2}\/\d{2}$/';
        $rules['card_cvv'] = 'required|digits:3';
    }
    
    $validated = $request->validate($rules);
    
    try {
        // Just use base cost as total
        $totalCost = $packageData['shipping_cost'];
        
        // For testing, simulate successful payment
        $paymentResult = [
            'success' => true,
            'payment_id' => 'PAY' . time() . rand(1000, 9999),
            'transaction_id' => 'TXN' . time() . rand(1000, 9999)
        ];
        
        if ($paymentResult['success']) {
            // Create the package after successful payment
            $package = $this->packageService->createPackage($packageData);
            
            // Prepare payment data
            $paymentData = [
                'payment_id' => $paymentResult['payment_id'],
                'package_id' => $package->package_id,
                'user_id' => Auth::id(),
                'amount' => $totalCost,
                'payment_method' => $validated['payment_method'],
                'transaction_id' => $paymentResult['transaction_id'],
                'status' => 'completed',
                'payment_date' => now()
            ];
            
            // Add payment method specific notes
            if (in_array($paymentMethod, ['credit_card', 'debit_card'])) {
                $cardNumber = preg_replace('/\s/', '', $validated['card_number']);
                $lastFour = substr($cardNumber, -4);
                $paymentData['notes'] = "Card ending in {$lastFour}";
            } elseif ($paymentMethod === 'online_banking') {
                $paymentData['notes'] = "Online Banking Payment";
            } elseif ($paymentMethod === 'e_wallet') {
                $paymentData['notes'] = "E-Wallet Payment";
            }
            
            // Create payment record
            $payment = Payment::create($paymentData);
            
            // Update package with payment info
            $package->update([
                'payment_status' => 'paid',
                'payment_id' => $paymentResult['payment_id'],
                'package_status' => 'processing'
            ]);
            
            // Clear session
            Session::forget('pending_package');
            
            return redirect()->route('customer.payment.success', $paymentResult['payment_id'])
                           ->with('success', 'Payment successful! Your package has been created and will be processed shortly.');
        } else {
            return back()->with('error', 'Payment failed. Please try again.')->withInput();
        }
        
    } catch (\Exception $e) {
        Log::error('Payment processing error', [
            'user_id' => Auth::id(),
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
        try {
            $payment = Payment::where('payment_id', $paymentId)
                             ->where('user_id', Auth::id())
                             ->with(['package', 'invoice'])
                             ->firstOrFail();
            
            return view('customer.payment.success', compact('payment'));
            
        } catch (\Exception $e) {
            Log::error('Error loading payment success page', [
                'payment_id' => $paymentId,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('customer.packages.index')
                           ->with('error', 'Payment record not found.');
        }
    }
    
    /**
     * View billing history
     */
    public function billingHistory(Request $request)
    {
        try {
            $filters = $request->only(['status', 'date_from', 'date_to']);
            
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
            
        } catch (\Exception $e) {
            Log::error('Error loading billing history', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('customer.dashboard')
                           ->with('error', 'Unable to load billing history.');
        }
    }
    
    /**
     * Download invoice/receipt
     */
    public function downloadInvoice(string $paymentId)
    {
        try {
            $payment = Payment::where('payment_id', $paymentId)
                             ->where('user_id', Auth::id())
                             ->firstOrFail();
            
            $result = $this->paymentFacade->generateInvoice($paymentId);
            
            if ($result['success']) {
                return response()->download($result['pdf_path']);
            }
            
            return back()->with('error', 'Unable to generate invoice. Please try again.');
            
        } catch (\Exception $e) {
            Log::error('Error generating invoice', [
                'payment_id' => $paymentId,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', 'Invoice not found or access denied.');
        }
    }
    
    /**
     * Request refund page
     */
    public function showRefundRequest(string $paymentId)
    {
        try {
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
            
        } catch (\Exception $e) {
            Log::error('Error loading refund request page', [
                'payment_id' => $paymentId,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('customer.billing.history')
                           ->with('error', 'Payment not found or access denied.');
        }
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
        
        try {
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
                Log::info('Refund request submitted', [
                    'refund_id' => $result['refund_id'],
                    'payment_id' => $paymentId,
                    'user_id' => Auth::id()
                ]);
                
                return redirect()->route('customer.refund.status', $result['refund_id'])
                               ->with('success', 'Refund request submitted successfully. We will review it within 2-3 business days.');
            }
            
            return back()->with('error', $result['message']);
            
        } catch (\Exception $e) {
            Log::error('Error submitting refund request', [
                'payment_id' => $paymentId,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', 'Unable to submit refund request. Please try again.');
        }
    }
    
    /**
     * View refund status
     */
    public function refundStatus(string $refundId)
    {
        try {
            $refund = Refund::where('refund_id', $refundId)
                           ->where('user_id', Auth::id())
                           ->with(['payment.package'])
                           ->firstOrFail();
            
            return view('customer.payment.refund-status', compact('refund'));
            
        } catch (\Exception $e) {
            Log::error('Error loading refund status', [
                'refund_id' => $refundId,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('customer.billing.history')
                           ->with('error', 'Refund not found or access denied.');
        }
    }
    
    public function showPaymentPage(string $packageId)
{
    try {
        // Get the package
        $package = Package::where('package_id', $packageId)
                        ->where('user_id', Auth::id())
                        ->firstOrFail();
        
        // Check if already paid
        if ($package->payment_status === 'paid') {
            return redirect()->route('customer.packages.show', $packageId)
                          ->with('info', 'This package has already been paid.');
        }
        
        // Just use shipping cost as total
        $baseCost = $package->shipping_cost;
        
        $paymentMethods = [
            'credit_card' => 'Credit Card',
            'debit_card' => 'Debit Card', 
            'online_banking' => 'Online Banking',
            'e_wallet' => 'E-Wallet'
        ];
        
        return view('customer.payment.payment', compact(
            'package',
            'baseCost',
            'paymentMethods'
        ));
        
    } catch (\Exception $e) {
        Log::error('Error loading payment page', [
            'package_id' => $packageId,
            'user_id' => Auth::id(),
            'error' => $e->getMessage()
        ]);
        
        return redirect()->route('customer.packages.index')
                      ->with('error', 'Package not found or access denied.');
    }
}

    /**
     * Generate receipt PDF
     */
    public function generateReceipt(string $paymentId)
{
    try {
        $payment = Payment::where('payment_id', $paymentId)
                         ->where('user_id', Auth::id())
                         ->with(['package', 'user'])
                         ->firstOrFail();
        
        // Check if PDF download is requested
        if (request()->has('download') && class_exists('\Barryvdh\DomPDF\Facade\Pdf')) {
            // If DomPDF is installed and download requested
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('customer.payment.receipt', compact('payment'));
            return $pdf->download("receipt_{$payment->payment_id}.pdf");
        }
        
        // Return printable HTML view
        return view('customer.payment.receipt', compact('payment'));
        
    } catch (\Exception $e) {
        Log::error('Error generating receipt', [
            'payment_id' => $paymentId,
            'user_id' => Auth::id(),
            'error' => $e->getMessage()
        ]);
        
        return back()->with('error', 'Unable to generate receipt. Please try again.');
    }
}
}