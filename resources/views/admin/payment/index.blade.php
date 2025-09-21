@extends('layouts.adminLayout')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Payment Management</h1>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-green-500 text-white rounded-lg p-6">
            <h3 class="text-lg">Total Revenue</h3>
            <p class="text-3xl font-bold">RM{{ number_format($statistics['total_revenue'], 2) }}</p>
        </div>
        <div class="bg-blue-500 text-white rounded-lg p-6">
            <h3 class="text-lg">Pending Payments</h3>
            <p class="text-3xl font-bold">{{ $statistics['pending_payments'] }}</p>
        </div>
        <div class="bg-purple-500 text-white rounded-lg p-6">
            <h3 class="text-lg">Today's Revenue</h3>
            <p class="text-3xl font-bold">RM{{ number_format($statistics['completed_today'], 2) }}</p>
        </div>
        <div class="bg-orange-500 text-white rounded-lg p-6">
            <h3 class="text-lg">Avg Transaction</h3>
            <p class="text-3xl font-bold">RM{{ number_format($statistics['average_transaction'], 2) }}</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form method="GET" class="flex gap-4 flex-wrap">
            <select name="status" class="border rounded px-4 py-2">
                <option value="">All Status</option>
                @foreach($statuses as $status)
                    <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                        {{ ucfirst($status) }}
                    </option>
                @endforeach
            </select>
            <select name="payment_method" class="border rounded px-4 py-2">
                <option value="">All Methods</option>
                @foreach($paymentMethods as $key => $method)
                    <option value="{{ $key }}" {{ request('payment_method') == $key ? 'selected' : '' }}>
                        {{ $method }}
                    </option>
                @endforeach
            </select>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="border rounded px-4 py-2">
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="border rounded px-4 py-2">
            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded">Filter</button>
            <a href="{{ route('admin.payment.index') }}" class="bg-gray-300 text-gray-700 px-6 py-2 rounded">Clear</a>
        </form>
    </div>

    <!-- Payments Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left">Payment ID</th>
                    <th class="px-6 py-3 text-left">Package ID</th>
                    <th class="px-6 py-3 text-left">Customer</th>
                    <th class="px-6 py-3 text-left">Amount</th>
                    <th class="px-6 py-3 text-left">Method</th>
                    <th class="px-6 py-3 text-left">Status</th>
                    <th class="px-6 py-3 text-left">Date</th>
                    <th class="px-6 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payments as $payment)
                <tr class="border-t hover:bg-gray-50">
                    <td class="px-6 py-4 font-mono">{{ $payment->payment_id }}</td>
                    <td class="px-6 py-4">{{ $payment->package_id }}</td>
                    <td class="px-6 py-4">{{ $payment->user->username }}</td>
                    <td class="px-6 py-4 font-semibold">RM{{ number_format($payment->amount, 2) }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-sm">
                            {{ $paymentMethods[$payment->payment_method] ?? $payment->payment_method }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        @if($payment->status == 'completed')
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-sm">Completed</span>
                        @elseif($payment->status == 'pending')
                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-sm">Pending</span>
                        @elseif($payment->status == 'failed')
                            <span class="px-2 py-1 bg-red-100 text-red-800 rounded text-sm">Failed</span>
                        @else
                            <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded text-sm">Refunded</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm">{{ $payment->payment_date->format('M d, Y') }}</td>
                    <td class="px-6 py-4 space-x-2">
                        <a href="{{ route('admin.payment.show', $payment->payment_id) }}" 
                           class="text-blue-600 hover:text-blue-800 text-sm">View</a>
                        <a href="{{ route('admin.payment.generateInvoice', $payment->payment_id) }}" 
                           class="text-green-600 hover:text-green-800 text-sm">Invoice</a>
                        @if($payment->status == 'pending')
                            <form method="POST" action="{{ route('admin.payment.verifyPayment', $payment->payment_id) }}" class="inline">
                                @csrf
                                <button type="submit" class="text-indigo-600 hover:text-indigo-800 text-sm">Verify</button>
                            </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <div class="px-6 py-4 bg-gray-50">
            {{ $payments->withQueryString()->links() }}
        </div>
    </div>

    <!-- Report Generation -->
    <div class="mt-6 bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Generate Financial Report</h3>
        <form method="POST" action="{{ route('admin.payment.generateReport') }}" class="flex gap-4 items-end">
            @csrf
            <div>
                <label class="block text-sm font-medium mb-2">Report Type</label>
                <select name="report_type" class="border rounded px-3 py-2">
                    <option value="revenue_summary">Revenue Summary</option>
                    <option value="payment_methods">Payment Methods Breakdown</option>
                    <option value="refund_analysis">Refund Analysis</option>
                    <option value="customer_spending">Customer Spending Report</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">Start Date</label>
                <input type="date" name="start_date" class="border rounded px-3 py-2" required>
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">End Date</label>
                <input type="date" name="end_date" class="border rounded px-3 py-2" required>
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">Format</label>
                <select name="format" class="border rounded px-3 py-2">
                    <option value="view">View Online</option>
                    <option value="pdf">Download PDF</option>
                    <option value="excel">Download Excel</option>
                </select>
            </div>
            <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded">Generate Report</button>
        </form>
    </div>
</div>
@endsection
