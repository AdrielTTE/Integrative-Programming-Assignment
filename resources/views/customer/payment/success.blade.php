@extends('layouts.customerLayout')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-md p-8 text-center">
        <div class="mb-6">
            <div class="mx-auto w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-800">Payment Successful!</h1>
            <p class="text-gray-600 mt-2">Your delivery request has been confirmed and will be processed shortly.</p>
        </div>

        <div class="bg-gray-50 rounded-lg p-6 mb-6">
            <h2 class="text-lg font-semibold mb-4">Payment Details</h2>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span>Payment ID:</span>
                    <span class="font-mono">{{ $payment->payment_id }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Package ID:</span>
                    <span class="font-mono">{{ $payment->package_id }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Amount Paid:</span>
                    <span class="font-semibold">RM{{ number_format($payment->amount, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Payment Method:</span>
                    <span class="capitalize">{{ str_replace('_', ' ', $payment->payment_method) }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Transaction ID:</span>
                    <span class="font-mono text-xs">{{ $payment->transaction_id }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Date:</span>
                    <span>{{ $payment->payment_date->format('M d, Y g:i A') }}</span>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <a href="{{ route('customer.packages.show', $payment->package_id) }}" 
               class="inline-block bg-indigo-600 text-white px-6 py-3 rounded-md hover:bg-indigo-700">
                View Package Details
            </a>
            
            @if($payment->invoice)
                <a href="{{ route('customer.billing.invoice.download', $payment->payment_id) }}" 
                   class="inline-block bg-green-600 text-white px-6 py-3 rounded-md hover:bg-green-700 ml-4">
                    Download Invoice
                </a>
            @endif
            
            <div class="mt-4">
                <a href="{{ route('customer.packages.index') }}" class="text-indigo-600 hover:text-indigo-800">
                    ← Back to My Packages
                </a>
            </div>
        </div>
    </div>
</div>
@endsection