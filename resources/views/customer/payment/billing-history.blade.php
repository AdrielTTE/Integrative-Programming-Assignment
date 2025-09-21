@extends('layouts.customerLayout')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Billing History</h1>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <form method="GET" class="flex gap-4 flex-wrap">
            <select name="status" class="border rounded px-4 py-2">
                <option value="">All Status</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                <option value="refunded" {{ request('status') == 'refunded' ? 'selected' : '' }}>Refunded</option>
            </select>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="border rounded px-4 py-2">
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="border rounded px-4 py-2">
            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded">Filter</button>
            <a href="{{ route('customer.billing.history') }}" class="bg-gray-300 text-gray-700 px-6 py-2 rounded">Clear</a>
        </form>
    </div>

    <!-- Payments List -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        @if($payments->count() > 0)
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payment ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Package</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($payments as $payment)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-mono">{{ $payment->payment_id }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $payment->package_id }}</div>
                                <div class="text-sm text-gray-500">{{ Str::limit($payment->package->package_contents ?? '', 30) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold">RM{{ number_format($payment->amount, 2) }}</div>
                                <div class="text-xs text-gray-500">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($payment->status == 'completed')
                                    <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded-full">Completed</span>
                                @elseif($payment->status == 'pending')
                                    <span class="px-2 py-1 text-xs font-semibold bg-yellow-100 text-yellow-800 rounded-full">Pending</span>
                                @elseif($payment->status == 'refunded')
                                    <span class="px-2 py-1 text-xs font-semibold bg-purple-100 text-purple-800 rounded-full">Refunded</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold bg-red-100 text-red-800 rounded-full">Failed</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $payment->payment_date->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                                @if($payment->invoice)
                                    <a href="{{ route('customer.billing.invoice.download', $payment->payment_id) }}" 
                                       class="text-indigo-600 hover:text-indigo-800">Download Invoice</a>
                                @endif
                                
                                @if($payment->is_refundable && !$payment->refund)
                                    <a href="{{ route('customer.refund.request', $payment->payment_id) }}" 
                                       class="text-orange-600 hover:text-orange-800">Request Refund</a>
                                @endif
                                
                                @if($payment->refund)
                                    <a href="{{ route('customer.refund.status', $payment->refund->refund_id) }}" 
                                       class="text-gray-600 hover:text-gray-800">View Refund</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            
            <div class="px-6 py-4 bg-gray-50">
                {{ $payments->withQueryString()->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No payment history</h3>
                <p class="mt-1 text-sm text-gray-500">Your payment history will appear here once you make your first payment.</p>
            </div>
        @endif
    </div>
</div>
@endsection