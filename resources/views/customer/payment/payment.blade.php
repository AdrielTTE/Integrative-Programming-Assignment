@extends('layouts.customerLayout')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Complete Payment</h1>

    @if(session('package_created'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            <p class="font-semibold">Package Created Successfully!</p>
            <p>Please complete your payment to process the delivery request.</p>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Package Summary -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Package Summary</h2>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-600">Package ID:</span>
                    <span class="font-mono">{{ $package->package_id }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Tracking Number:</span>
                    <span class="font-mono">{{ $package->tracking_number }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Contents:</span>
                    <span>{{ $package->package_contents }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Weight:</span>
                    <span>{{ $package->package_weight }} kg</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Priority:</span>
                    <span class="capitalize">{{ $package->priority }}</span>
                </div>
            </div>

            <hr class="my-4">

            <!-- Cost Breakdown -->
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span>Base Cost:</span>
                    <span>RM{{ number_format($baseCost, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Tax (6%):</span>
                    <span>RM{{ number_format($tax, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Service Fee:</span>
                    <span>RM{{ number_format($serviceFee, 2) }}</span>
                </div>
                <hr>
                <div class="flex justify-between text-lg font-bold">
                    <span>Total Amount:</span>
                    <span>RM{{ number_format($totalCost, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Payment Form -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Payment Details</h2>

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('customer.payment.process', $package->package_id) }}" id="paymentForm">
                @csrf
                
                <!-- Payment Method -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">Payment Method</label>
                    <div class="space-y-3">
                        @foreach($paymentMethods as $key => $method)
                            <label class="flex items-center">
                                <input type="radio" name="payment_method" value="{{ $key }}" 
                                       {{ old('payment_method', 'credit_card') == $key ? 'checked' : '' }}
                                       class="mr-3" required>
                                <span>{{ $method }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('payment_method')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Card Details -->
                <div id="cardDetails" class="space-y-4">
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label for="card_number" class="block text-sm font-medium text-gray-700">Card Number</label>
                            <input type="text" name="card_number" id="card_number" 
                                   value="{{ old('card_number') }}" placeholder="1234 5678 9012 3456"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('card_number')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="card_name" class="block text-sm font-medium text-gray-700">Cardholder Name</label>
                            <input type="text" name="card_name" id="card_name" 
                                   value="{{ old('card_name') }}" placeholder="John Doe"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('card_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="card_expiry" class="block text-sm font-medium text-gray-700">Expiry Date</label>
                                <input type="text" name="card_expiry" id="card_expiry" 
                                       value="{{ old('card_expiry') }}" placeholder="MM/YY"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('card_expiry')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="card_cvv" class="block text-sm font-medium text-gray-700">CVV</label>
                                <input type="text" name="card_cvv" id="card_cvv" 
                                       value="{{ old('card_cvv') }}" placeholder="123"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('card_cvv')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Other Payment Method Fields -->
                    <div id="bankingDetails" class="hidden">
                        <div>
                            <label for="bank_name" class="block text-sm font-medium text-gray-700">Bank</label>
                            <select name="bank_name" id="bank_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Select Bank</option>
                                <option value="maybank">Maybank</option>
                                <option value="cimb">CIMB Bank</option>
                                <option value="public_bank">Public Bank</option>
                                <option value="rhb">RHB Bank</option>
                            </select>
                        </div>
                    </div>

                    <div id="walletDetails" class="hidden">
                        <div>
                            <label for="wallet_provider" class="block text-sm font-medium text-gray-700">E-Wallet Provider</label>
                            <select name="wallet_provider" id="wallet_provider" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Select Wallet</option>
                                <option value="grabpay">GrabPay</option>
                                <option value="tng">Touch 'n Go eWallet</option>
                                <option value="boost">Boost</option>
                                <option value="shopeepay">ShopeePay</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-between items-center">
                    <a href="{{ route('customer.packages.show', $package->package_id) }}" 
                       class="text-gray-600 hover:text-gray-800">← Back to Package</a>
                    <button type="submit" id="payButton"
                            class="bg-indigo-600 text-white px-8 py-3 rounded-md hover:bg-indigo-700 font-semibold">
                        Pay RM{{ number_format($totalCost, 2) }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<style>
.card-input-error {
    border-color: #ef4444 !important;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1) !important;
}

.card-input-valid {
    border-color: #10b981 !important;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1) !important;
}
</style>
<script>

    document.getElementById('card_number').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\s/g, ''); // Remove all spaces
    let numericValue = value.replace(/\D/g, ''); // Remove non-digits
    
    // Limit to exactly 16 digits
    if (numericValue.length > 16) {
        numericValue = numericValue.substring(0, 16);
    }
    
    // Format with spaces every 4 digits
    let formattedValue = numericValue.match(/.{1,4}/g)?.join(' ') || numericValue;
    
    // Update the input value
    e.target.value = formattedValue;
    
    // Remove any existing error styling
    e.target.classList.remove('border-red-500');
    
    // Hide error message if exactly 16 digits
    if (numericValue.length === 16) {
        const errorMsg = e.target.parentElement.querySelector('.text-red-600');
        if (errorMsg) {
            errorMsg.style.display = 'none';
        }
    }
});

// Prevent non-numeric input (except spaces)
document.getElementById('card_number').addEventListener('keypress', function(e) {
    const char = String.fromCharCode(e.which);
    const currentValue = e.target.value.replace(/\s/g, '');
    
    // Allow backspace, delete, tab, etc.
    if (e.which === 8 || e.which === 46 || e.which === 9) {
        return true;
    }
    
    // Stop input if already 16 digits
    if (currentValue.length >= 16) {
        e.preventDefault();
        return false;
    }
    
    // Only allow digits
    if (!/\d/.test(char)) {
        e.preventDefault();
        return false;
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
    const cardDetails = document.getElementById('cardDetails');
    const bankingDetails = document.getElementById('bankingDetails');
    const walletDetails = document.getElementById('walletDetails');

    function togglePaymentFields() {
        const selectedMethod = document.querySelector('input[name="payment_method"]:checked').value;
        
        // Hide all detail sections
        cardDetails.style.display = 'none';
        bankingDetails.style.display = 'none';
        walletDetails.style.display = 'none';
        
        // Show relevant section
        if (selectedMethod === 'credit_card' || selectedMethod === 'debit_card') {
            cardDetails.style.display = 'block';
        } else if (selectedMethod === 'online_banking') {
            bankingDetails.style.display = 'block';
        } else if (selectedMethod === 'e_wallet') {
            walletDetails.style.display = 'block';
        }
    }

    paymentMethods.forEach(method => {
        method.addEventListener('change', togglePaymentFields);
    });

    // Initialize on page load
    togglePaymentFields();

    // Format card number
    document.getElementById('card_number').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\s/g, '');
        let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
        if (formattedValue.length <= 19) {
            e.target.value = formattedValue;
        }
    });

    // Format expiry
    document.getElementById('card_expiry').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length >= 2) {
            value = value.substring(0, 2) + '/' + value.substring(2, 4);
        }
        e.target.value = value;
    });
});
</script>
@endsection