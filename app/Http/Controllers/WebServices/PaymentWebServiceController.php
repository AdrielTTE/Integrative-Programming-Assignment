<?php
namespace App\Http\Controllers\WebServices;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Refund;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;


class PaymentWebServiceController extends Controller{
public function getPaymentStatistics(): array //API Implementation ( Provide )2
{
    $mostUsedMethod = Payment::select('payment_method')
        ->selectRaw('count(*) as count')
        ->groupBy('payment_method')
        ->orderByDesc('count')
        ->first();

    return [
        'total_revenue' => Payment::where('status', 'completed')->sum('amount'),
        'pending_payments' => Payment::where('status', 'pending')->count(),
        'completed_today' => Payment::whereDate('payment_date', today())
                                    ->where('status', 'completed')
                                    ->sum('amount'),
        'refunds_pending' => Refund::where('status', 'pending')->count(),
        'average_transaction' => Payment::where('status', 'completed')->avg('amount'),
        'most_used_method' => $mostUsedMethod ? $mostUsedMethod->payment_method : null,
    ];
}
}

