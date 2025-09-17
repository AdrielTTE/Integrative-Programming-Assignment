@extends('layouts.adminLayout')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">
        <i class="fas fa-undo mr-2"></i>Request Refunds
    </h1>

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-8">
        <div class="bg-yellow-500 text-white rounded-lg p-4">
            <h3 class="text-sm">Pending</h3>
            <p class="text-2xl font-bold">{{ $stats['pending'] }}</p>
        </div>
        <div class="bg-blue-500 text-white rounded-lg p-4">
            <h3 class="text-sm">Approved</h3>
            <p class="text-2xl font-bold">{{ $stats['approved'] }}</p>
        </div>
        <div class="bg-green-500 text-white rounded-lg p-4">
            <h3 class="text-sm">Processed</h3>
            <p class="text-2xl font-bold">{{ $stats['processed'] }}</p>
        </div>
        <div class="bg-red-500 text-white rounded-lg p-4">
            <h3 class="text-sm">Rejected</h3>
            <p class="text-2xl font-bold">{{ $stats['rejected'] }}</p>
        </div>
        <div class="bg-purple-500 text-white rounded-lg p-4">
            <h3 class="text-sm">Total Amount</h3>
            <p class="text-2xl font-bold">${{ number_format($stats['total_amount'], 2) }}</p>
        </div>
    </div>

    <!-- Filter -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form method="GET" class="flex gap-4">
            <select name="status" class="border rounded px-4 py-2">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
                <option value="processed">Processed</option>
            </select>
            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded">Filter</button>
        </form>
    </div>

    <!-- Refunds Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left">Refund ID</th>
                    <th class="px-6 py-3 text-left">Payment ID</th>
                    <th class="px-6 py-3 text-left">User ID</th>
                    <th class="px-6 py-3 text-left">Amount</th>
                    <th class="px-6 py-3 text-left">Reason</th>
                    <th class="px-6 py-3 text-left">Status</th>
                    <th class="px-6 py-3 text-left">Request Date</th>
                    <th class="px-6 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($refunds as $refund)
                <tr class="border-t">
                    <td class="px-6 py-4">{{ $refund->refund_id }}</td>
                    <td class="px-6 py-4">{{ $refund->payment_id }}</td>
                    <td class="px-6 py-4">{{ $refund->user_id }}</td>
                    <td class="px-6 py-4">${{ number_format($refund->refund_amount, 2) }}</td>
                    <td class="px-6 py-4">{{ $refund->reason }}</td>
                    <td class="px-6 py-4">
                        @if($refund->status == 'pending')
                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded">Pending</span>
                        @elseif($refund->status == 'approved')
                            <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded">Approved</span>
                        @elseif($refund->status == 'processed')
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded">Processed</span>
                        @else
                            <span class="px-2 py-1 bg-red-100 text-red-800 rounded">Rejected</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">{{ $refund->request_date }}</td>
                    <td class="px-6 py-4">
                        @if($refund->status == 'pending')
                            <button onclick="approveRefund({{ $refund->refund_id }})" 
                                    class="bg-green-500 text-white px-3 py-1 rounded text-sm mr-2">
                                Approve
                            </button>
                            <button onclick="rejectRefund({{ $refund->refund_id }})" 
                                    class="bg-red-500 text-white px-3 py-1 rounded text-sm">
                                Reject
                            </button>
                        @elseif($refund->status == 'approved')
                            <form action="{{ route('admin.refunds.process', $refund->refund_id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="bg-blue-500 text-white px-3 py-1 rounded text-sm">
                                    Process
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <div class="px-6 py-4 bg-gray-50">
            {{ $refunds->links() }}
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div id="approveModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 max-w-md w-full">
        <h2 class="text-xl font-bold mb-4">Approve Refund</h2>
        <form id="approveForm" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Admin Notes</label>
                <textarea name="admin_notes" required class="w-full border rounded px-3 py-2" rows="3"></textarea>
            </div>
            <div class="flex gap-4">
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">Approve</button>
                <button type="button" onclick="closeModal('approveModal')" class="bg-gray-400 text-white px-4 py-2 rounded">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 max-w-md w-full">
        <h2 class="text-xl font-bold mb-4">Reject Refund</h2>
        <form id="rejectForm" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Reason for Rejection</label>
                <textarea name="admin_notes" required class="w-full border rounded px-3 py-2" rows="3"></textarea>
            </div>
            <div class="flex gap-4">
                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded">Reject</button>
                <button type="button" onclick="closeModal('rejectModal')" class="bg-gray-400 text-white px-4 py-2 rounded">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function approveRefund(id) {
    document.getElementById('approveModal').classList.remove('hidden');
    document.getElementById('approveForm').action = `/admin/refunds/${id}/approve`;
}

function rejectRefund(id) {
    document.getElementById('rejectModal').classList.remove('hidden');
    document.getElementById('rejectForm').action = `/admin/refunds/${id}/reject`;
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}
</script>
@endsection