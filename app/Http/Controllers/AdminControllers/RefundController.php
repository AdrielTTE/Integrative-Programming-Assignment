<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RefundController extends Controller
{
    /**
     * Display refunds page
     */
    public function index(Request $request)
    {
        // Get refunds from database
        $refunds = DB::table('refunds')
            ->leftJoin('payments', 'refunds.payment_id', '=', 'payments.payment_id')
            ->select('refunds.*', 'payments.transaction_id', 'payments.amount as payment_amount')
            ->orderBy('refunds.request_date', 'desc')
            ->paginate(10);

        // Calculate statistics
        $stats = [
            'pending' => DB::table('refunds')->where('status', 'pending')->count(),
            'approved' => DB::table('refunds')->where('status', 'approved')->count(),
            'processed' => DB::table('refunds')->where('status', 'processed')->count(),
            'rejected' => DB::table('refunds')->where('status', 'rejected')->count(),
            'total_amount' => DB::table('refunds')->where('status', 'processed')->sum('refund_amount')
        ];

        return view('admin.refunds.index', compact('refunds', 'stats'));
    }

    /**
     * Approve refund
     */
    public function approve(Request $request, $id)
    {
        $request->validate([
            'admin_notes' => 'required|string|max:500'
        ]);

        DB::table('refunds')
            ->where('refund_id', $id)
            ->update([
                'status' => 'approved',
                'admin_notes' => strip_tags($request->admin_notes)
            ]);

        return redirect()->back()->with('success', 'Refund approved successfully');
    }

    /**
     * Reject refund
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'admin_notes' => 'required|string|max:500'
        ]);

        DB::table('refunds')
            ->where('refund_id', $id)
            ->update([
                'status' => 'rejected',
                'admin_notes' => strip_tags($request->admin_notes),
                'process_date' => now()
            ]);

        return redirect()->back()->with('success', 'Refund rejected');
    }

    /**
     * Process refund
     */
    public function process($id)
    {
        DB::beginTransaction();
        try {
            // Get refund details
            $refund = DB::table('refunds')->where('refund_id', $id)->first();
            
            if (!$refund) {
                throw new \Exception('Refund not found');
            }

            if ($refund->status !== 'approved') {
                throw new \Exception('Refund must be approved before processing');
            }

            // Update refund status
            DB::table('refunds')
                ->where('refund_id', $id)
                ->update([
                    'status' => 'processed',
                    'process_date' => now()
                ]);

            // Update payment status
            DB::table('payments')
                ->where('payment_id', $refund->payment_id)
                ->update(['status' => 'refunded']);

            DB::commit();
            return redirect()->back()->with('success', 'Refund processed successfully');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}