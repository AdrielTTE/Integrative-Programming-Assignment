@extends('layouts.customerLayout')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <!-- Success Message -->
        <div class="bg-green-50 rounded-lg p-8 text-center mb-6">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
                <svg class="h-10 w-10 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-green-800 mb-2">Payment Successful!</h1>
            <p class="text-green-600">Your payment has been processed successfully.</p>
        </div>

        <!-- Payment Details -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Payment Details</h2>
            <div class="space-y-3">
                <div class="flex justify-between py-2 border-b">
                    <span class="text-gray-600">Payment ID:</span>
                    <span class="font-mono">{{ $payment->payment_id }}</span>
                </div>
                <div class="flex justify-between py-2 border-b">
                    <span class="text-gray-600">Transaction ID:</span>
                    <span class="font-mono text-sm">{{ $payment->transaction_id }}</span>
                </div>
                <div class="flex justify-between py-2 border-b">
                    <span class="text-gray-600">Amount Paid:</span>
                    <span class="font-semibold text-lg">RM{{ number_format($payment->amount, 2) }}</span>
                </div>
                <div class="flex justify-between py-2 border-b">
                    <span class="text-gray-600">Payment Method:</span>
                    <span>{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</span>
                </div>
                <div class="flex justify-between py-2 border-b">
                    <span class="text-gray-600">Payment Date:</span>
                    <span>{{ $payment->payment_date->format('M d, Y h:i A') }}</span>
                </div>
            </div>
        </div>

        <!-- Package Information -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Package Information</h2>
            <div class="space-y-3">
                <div class="flex justify-between py-2 border-b">
                    <span class="text-gray-600">Package ID:</span>
                    <span class="font-mono">{{ $payment->package->package_id }}</span>
                </div>
                <div class="flex justify-between py-2 border-b">
                    <span class="text-gray-600">Tracking Number:</span>
                    <span class="font-mono">{{ $payment->package->tracking_number }}</span>
                </div>
                <div class="flex justify-between py-2 border-b">
                    <span class="text-gray-600">Contents:</span>
                    <span>{{ $payment->package->package_contents }}</span>
                </div>
                <div class="flex justify-between py-2 border-b">
                    <span class="text-gray-600">Status:</span>
                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">Processing</span>
                </div>
            </div>
        </div>

        <!-- Next Steps -->
        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
            <h3 class="font-semibold text-blue-800 mb-2">What's Next?</h3>
            <ul class="text-sm text-blue-700 space-y-1">
                <li>• Your package is now being processed for delivery</li>
                <li>• You will receive an email confirmation shortly</li>
                <li>• Track your package using the tracking number above</li>
                <li>• You can view this payment in your billing history</li>
            </ul>
        </div>

        <!-- Actions -->
        <div class="flex flex-col sm:flex-row gap-4">
            <a href="{{ route('customer.packages.show', $payment->package_id) }}" 
               class="flex-1 bg-indigo-600 text-white text-center px-6 py-3 rounded hover:bg-indigo-700">
                View Package Details
            </a>
            
            @if($payment->invoice)
            <a href="{{ route('customer.billing.invoice.download', $payment->payment_id) }}" 
               class="flex-1 bg-green-600 text-white text-center px-6 py-3 rounded hover:bg-green-700">
                Download Invoice
            </a>
            @endif
            
            <a href="{{ route('customer.billing.history') }}" 
               class="flex-1 bg-gray-600 text-white text-center px-6 py-3 rounded hover:bg-gray-700">
                View Billing History
            </a>
        </div>

        <!-- Create Another Package -->
        <div class="text-center mt-8">
            <p class="text-gray-600 mb-2">Need to send another package?</p>
            <a href="{{ route('customer.packages.create') }}" 
               class="text-indigo-600 hover:text-indigo-800 font-semibold">
                Create New Package →
            </a>
        </div>
    </div>
</div>
@endsection