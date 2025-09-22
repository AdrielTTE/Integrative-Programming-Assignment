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

class RefundController extends Controller
{
    protected PaymentFacade $paymentFacade;
    
    public function __construct()
    {
        $this->paymentFacade = new PaymentFacade();
    }
    
    /**
     * Display refund requests
     */
    public function index(Request $request)
    {
        $query = Refund::with(['payment.package', 'user', 'processedBy']);
        
        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('date_from')) {
            $query->whereDate('requested_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to')) {
            $query->whereDate('requested_at', '<=', $request->date_to);
        }
        
        $refunds = $query->orderBy('requested_at', 'desc')->paginate(20);
        
        // Get statistics
        $statistics = [
            'pending' => Refund::where('status', 'pending')->count(),
            'approved' => Refund::where('status', 'approved')->count(),
            'rejected' => Refund::where('status', 'rejected')->count(),
            'total_amount' => Refund::where('status', 'approved')->sum('amount'),
            'avg_processing_time' => Refund::whereNotNull('processed_at')
                                           ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, requested_at, processed_at)) as avg_hours')
                                           ->value('avg_hours')
        ];
        
        return view('admin.refund.index', compact('refunds', 'statistics'));
    }
    
    /**
     * View refund details
     */
    public function show(string $refundId)
    {
        $refund = Refund::with(['payment.package', 'user', 'processedBy'])
                       ->findOrFail($refundId);
        
        return view('admin.refund.show', compact('refund'));
    }
    
    /**
     * Approve refund request
     */
    public function approve(Request $request, string $refundId)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string|max:500'
        ]);
        
        $result = $this->paymentFacade->processRefund($refundId, 'approve', Auth::id());
        
        if ($result['success']) {
            Log::info('Refund approved', [
                'refund_id' => $refundId,
                'admin_id' => Auth::id()
            ]);
            
            return redirect()->route('admin.refunds.index')
                           ->with('success', 'Refund approved successfully. Amount will be credited to customer.');
        }
        
        return back()->with('error', $result['message']);
    }
    
    /**
     * Reject refund request
     */
    public function reject(Request $request, string $refundId)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500'
        ]);
        
        $refund = Refund::findOrFail($refundId);
        
        if ($refund->status !== 'pending') {
            return back()->with('error', 'This refund has already been processed.');
        }
        
        $refund->update([
            'status' => 'rejected',
            'processed_by' => Auth::id(),
            'processed_at' => now(),
            'admin_notes' => $validated['reason']
        ]);
        
        Log::info('Refund rejected', [
            'refund_id' => $refundId,
            'admin_id' => Auth::id(),
            'reason' => $validated['reason']
        ]);
        
        return redirect()->route('admin.refunds.index')
                       ->with('success', 'Refund request rejected.');
    }
    
    /**
     * Process multiple refunds (bulk action)
     */
    public function bulkProcess(Request $request)
    {
        $validated = $request->validate([
            'refund_ids' => 'required|array',
            'refund_ids.*' => 'exists:refunds,refund_id',
            'action' => 'required|in:approve,reject',
            'reason' => 'required_if:action,reject|nullable|string|max:500'
        ]);
        
        $processed = 0;
        $failed = 0;
        
        foreach ($validated['refund_ids'] as $refundId) {
            try {
                if ($validated['action'] === 'approve') {
                    $result = $this->paymentFacade->processRefund($refundId, 'approve', Auth::id());
                } else {
                    $refund = Refund::find($refundId);
                    if ($refund && $refund->status === 'pending') {
                        $refund->update([
                            'status' => 'rejected',
                            'processed_by' => Auth::id(),
                            'processed_at' => now(),
                            'admin_notes' => $validated['reason']
                        ]);
                        $result = ['success' => true];
                    } else {
                        $result = ['success' => false];
                    }
                }
                
                if ($result['success']) {
                    $processed++;
                } else {
                    $failed++;
                }
            } catch (\Exception $e) {
                $failed++;
                Log::error('Bulk refund processing error', [
                    'refund_id' => $refundId,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        $message = "Processed {$processed} refunds successfully.";
        if ($failed > 0) {
            $message .= " {$failed} refunds failed to process.";
        }
        
        return back()->with($failed > 0 ? 'warning' : 'success', $message);
    }
    
    /**
     * Generate refund report
     */
    public function generateReport(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ]);
        
        $refunds = Refund::with(['payment', 'user'])
                        ->whereBetween('requested_at', [$validated['start_date'], $validated['end_date']])
                        ->get();
        
        $report = [
            'period' => [
                'start' => $validated['start_date'],
                'end' => $validated['end_date']
            ],
            'summary' => [
                'total_requests' => $refunds->count(),
                'approved' => $refunds->where('status', 'approved')->count(),
                'rejected' => $refunds->where('status', 'rejected')->count(),
                'pending' => $refunds->where('status', 'pending')->count(),
                'total_amount' => $refunds->where('status', 'approved')->sum('amount')
            ],
            'refunds' => $refunds
        ];
        
        $pdf = Pdf::loadView('admin.refund.report', compact('report'));
        
        return $pdf->download('refund_report_' . date('Ymd') . '.pdf');
    }
}