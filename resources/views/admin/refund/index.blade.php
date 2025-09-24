@extends('layouts.adminLayout')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">
        <i class="fas fa-undo mr-2"></i>Refund Management
    </h1>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            {{ session('success') }}
        </div>
    @endif

    @if(session('warning'))
        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-6">
            {{ session('warning') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            {{ session('error') }}
        </div>
    @endif

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-yellow-500 text-white rounded-lg p-4">
            <h3 class="text-sm">Pending</h3>
            <p class="text-2xl font-bold">{{ $statistics['pending'] ?? 0 }}</p>
        </div>
        <div class="bg-blue-500 text-white rounded-lg p-4">
            <h3 class="text-sm">Approved</h3>
            <p class="text-2xl font-bold">{{ $statistics['approved'] ?? 0 }}</p>
        </div>
        <div class="bg-red-500 text-white rounded-lg p-4">
            <h3 class="text-sm">Rejected</h3>
            <p class="text-2xl font-bold">{{ $statistics['rejected'] ?? 0 }}</p>
        </div>
        <div class="bg-purple-500 text-white rounded-lg p-4">
            <h3 class="text-sm">Total Amount</h3>
            <p class="text-xl font-bold">RM{{ number_format($statistics['total_amount'] ?? 0, 2) }}</p>
        </div>
    </div>

    <!-- Filter -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form method="GET" class="flex gap-4">
            <select name="status" class="border rounded px-4 py-2">
                <option value="">All Status</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="border rounded px-4 py-2" placeholder="From Date">
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="border rounded px-4 py-2" placeholder="To Date">
            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded">Filter</button>
            <a href="{{ route('admin.refunds.index') }}" class="bg-gray-400 text-white px-6 py-2 rounded">Clear</a>
        </form>
    </div>

    <!-- Bulk Actions -->
    <form id="bulkForm" action="{{ route('admin.refunds.bulk') }}" method="POST">
        @csrf
        <div class="mb-4 flex gap-2">
            <select name="action" class="border rounded px-4 py-2" required>
                <option value="">Select Bulk Action</option>
                <option value="approve">Approve Selected</option>
                <option value="reject">Reject Selected</option>
            </select>
            <input type="text" name="reason" placeholder="Reason for bulk action" class="border rounded px-4 py-2">
            <button type="submit" class="bg-orange-600 text-white px-6 py-2 rounded">Apply to Selected</button>
        </div>

        <!-- Refunds Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left">
                            <input type="checkbox" id="selectAll" class="form-checkbox">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Refund ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payment ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reason</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Request Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($refunds as $refund)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            @if($refund->status == 'pending')
                                <input type="checkbox" name="refund_ids[]" value="{{ $refund->refund_id }}" class="refund-checkbox">
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap font-mono text-sm">{{ $refund->refund_id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a href="{{ route('admin.refunds.show', $refund->refund_id) }}" class="text-indigo-600 hover:text-indigo-800">
                                {{ $refund->payment_id }}
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $refund->user->username ?? 'N/A' }}</div>
                            <div class="text-sm text-gray-500">{{ $refund->user_id }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap font-semibold">RM{{ number_format($refund->amount, 2) }}</td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900 max-w-xs truncate" title="{{ $refund->reason }}">
                                {{ Str::limit($refund->reason, 30) }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($refund->status == 'pending')
                                <span class="px-2 py-1 text-xs font-semibold bg-yellow-100 text-yellow-800 rounded-full">Pending</span>
                            @elseif($refund->status == 'approved')
                                <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded-full">Approved</span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold bg-red-100 text-red-800 rounded-full">Rejected</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $refund->requested_at->format('M d, Y') }}
                            @if($refund->status == 'pending' && $refund->days_waiting > 3)
                                <br><span class="text-red-600 text-xs">{{ $refund->days_waiting }} days waiting</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <a href="{{ route('admin.refunds.show', $refund->refund_id) }}" 
                               class="text-indigo-600 hover:text-indigo-800 mr-2">View</a>
                            
                            @if($refund->status == 'pending')
                                <button type="button" onclick="openApproveModal('{{ $refund->refund_id }}')" 
                                        class="text-green-600 hover:text-green-800 mr-2">Approve</button>
                                <button type="button" onclick="openRejectModal('{{ $refund->refund_id }}')" 
                                        class="text-red-600 hover:text-red-800">Reject</button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center text-gray-500">
                            No refund requests found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            
            <div class="px-6 py-4 bg-gray-50">
                {{ $refunds->withQueryString()->links() }}
            </div>
        </div>
    </form>

<!-- Approve Modal -->
<div id="approveModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-md w-full">
        <h2 class="text-xl font-bold mb-4">Approve Refund</h2>
        <form id="approveForm" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Admin Notes (Optional)</label>
                <textarea name="notes" class="w-full border rounded px-3 py-2" rows="3" 
                          placeholder="Enter any notes about this approval..."></textarea>
            </div>
            <div class="flex gap-4">
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                    Approve Refund
                </button>
                <button type="button" onclick="closeModal('approveModal')" 
                        class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-md w-full">
        <h2 class="text-xl font-bold mb-4">Reject Refund</h2>
        <form id="rejectForm" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Reason for Rejection *</label>
                <textarea name="reason" required class="w-full border rounded px-3 py-2" rows="3" 
                          placeholder="Please provide a reason for rejection..."></textarea>
            </div>
            <div class="flex gap-4">
                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                    Reject Refund
                </button>
                <button type="button" onclick="closeModal('rejectModal')" 
                        class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
// Select All functionality
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.refund-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
});

function openApproveModal(refundId) {
    document.getElementById('approveModal').classList.remove('hidden');
    document.getElementById('approveForm').action = `/admin/refunds/${refundId}/approve`;
}

function openRejectModal(refundId) {
    document.getElementById('rejectModal').classList.remove('hidden');
    document.getElementById('rejectForm').action = `/admin/refunds/${refundId}/reject`;
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

// Close modals on escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeModal('approveModal');
        closeModal('rejectModal');
    }
});
</script>
@endsection