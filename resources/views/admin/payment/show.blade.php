@extends('layouts.adminLayout')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6 no-print">
        <h1 class="text-3xl font-bold text-gray-800">Payment Details</h1>
        <a href="{{ route('admin.payment.index') }}" class="text-indigo-600 hover:text-indigo-900">
            &larr; Back to Payment Management
        </a>
    </div>

    <!-- Print Header (only visible when printing) -->
    <div class="print-only text-center mb-8">
        <h1 class="text-2xl font-bold text-gray-800 mb-2">Payment Details Report</h1>
        <p class="text-gray-600">Generated on {{ now()->format('M d, Y h:i A') }}</p>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6 no-print">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6 no-print">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Payment Information -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Payment Overview -->
            <div class="bg-white shadow-lg rounded-lg p-6">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <p class="text-sm text-gray-500">Payment ID</p>
                        <p class="text-2xl font-mono text-indigo-600 font-bold">{{ $payment->payment_id }}</p>
                        @if($payment->transaction_id)
                            <p class="text-sm font-mono text-gray-600 mt-1">TXN: {{ $payment->transaction_id }}</p>
                        @endif
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-500 mb-1">Status</p>
                        @if($payment->refund)
                            {{-- If there's a refund, prioritize showing refund status --}}
                            @if($payment->refund->status == 'approved')
                                <span class="px-3 py-1 bg-purple-100 text-purple-800 rounded-full text-sm font-semibold">Refunded</span>
                            @elseif($payment->refund->status == 'pending')
                                <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-semibold">Refund Pending</span>
                            @elseif($payment->refund->status == 'rejected')
                                <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm font-semibold">Refund Rejected</span>
                            @endif
                        @else
                            {{-- No refund, show payment status --}}
                            @if($payment->status == 'completed')
                                <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-semibold">Completed</span>
                            @elseif($payment->status == 'pending')
                                <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-semibold">Pending</span>
                            @elseif($payment->status == 'failed')
                                <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm font-semibold">Failed</span>
                            @else
                                <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm font-semibold">{{ ucfirst($payment->status) }}</span>
                            @endif
                        @endif
                    </div>
                </div>

                <hr class="my-4">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="font-semibold text-lg mb-3">Payment Information</h3>
                        <div class="space-y-2 text-sm">
                            <div><strong>Amount:</strong> RM{{ number_format($payment->amount, 2) }}</div>
                            <div><strong>Payment Method:</strong> {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</div>
                            <div><strong>Payment Date:</strong> {{ $payment->payment_date->format('M d, Y h:i A') }}</div>
                            @if($payment->notes)
                                <div><strong>Notes:</strong> {{ $payment->notes }}</div>
                            @endif
                        </div>
                    </div>

                    <div>
                        <h3 class="font-semibold text-lg mb-3">Package Information</h3>
                        <div class="space-y-2 text-sm">
                            <div><strong>Package ID:</strong> 
                                <a href="{{ route('admin.packages.show', $payment->package_id) }}" 
                                   class="text-indigo-600 hover:text-indigo-900 font-mono">
                                    {{ $payment->package_id }}
                                </a>
                            </div>
                            @if($payment->package)
                                <div><strong>Tracking Number:</strong> 
                                    <span class="font-mono">{{ $payment->package->tracking_number }}</span>
                                </div>
                                <div><strong>Contents:</strong> {{ $payment->package->package_contents }}</div>
                                <div><strong>Weight:</strong> {{ $payment->package->package_weight }} kg</div>
                                <div><strong>Status:</strong> {{ ucwords(str_replace('_', ' ', $payment->package->package_status)) }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Information -->
            <div class="bg-white shadow-lg rounded-lg p-6">
                <h3 class="font-semibold text-lg mb-4">Customer Information</h3>
                @if($payment->user)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Customer ID</p>
                            <p class="text-gray-800 font-mono">{{ $payment->user->user_id }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-600">Username</p>
                            <p class="text-gray-800">{{ $payment->user->username }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-600">Email</p>
                            <p class="text-gray-800">
                                <a href="mailto:{{ $payment->user->email }}" class="text-indigo-600 hover:underline">
                                    {{ $payment->user->email }}
                                </a>
                            </p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-600">Phone</p>
                            <p class="text-gray-800">{{ $payment->user->phone_number ?: 'Not provided' }}</p>
                        </div>
                    </div>
                @else
                    <p class="text-gray-500 italic">Customer information not available</p>
                @endif
            </div>

            <!-- Invoice Information -->
            @if($payment->invoice)
                <div class="bg-white shadow-lg rounded-lg p-6">
                    <h3 class="font-semibold text-lg mb-4">Invoice Information</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between py-2 border-b">
                            <span class="text-gray-600">Invoice ID:</span>
                            <span class="font-mono">{{ $payment->invoice->invoice_id }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b">
                            <span class="text-gray-600">Invoice Number:</span>
                            <span class="font-mono">{{ $payment->invoice->invoice_number }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b">
                            <span class="text-gray-600">Issue Date:</span>
                            <span>{{ $payment->invoice->issue_date->format('M d, Y') }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b">
                            <span class="text-gray-600">Status:</span>
                            <span class="capitalize">{{ $payment->invoice->status }}</span>
                        </div>
                        @if($payment->invoice->sent_at)
                            <div class="flex justify-between py-2 border-b">
                                <span class="text-gray-600">Sent to Customer:</span>
                                <span>{{ $payment->invoice->sent_at->format('M d, Y h:i A') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Refund Information -->
            @if($payment->refund)
                <div class="bg-white shadow-lg rounded-lg p-6">
                    <h3 class="font-semibold text-lg mb-4">Refund Information</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between py-2 border-b">
                            <span class="text-gray-600">Refund ID:</span>
                            <span class="font-mono">{{ $payment->refund->refund_id }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b">
                            <span class="text-gray-600">Refund Amount:</span>
                            <span class="font-semibold">RM{{ number_format($payment->refund->amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b">
                            <span class="text-gray-600">Status:</span>
                            <span>
                                @if($payment->refund->status == 'approved')
                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-sm font-semibold">Approved</span>
                                @elseif($payment->refund->status == 'pending')
                                    <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-sm font-semibold">Pending</span>
                                @elseif($payment->refund->status == 'rejected')
                                    <span class="px-2 py-1 bg-red-100 text-red-800 rounded text-sm font-semibold">Rejected</span>
                                @endif
                            </span>
                        </div>
                        <div class="flex justify-between py-2 border-b">
                            <span class="text-gray-600">Requested:</span>
                            <span>{{ $payment->refund->requested_at->format('M d, Y h:i A') }}</span>
                        </div>
                        @if($payment->refund->processed_at)
                            <div class="flex justify-between py-2 border-b">
                                <span class="text-gray-600">Processed:</span>
                                <span>{{ $payment->refund->processed_at->format('M d, Y h:i A') }}</span>
                            </div>
                        @endif
                        <div class="pt-2">
                            <p class="text-sm text-gray-600 mb-1">Reason:</p>
                            <p class="text-gray-800 bg-gray-50 p-2 rounded">{{ $payment->refund->reason }}</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Print Button -->
            <div class="bg-white shadow-lg rounded-lg p-6">
                <h3 class="font-semibold text-lg mb-4">Actions</h3>
                <div class="space-y-3">
                    <button onclick="window.print()" 
                            class="w-full px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-md hover:bg-gray-700">
                        <i class="fas fa-print mr-2"></i>Print Payment Details
                    </button>
                    
                </div>
            </div>

            <!-- Payment Timeline -->
            <div class="bg-white shadow-lg rounded-lg p-6">
                <h3 class="font-semibold text-lg mb-4">Payment Timeline</h3>
                <div class="flow-root">
                    <ul role="list" class="-mb-8">
                        <!-- Payment Created -->
                        <li>
                            <div class="relative pb-8">
                                <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                <div class="relative flex space-x-3">
                                    <div>
                                        <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                                            <i class="fas fa-credit-card text-white text-xs"></i>
                                        </span>
                                    </div>
                                    <div class="min-w-0 flex-1 pt-1.5">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">Payment Created</p>
                                            <p class="text-xs text-gray-500">Payment initiated by customer</p>
                                        </div>
                                        <div class="mt-1 text-xs text-gray-400">
                                            {{ $payment->created_at->format('M d, Y h:i A') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>

                        <!-- Payment Processed -->
                        @if($payment->status !== 'pending')
                            <li>
                                <div class="relative pb-8">
                                    @if($payment->invoice || $payment->refund)
                                        <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                    @endif
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full 
                                                @if($payment->status == 'completed') bg-green-500
                                                @elseif($payment->status == 'failed') bg-red-500
                                                @else bg-yellow-500 @endif
                                                flex items-center justify-center ring-8 ring-white">
                                                @if($payment->status == 'completed')
                                                    <i class="fas fa-check text-white text-xs"></i>
                                                @elseif($payment->status == 'failed')
                                                    <i class="fas fa-times text-white text-xs"></i>
                                                @else
                                                    <i class="fas fa-clock text-white text-xs"></i>
                                                @endif
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1.5">
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">
                                                    Payment {{ ucfirst($payment->status) }}
                                                </p>
                                                <p class="text-xs text-gray-500">
                                                    @if($payment->status == 'completed')
                                                        Payment successfully processed
                                                    @elseif($payment->status == 'failed')
                                                        Payment processing failed
                                                    @else
                                                        Payment status updated
                                                    @endif
                                                </p>
                                            </div>
                                            <div class="mt-1 text-xs text-gray-400">
                                                {{ $payment->payment_date->format('M d, Y h:i A') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @endif

                        <!-- Invoice Generated -->
                        @if($payment->invoice)
                            <li>
                                <div class="relative pb-8">
                                    @if($payment->refund)
                                        <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                    @endif
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full bg-purple-500 flex items-center justify-center ring-8 ring-white">
                                                <i class="fas fa-file-invoice text-white text-xs"></i>
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1.5">
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">Invoice Generated</p>
                                                <p class="text-xs text-gray-500">Invoice #{{ $payment->invoice->invoice_number }}</p>
                                            </div>
                                            <div class="mt-1 text-xs text-gray-400">
                                                {{ $payment->invoice->issue_date->format('M d, Y h:i A') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @endif

                        <!-- Refund -->
                        @if($payment->refund)
                            <li>
                                <div class="relative">
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full bg-orange-500 flex items-center justify-center ring-8 ring-white">
                                                <i class="fas fa-undo text-white text-xs"></i>
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1.5">
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">Refund {{ ucfirst($payment->refund->status) }}</p>
                                                <p class="text-xs text-gray-500">RM{{ number_format($payment->refund->amount, 2) }}</p>
                                            </div>
                                            <div class="mt-1 text-xs text-gray-400">
                                                @if($payment->refund->processed_at)
                                                    {{ $payment->refund->processed_at->format('M d, Y h:i A') }}
                                                @else
                                                    {{ $payment->refund->requested_at->format('M d, Y h:i A') }}
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Print-specific styles */
    @media print {
        /* Hide elements that shouldn't be printed */
        .no-print,
        .sidebar,
        nav,
        .btn,
        button,
        .bg-indigo-600,
        .bg-blue-600,
        .bg-gray-600 {
            display: none !important;
        }
        
        /* Show print-only elements */
        .print-only {
            display: block !important;
        }
        
        /* Adjust layout for print */
        body {
            background: white !important;
            color: black !important;
            font-size: 12pt;
            line-height: 1.4;
        }
        
        .container {
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        
        .grid {
            display: block !important;
        }
        
        .lg\:col-span-2 {
            width: 100% !important;
        }
        
        /* Ensure proper spacing */
        .space-y-6 > * + * {
            margin-top: 1.5rem !important;
        }
        
        /* Card styling for print */
        .bg-white {
            background: white !important;
            box-shadow: none !important;
            border: 1px solid #d1d5db !important;
            margin-bottom: 1rem !important;
            page-break-inside: avoid;
        }
        
        .shadow-lg {
            box-shadow: none !important;
        }
        
        /* Typography adjustments */
        h1, h2, h3 {
            color: black !important;
            page-break-after: avoid;
        }
        
        /* Table and data styling */
        .space-y-2 > * + * {
            margin-top: 0.5rem !important;
        }
        
        .border-b {
            border-bottom: 1px solid #d1d5db !important;
        }
        
        /* Timeline adjustments */
        .flow-root ul {
            list-style: none;
        }
        
        /* Status badges */
        .rounded-full {
            border: 1px solid #d1d5db !important;
            color: black !important;
            background: white !important;
        }
        
        /* Links */
        a {
            color: black !important;
            text-decoration: underline;
        }
        
        /* Page breaks */
        .page-break {
            page-break-before: always;
        }
        
        /* Font adjustments */
        .font-mono {
            font-family: 'Courier New', monospace;
        }
        
        /* Hide background colors */
        .bg-gray-50,
        .bg-gray-100,
        .bg-green-100,
        .bg-blue-100,
        .bg-yellow-100,
        .bg-red-100,
        .bg-purple-100,
        .bg-orange-100 {
            background: white !important;
            border: 1px solid #d1d5db !important;
        }
    }
    
    /* Hide print-only elements on screen */
    .print-only {
        display: none;
    }
    
    /* Screen-only styles */
    @media screen {
        .no-print {
            display: block;
        }
    }
</style>
@endsection