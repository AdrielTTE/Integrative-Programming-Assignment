@extends('layouts.adminLayout')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Refund Details #{{ $refund->refund_id }}</h1>
        <a href="{{ route('admin.refunds.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded">
            Back to Refunds
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Refund Information -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Refund Information</h2>
            <div class="space-y-3">
                <div class="flex justify-between border-b pb-2">
                    <span class="text-gray-600">Refund ID:</span>
                    <span class="font-mono">{{ $refund->refund_id }}</span>
                </div>
                <div class="flex justify-between border-b pb-2">
                    <span class="text-gray-600">Status:</span>
                    <span>
                        @if($refund->status == 'pending')
                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm">Pending</span>
                        @elseif($refund->status == 'approved')
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-sm">Approved</span>
                        @else
                            <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-sm">Rejected</span>
                        @endif
                    </span>
                </div>
                <div class="flex justify-between border-b pb-2">
                    <span class="text-gray-600">Amount:</span>
                    <span class="font-bold text-lg">RM{{ number_format($refund->amount, 2) }}</span>
                </div>
                <div class="flex justify-between border-b pb-2">
                    <span class="text-gray-600">Requested At:</span>
                    <span>{{ $refund->requested_at->format('M d, Y H:i') }}</span>
                </div>
                @if($refund->processed_at)
                <div class="flex justify-between border-b pb-2">
                    <span class="text-gray-600">Processed At:</span>
                    <span>{{ $refund->processed_at->format('M d, Y H:i') }}</span>
                </div>
                @endif
                @if($refund->processedBy)
                <div class="flex justify-between border-b pb-2">
                    <span class="text-gray-600">Processed By:</span>
                    <span>{{ $refund->processedBy->name }}</span>
                </div>
                @endif
            </div>
        </div>

        <!-- Payment Information -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Payment Information</h2>
            <div class="space-y-3">
                <div class="flex justify-between border-b pb-2">
                    <span class="text-gray-600">Payment ID:</span>
                    <span class="font-mono">{{ $refund->payment->payment_id }}</span>
                </div>
                <div class="flex justify-between border-b pb-2">
                    <span class="text-gray-600">Package ID:</span>
                    <span class="font-mono">{{ $refund->payment->package_id }}</span>
                </div>
                <div class="flex justify-between border-b pb-2">
                    <span class="text-gray-600">Original Amount:</span>
                    <span>RM{{ number_format($refund->payment->amount, 2) }}</span>
                </div>
                <div class="flex justify-between border-b pb-2">
                    <span class="text-gray-600">Payment Method:</span>
                    <span>{{ ucfirst(str_replace('_', ' ', $refund->payment->payment_method)) }}</span>
                </div>
                <div class="flex justify-between border-b pb-2">
                    <span class="text-gray-600">Payment Date:</span>
                    <span>{{ $refund->payment->payment_date->format('M d, Y') }}</span>
                </div>
            </div>
        </div>

        <!-- Customer Information -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Customer Information</h2>
            <div class="space-y-3">
                <div class="flex justify-between border-b pb-2">
                    <span class="text-gray-600">Customer ID:</span>
                    <span class="font-mono">{{ $refund->user_id }}</span>
                </div>
                <div class="flex justify-between border-b pb-2">
                    <span class="text-gray-600">Name:</span>
                    <span>{{ $refund->user->name ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between border-b pb-2">
                    <span class="text-gray-600">Email:</span>
                    <span>{{ $refund->user->email ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between border-b pb-2">
                    <span class="text-gray-600">Phone:</span>
                    <span>{{ $refund->user->phone ?? 'N/A' }}</span>
                </div>
            </div>
        </div>

        <!-- Package Information -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Package Information</h2>
            <div class="space-y-3">
                <div class="flex justify-between border-b pb-2">
                    <span class="text-gray-600">Package ID:</span>
                    <span class="font-mono">{{ $refund->payment->package->package_id ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between border-b pb-2">
                    <span class="text-gray-600">Contents:</span>
                    <span>{{ $refund->payment->package->package_contents ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between border-b pb-2">
                    <span class="text-gray-600">Status:</span>
                    <span>{{ $refund->payment->package->package_status ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between border-b pb-2">
                    <span class="text-gray-600">Weight:</span>
                    <span>{{ $refund->payment->package->package_weight ?? 0 }} kg</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Reason and Notes -->
    <div class="bg-white rounded-lg shadow p-6 mt-6">
        <h2 class="text-xl font-semibold mb-4">Reason and Notes</h2>
        <div class="space-y-4">
            <div>
                <h3 class="font-medium text-gray-700 mb-2">Customer's Reason:</h3>
                <div class="bg-gray-50 p-4 rounded">
                    {{ $refund->reason }}
                </div>
            </div>
            @if($refund->admin_notes)
            <div>
                <h3 class="font-medium text-gray-700 mb-2">Admin Notes:</h3>
                <div class="bg-blue-50 p-4 rounded">
                    {{ $refund->admin_notes }}
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Actions -->
    @if($refund->status == 'pending')
    <div class="bg-white rounded-lg shadow p-6 mt-6">
        <h2 class="text-xl font-semibold mb-4">Actions</h2>
        <div class="flex gap-4">
            <form action="{{ route('admin.refunds.approve', $refund->refund_id) }}" method="POST" class="flex-1">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Admin Notes (Optional)</label>
                    <textarea name="notes" class="w-full border rounded px-3 py-2" rows="3"></textarea>
                </div>
                <button type="submit" class="w-full bg-green-600 text-white px-6 py-3 rounded hover:bg-green-700">
                    Approve Refund
                </button>
            </form>

            <form action="{{ route('admin.refunds.reject', $refund->refund_id) }}" method="POST" class="flex-1">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Reason for Rejection *</label>
                    <textarea name="reason" required class="w-full border rounded px-3 py-2" rows="3"></textarea>
                </div>
                <button type="submit" class="w-full bg-red-600 text-white px-6 py-3 rounded hover:bg-red-700">
                    Reject Refund
                </button>
            </form>
        </div>
    </div>
    @endif
</div>
@endsection