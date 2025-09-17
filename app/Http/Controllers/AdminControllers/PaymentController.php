<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * Display payments page
     */
    public function index(Request $request)
    {
        // Build query
        $query = DB::table('payments');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('method')) {
            $query->where('payment_method', $request->method);
        }
        if ($request->filled('user')) {
            $query->where('user_id', 'like', '%'.$request->user.'%');
        }

        // Get paginated results
        $payments = $query->orderBy('payment_date', 'desc')->paginate(10);

        // Calculate statistics
        $stats = [
            'totalRevenue' => DB::table('payments')
                ->where('status', 'completed')
                ->sum('amount'),
            'avgDeliveryCost' => DB::table('payments')
                ->where('status', 'completed')
                ->avg('amount'),
            'unpaidCount' => DB::table('payments')
                ->where('status', 'pending')
                ->count(),
            'totalRefunds' => DB::table('payments')
                ->where('status', 'refunded')
                ->sum('amount'),
        ];

        return view('admin.payment.index', compact('payments', 'stats'));
    }

    /**
     * Generate report
     */
    public function generateReport(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ]);

        $payments = DB::table('payments')
            ->whereBetween('payment_date', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ])
            ->get();

        $report = [
            'total_transactions' => $payments->count(),
            'total_revenue' => $payments->where('status', 'completed')->sum('amount'),
            'by_method' => $payments->groupBy('payment_method')->map->count(),
            'by_status' => $payments->groupBy('status')->map->count()
        ];

        return response()->json($report);
    }

    /**
     * Generate invoice
     */
    public function generateInvoice($id)
    {
        $payment = DB::table('payments')
            ->where('payment_id', $id)
            ->first();

        if (!$payment) {
            return redirect()->back()->with('error', 'Payment not found');
        }

        // For now, return a simple view. You can enhance this later
        return view('admin.invoice', compact('payment'));
    }
}