<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use App\Facades\PaymentFacade;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\Refund;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Admin Payment Controller - Manages payments and reports
 */
class PaymentController extends Controller
{
    protected PaymentFacade $paymentFacade;
    
    public function __construct()
    {
        $this->paymentFacade = new PaymentFacade();
    }
    
    /**
     * Display payment management dashboard
     */
    public function index(Request $request)
    {
        // Get statistics
        $statistics = $this->paymentFacade->getPaymentStatistics();
        
        // Apply filters
        $filters = $request->only(['status', 'customer_id', 'payment_method', 'date_from', 'date_to']);
        
        // Get paginated payments
        $payments = $this->paymentFacade->getAllPayments($filters);
        
        // Get payment methods for filter dropdown
        $paymentMethods = [
            'credit_card' => 'Credit Card',
            'debit_card' => 'Debit Card', 
            'online_banking' => 'Online Banking',
            'e_wallet' => 'E-Wallet'
        ];
        
        // Get statuses for filter
        $statuses = ['pending', 'completed', 'failed', 'refunded'];
        
        return view('admin.payment.index', compact(
            'payments',
            'statistics',
            'paymentMethods',
            'statuses'
        ));
    }
    
    /**
     * View payment details
     */
    public function show(string $paymentId)
    {
        $payment = Payment::with(['user', 'package', 'invoice', 'refund'])
                         ->findOrFail($paymentId);
        
        return view('admin.payment.show', compact('payment'));
    }
    
    /**
     * Generate invoice for payment
     */
    public function generateInvoice(string $paymentId)
    {
        $result = $this->paymentFacade->generateInvoice($paymentId);
        
        if ($result['success']) {
            return response()->download($result['pdf_path']);
        }
        
        return back()->with('error', 'Failed to generate invoice: ' . $result['message']);
    }
    
    /**
     * Email invoice to customer
     */
    public function emailInvoice(Request $request, string $invoiceId)
    {
        $success = $this->paymentFacade->emailInvoice($invoiceId);
        
        if ($success) {
            return back()->with('success', 'Invoice emailed successfully to customer.');
        }
        
        return back()->with('error', 'Failed to email invoice. Please try again.');
    }
    
    /**
     * Generate financial report
     */
    public function generateReport(Request $request)
    {
        $validated = $request->validate([
            'report_type' => 'required|in:revenue_summary,payment_methods,refund_analysis,unpaid_transactions,customer_spending,general',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'format' => 'required|in:view,pdf,excel'
        ]);
        
        $params = [
            'start_date' => $validated['start_date'] ?? now()->startOfMonth(),
            'end_date' => $validated['end_date'] ?? now()->endOfMonth()
        ];
        
        $reportData = $this->paymentFacade->generateFinancialReport($validated['report_type'], $params);
        
        // Handle different export formats
        switch ($validated['format']) {
            case 'pdf':
                return $this->exportReportAsPDF($reportData, $validated['report_type'], $params);
                
            case 'excel':
                return $this->exportReportAsExcel($reportData, $validated['report_type'], $params);
                
            default:
                return view('admin.payment.report', [
                    'reportData' => $reportData,
                    'reportType' => $validated['report_type'],
                    'params' => $params
                ]);
        }
    }
    
    /**
     * Export report as PDF
     */
    protected function exportReportAsPDF($reportData, $reportType, $params)
    {
        $pdf = Pdf::loadView('admin.payment.report-pdf', [
            'reportData' => $reportData,
            'reportType' => $reportType,
            'params' => $params
        ]);
        
        $filename = "financial_report_{$reportType}_" . date('Ymd') . ".pdf";
        
        return $pdf->download($filename);
    }
    
    /**
     * Export report as Excel
     */
    protected function exportReportAsExcel($reportData, $reportType, $params)
    {
        // This would require an Excel export class
        // For now, return CSV
        $filename = "financial_report_{$reportType}_" . date('Ymd') . ".csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\""
        ];
        
        $callback = function() use ($reportData) {
            $file = fopen('php://output', 'w');
            
            // Write headers
            fputcsv($file, array_keys($reportData));
            
            // Write data
            fputcsv($file, $reportData);
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    public function apiProcessPayment(Request $request)
{
    $validated = $request->validate([
        'package_id' => 'required|string|exists:package,package_id',
        'payment_method' => 'required|string',
        'amount' => 'required|numeric|min:0'
    ]);
    
    $result = $this->paymentFacade->processPayment($validated['package_id'], $validated);
    
    return response()->json($result);
}

    public function apiGetPaymentStatus(string $paymentId)
    {
        $payment = Payment::with(['package', 'refund'])->findOrFail($paymentId);
        
        return response()->json([
            'success' => true,
            'payment' => [
                'payment_id' => $payment->payment_id,
                'package_id' => $payment->package_id,
                'status' => $payment->status,
                'amount' => $payment->amount,
                'payment_method' => $payment->payment_method,
                'payment_date' => $payment->payment_date,
                'has_refund' => $payment->refund ? true : false
            ]
        ]);
    }

    /**
     * Mark payment as verified (manual verification)
     */
    public function verifyPayment(string $paymentId)
    {
        $payment = Payment::findOrFail($paymentId);
        
        if ($payment->status === 'pending') {
            $payment->status = 'completed';
            $payment->save();
            
            // Update package payment status
            $package = $payment->package;
            $package->payment_status = 'paid';
            $package->save();
            
            // Generate invoice
            $this->paymentFacade->generateInvoice($paymentId);
            
            return back()->with('success', 'Payment verified and marked as completed.');
        }
        
        return back()->with('info', 'Payment is already verified.');
    }
}