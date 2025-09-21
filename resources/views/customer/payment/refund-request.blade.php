@extends('layouts.customerLayout')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Request Refund</h1>

    <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-md p-6">
        <div class="mb-6">
            <h2 class="text-xl font-semibold mb-4">Payment Information</h2>
            <div class="bg-gray-50 rounded p-4 space-y-2">
                <div class="flex justify-between">
                    <span class="text-gray-600">Payment ID:</span>
                    <span class="font-mono">{{ $payment->payment_id }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Package ID:</span>
                    <span class="font-mono">{{ $payment->package_id }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Amount:</span>
                    <span class="font-semibold">RM{{ number_format($payment->amount, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Payment Date:</span>
                    <span>{{ $payment->payment_date->format('M d, Y') }}</span>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('customer.refund.submit', $payment->payment_id) }}">
            @csrf
            <div class="mb-6">
                <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">Reason for Refund *</label>
                <select name="reason" id="reason" class="w-full rounded-md border-gray-300 shadow-sm" required>
                    <option value="">Select a reason</option>
                    @foreach($refundReasons as $value => $label)
                        <option value="{{ $value }}" {{ old('reason') == $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('reason')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="additional_info" class="block text-sm font-medium text-gray-700 mb-2">Additional Information</label>
                <textarea name="additional_info" id="additional_info" rows="4" 
                          class="w-full rounded-md border-gray-300 shadow-sm"
                          placeholder="Please provide any additional details about your refund request...">{{ old('additional_info') }}</textarea>
                @error('additional_info')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="bg-yellow-50 border border-yellow-200 rounded p-4 mb-6">
                <h4 class="font-semibold text-yellow-800 mb-2">Refund Policy</h4>
                <ul class="text-sm text-yellow-700 space-y-1">
                    <li>• Refunds are processed within 7-14 business days</li>
                    <li>• Refunds are only available for payments made within the last 7 days</li>
                    <li>• Packages that are already in transit or delivered cannot be refunded</li>
                    <li>• Processing fees may apply depending on the payment method</li>
                </ul>
            </div>

            <div class="flex justify-between">
                <a href="{{ route('customer.billing.history') }}" class="text-gray-600 hover:text-gray-800">
                    ← Back to Billing History
                </a>
                <button type="submit" class="bg-orange-600 text-white px-6 py-3 rounded-md hover:bg-orange-700">
                    Submit Refund Request
                </button>
            </div>
        </form>
    </div>
</div>
@endsection