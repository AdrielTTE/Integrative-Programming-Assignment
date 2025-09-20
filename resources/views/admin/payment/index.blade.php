@extends('layouts.adminLayout')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">
        <i class="fas fa-credit-card mr-2"></i>Manage Payments And Report
    </h1>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-green-500 text-white rounded-lg p-6">
            <h3 class="text-lg">Total Revenue</h3>
            <p class="text-3xl font-bold">${{ number_format($stats['totalRevenue'], 2) }}</p>
        </div>
        <div class="bg-blue-500 text-white rounded-lg p-6">
            <h3 class="text-lg">Avg Cost</h3>
            <p class="text-3xl font-bold">${{ number_format($stats['avgDeliveryCost'], 2) }}</p>
        </div>
        <div class="bg-red-500 text-white rounded-lg p-6">
            <h3 class="text-lg">Unpaid</h3>
            <p class="text-3xl font-bold">{{ $stats['unpaidCount'] }}</p>
        </div>
        <div class="bg-purple-500 text-white rounded-lg p-6">
            <h3 class="text-lg">Refunds</h3>
            <p class="text-3xl font-bold">${{ number_format($stats['totalRefunds'], 2) }}</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form method="GET" class="flex gap-4">
            <select name="status" class="border rounded px-4 py-2">
                <option value="">All Status</option>
                <option value="completed">Completed</option>
                <option value="pending">Pending</option>
                <option value="failed">Failed</option>
                <option value="refunded">Refunded</option>
            </select>
            <select name="method" class="border rounded px-4 py-2">
                <option value="">All Methods</option>
                <option value="card">Card</option>
                <option value="paypal">PayPal</option>
                <option value="wallet">Wallet</option>
            </select>
            <input type="text" name="user" placeholder="User ID" class="border rounded px-4 py-2">
            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded">
                Filter
            </button>
            <button type="button" onclick="generateReport()" class="bg-green-600 text-white px-6 py-2 rounded">
                Generate Report
            </button>
        </form>
    </div>

    <!-- Payments Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left">Transaction ID</th>
                    <th class="px-6 py-3 text-left">Package ID</th>
                    <th class="px-6 py-3 text-left">User ID</th>
                    <th class="px-6 py-3 text-left">Amount</th>
                    <th class="px-6 py-3 text-left">Method</th>
                    <th class="px-6 py-3 text-left">Status</th>
                    <th class="px-6 py-3 text-left">Date</th>
                    <th class="px-6 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payments as $payment)
                <tr class="border-t">
                    <td class="px-6 py-4">{{ $payment->transaction_id }}</td>
                    <td class="px-6 py-4">{{ $payment->package_id }}</td>
                    <td class="px-6 py-4">{{ $payment->user_id }}</td>
                    <td class="px-6 py-4">${{ number_format($payment->amount, 2) }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded">
                            {{ ucfirst($payment->payment_method) }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        @if($payment->status == 'completed')
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded">Completed</span>
                        @elseif($payment->status == 'pending')
                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded">Pending</span>
                        @elseif($payment->status == 'failed')
                            <span class="px-2 py-1 bg-red-100 text-red-800 rounded">Failed</span>
                        @else
                            <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded">Refunded</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">{{ $payment->payment_date }}</td>
                    <td class="px-6 py-4">
                        <a href="{{ route('admin.payment.invoice', $payment->payment_id) }}" 
                           class="text-blue-600 hover:text-blue-800">
                            <i class="fas fa-file-invoice"></i> Invoice
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <div class="px-6 py-4 bg-gray-50">
            {{ $payments->links() }}
        </div>
    </div>
</div>

<!-- Report Modal -->
<div id="reportModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-8 max-w-md w-full">
        <h2 class="text-2xl font-bold mb-4">Generate Report</h2>
        <form id="reportForm">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Start Date</label>
                <input type="date" name="start_date" required class="w-full border rounded px-3 py-2">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">End Date</label>
                <input type="date" name="end_date" required class="w-full border rounded px-3 py-2">
            </div>
            <div class="flex gap-4">
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">Generate</button>
                <button type="button" onclick="closeReportModal()" class="bg-gray-400 text-white px-4 py-2 rounded">Cancel</button>
            </div>
        </form>
        <div id="reportResult" class="mt-4 hidden"></div>
    </div>
</div>

<script>
function generateReport() {
    document.getElementById('reportModal').classList.remove('hidden');
}

function closeReportModal() {
    document.getElementById('reportModal').classList.add('hidden');
}

document.getElementById('reportForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    const response = await fetch('{{ route("admin.payment.report") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(Object.fromEntries(formData))
    });
    
    const data = await response.json();
    
    document.getElementById('reportResult').innerHTML = `
        <div class="bg-gray-100 p-4 rounded">
            <h3 class="font-bold mb-2">Report Results</h3>
            <p>Total Transactions: ${data.total_transactions}</p>
            <p>Total Revenue: $${data.total_revenue}</p>
        </div>
    `;
    document.getElementById('reportResult').classList.remove('hidden');
});
</script>
@endsection