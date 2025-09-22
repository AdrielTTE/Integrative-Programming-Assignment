@extends('layouts.customerLayout')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Refund Status</h1>

    <div class="max-w-3xl mx-auto">
        <!-- Status Alert -->
        @if($refund->status == 'pending')
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            Your refund request is pending review. We'll process it within 2-3 business days.
                        </p>
                    </div>
                </div>
            </div>
        @elseif($refund->status == 'approved')
            <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-700">
                            Your refund has been approved! The amount will be credited to your original payment method within 7-14 business days.
                        </p>
                    </div>
                </div>
            </div>
        @else
            <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700">
                            Your refund request has been rejected. Please contact customer support if you have questions.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Refund Details -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Refund Details</h2>
            <div class="space-y-3">
                <div class="flex justify-between py-2 border-b">
                    <span class="text-gray-600">Refund ID:</span>
                    <span class="font-mono">{{ $refund->refund_id }}</span>
                </div>
                <div class="flex justify-between py-2 border-b">
                    <span class="text-gray-600">Status:</span>
                    <span>
                        @if($refund->status == 'pending')
                            <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-semibold">Pending</span>
                        @elseif($refund->status == 'approved')
                            <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-semibold">Approved</span>
                        @else
                            <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm font-semibold">Rejected</span>
                        @endif
                    </span>
                </div>
                <div class="flex justify-between py-2 border-b">
                    <span class="text-gray-600">Refund Amount:</span>
                    <span class="font-semibold text-lg">RM{{ number_format($refund->amount, 2) }}</span>
                </div>
                <div class="flex justify-between py-2 border-b">
                    <span class="text-gray-600">Request Date:</span>
                    <span>{{ $refund->requested_at->format('M d, Y h:i A') }}</span>
                </div>
                @if($refund->processed_at)
                <div class="flex justify-between py-2 border-b">
                    <span class="text-gray-600">Processed Date:</span>
                    <span>{{ $refund->processed_at->format('M d, Y h:i A') }}</span>
                </div>
                @endif
                @if($refund->refund_transaction_id)
                <div class="flex justify-between py-2 border-b">
                    <span class="text-gray-600">Transaction ID:</span>
                    <span class="font-mono text-sm">{{ $refund->refund_transaction_id }}</span>
                </div>
                @endif
            </div>
        </div>

        <!-- Original Payment Information -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Original Payment Information</h2>
            <div class="space-y-3">
                <div class="flex justify-between py-2 border-b">
                    <span class="text-gray-600">Payment ID:</span>
                    <span class="font-mono">{{ $refund->payment->payment_id }}</span>
                </div>
                <div class="flex justify-between py-2 border-b">
                    <span class="text-gray-600">Package ID:</span>
                    <span class="font-mono">{{ $refund->payment->package_id }}</span>
                </div>
                <div class="flex justify-between py-2 border-b">
                    <span class="text-gray-600">Payment Method:</span>
                    <span>{{ ucfirst(str_replace('_', ' ', $refund->payment->payment_method)) }}</span>
                </div>
                <div class="flex justify-between py-2 border-b">
                    <span class="text-gray-600">Payment Date:</span>
                    <span>{{ $refund->payment->payment_date->format('M d, Y') }}</span>
                </div>
            </div>
        </div>

        <!-- Reason and Notes -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Reason & Notes</h2>
            <div class="space-y-4">
                <div>
                    <h3 class="font-medium text-gray-700 mb-2">Your Reason:</h3>
                    <div class="bg-gray-50 p-4 rounded">
                        {{ $refund->reason }}
                    </div>
                </div>
                
                @if($refund->admin_notes)
                <div>
                    <h3 class="font-medium text-gray-700 mb-2">Admin Response:</h3>
                    <div class="bg-blue-50 p-4 rounded">
                        {{ $refund->admin_notes }}
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Timeline -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Refund Timeline</h2>
            <div class="relative">
                <div class="absolute left-4 top-0 h-full w-0.5 bg-gray-200"></div>
                
                <!-- Request Submitted -->
                <div class="flex items-center mb-6">
                    <div class="absolute left-2 w-4 h-4 bg-indigo-600 rounded-full"></div>
                    <div class="ml-10">
                        <div class="font-medium">Refund Requested</div>
                        <div class="text-sm text-gray-500">{{ $refund->requested_at->format('M d, Y h:i A') }}</div>
                    </div>
                </div>
                
                @if($refund->status != 'pending')
                <!-- Processed -->
                <div class="flex items-center mb-6">
                    <div class="absolute left-2 w-4 h-4 {{ $refund->status == 'approved' ? 'bg-green-600' : 'bg-red-600' }} rounded-full"></div>
                    <div class="ml-10">
                        <div class="font-medium">
                            {{ $refund->status == 'approved' ? 'Refund Approved' : 'Refund Rejected' }}
                        </div>
                        <div class="text-sm text-gray-500">{{ $refund->processed_at->format('M d, Y h:i A') }}</div>
                        @if($refund->processedBy)
                        <div class="text-sm text-gray-400">By {{ $refund->processedBy->name }}</div>
                        @endif
                    </div>
                </div>
                @endif
                
                @if($refund->status == 'approved')
                <!-- Expected Credit -->
                <div class="flex items-center">
                    <div class="absolute left-2 w-4 h-4 bg-gray-400 rounded-full"></div>
                    <div class="ml-10">
                        <div class="font-medium">Expected Credit</div>
                        <div class="text-sm text-gray-500">
                            Within 7-14 business days from approval
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-between">
            <a href="{{ route('customer.billing.history') }}" 
               class="text-gray-600 hover:text-gray-800 flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Billing History
            </a>
            
            @if($refund->status == 'rejected' || ($refund->status == 'approved' && $refund->processed_at->diffInDays(now()) > 14))
            <a href="mailto:support@example.com?subject=Refund Inquiry - {{ $refund->refund_id }}" 
               class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                Contact Support
            </a>
            @endif
        </div>
    </div>
</div>
@endsection