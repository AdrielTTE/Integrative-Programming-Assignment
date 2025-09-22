@extends('layouts.customerLayout')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Complete Payment</h1>

    {{-- Error Messages --}}
    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        {{-- Package Summary --}}
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Package Summary</h2>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-600">Contents:</span>
                    <span>{{ $packageData['package_contents'] }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Weight:</span>
                    <span>{{ $packageData['package_weight'] }} kg</span>
                </div>
                @if(isset($packageData['package_dimensions']))
                    <div class="flex justify-between">
                        <span class="text-gray-600">Dimensions:</span>
                        <span>{{ $packageData['package_dimensions'] }}</span>
                    </div>
                @endif
                <div class="flex justify-between">
                    <span class="text-gray-600">Priority:</span>
                    <span class="capitalize">{{ $packageData['priority'] }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">From:</span>
                    <span class="text-sm">{{ $packageData['sender_address'] }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">To:</span>
                    <span class="text-sm">{{ $packageData['recipient_address'] }}</span>
                </div>
                @if(isset($packageData['notes']) && $packageData['notes'])
                    <div class="flex justify-between">
                        <span class="text-gray-600">Notes:</span>
                        <span class="text-sm">{{ $packageData['notes'] }}</span>
                    </div>
                @endif
            </div>

            <hr class="my-4">

            {{-- Total Amount --}}
            <div class="flex justify-between text-lg font-bold">
                <span>Total Amount:</span>
                <span class="text-indigo-600">RM{{ number_format($baseCost, 2) }}</span>
            </div>
        </div>

        {{-- Payment Form --}}
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Payment Details</h2>

            <form method="POST" action="{{ route('customer.payment.processSessionPayment') }}" id="paymentForm">
                @csrf
                
                {{-- Payment Method Selection --}}
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">Payment Method</label>
                    <div class="space-y-3">
                        @foreach($paymentMethods as $key => $method)
                            <label class="flex items-center cursor-pointer hover:bg-gray-50 p-2 rounded">
                                <input type="radio" 
                                       name="payment_method" 
                                       value="{{ $key }}" 
                                       {{ old('payment_method', 'credit_card') == $key ? 'checked' : '' }}
                                       class="mr-3 text-indigo-600 focus:ring-indigo-500" 
                                       required>
                                <span>{{ $method }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('payment_method')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Card Details Section --}}
                <div id="cardDetails" class="space-y-4">
                    <div>
                        <label for="card_number" class="block text-sm font-medium text-gray-700">Card Number</label>
                        <input type="text" 
                               name="card_number" 
                               id="card_number" 
                               value="{{ old('card_number') }}"
                               placeholder="1234 5678 9012 3456"
                               maxlength="19"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('card_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="card_name" class="block text-sm font-medium text-gray-700">Cardholder Name</label>
                        <input type="text" 
                               name="card_name" 
                               id="card_name" 
                               value="{{ old('card_name') }}"
                               placeholder="John Doe"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('card_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="card_expiry" class="block text-sm font-medium text-gray-700">Expiry Date</label>
                            <input type="text" 
                                   name="card_expiry" 
                                   id="card_expiry" 
                                   value="{{ old('card_expiry') }}"
                                   placeholder="MM/YY"
                                   maxlength="5"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('card_expiry')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="card_cvv" class="block text-sm font-medium text-gray-700">CVV</label>
                            <input type="text" 
                                   name="card_cvv" 
                                   id="card_cvv" 
                                   value="{{ old('card_cvv') }}"
                                   placeholder="123"
                                   maxlength="3"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('card_cvv')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Online Banking Section --}}
                <div id="bankingDetails" class="hidden">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <svg class="w-6 h-6 text-blue-600 mt-1 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <p class="text-blue-800 font-medium">Online Banking Selected</p>
                                <p class="text-blue-600 text-sm mt-1">You will be redirected to your bank's secure payment page after clicking Pay.</p>
                                <p class="text-blue-600 text-sm mt-2">Supported banks: Maybank, CIMB, Public Bank, RHB, and more.</p>
                            </div>
                        </div>
                    </div>
                    {{-- Hidden input to prevent validation issues --}}
                    <input type="hidden" name="bank_name" value="online_banking">
                </div>

                {{-- E-Wallet Section --}}
                <div id="walletDetails" class="hidden">
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <svg class="w-6 h-6 text-green-600 mt-1 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                            </svg>
                            <div>
                                <p class="text-green-800 font-medium">E-Wallet Selected</p>
                                <p class="text-green-600 text-sm mt-1">You will be redirected to your e-wallet provider after clicking Pay.</p>
                                <p class="text-green-600 text-sm mt-2">Supported wallets: GrabPay, Touch 'n Go, Boost, ShopeePay.</p>
                            </div>
                        </div>
                    </div>
                    {{-- Hidden input to prevent validation issues --}}
                    <input type="hidden" name="wallet_provider" value="e_wallet">
                </div>

                {{-- Submit Buttons --}}
                <div class="mt-6 flex justify-between items-center">
                    <a href="{{ route('customer.packages.create') }}" 
                       class="text-gray-600 hover:text-gray-800 flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        Back to Create Package
                    </a>
                    <button type="submit"
                            id="payButton"
                            class="bg-indigo-600 text-white px-8 py-3 rounded-md hover:bg-indigo-700 font-semibold transition duration-150 ease-in-out flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        Pay RM{{ number_format($baseCost, 2) }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
    const cardDetails = document.getElementById('cardDetails');
    const bankingDetails = document.getElementById('bankingDetails');
    const walletDetails = document.getElementById('walletDetails');

    function togglePaymentFields() {
        const selectedMethod = document.querySelector('input[name="payment_method"]:checked');
        
        if (!selectedMethod) return;
        
        const methodValue = selectedMethod.value;
        
        // Hide all detail sections first
        cardDetails.style.display = 'none';
        bankingDetails.style.display = 'none';
        walletDetails.style.display = 'none';
        
        // Remove required attributes from all card fields
        const cardInputs = cardDetails.querySelectorAll('input');
        cardInputs.forEach(input => {
            input.removeAttribute('required');
            // Clear values when switching away from card payment
            if (methodValue !== 'credit_card' && methodValue !== 'debit_card') {
                // Don't clear if there's an old value (for validation errors)
                if (!input.hasAttribute('data-old-value')) {
                    input.value = '';
                }
            }
        });
        
        // Show relevant section based on payment method
        if (methodValue === 'credit_card' || methodValue === 'debit_card') {
            cardDetails.style.display = 'block';
            // Make card fields required
            document.getElementById('card_number').setAttribute('required', 'required');
            document.getElementById('card_name').setAttribute('required', 'required');
            document.getElementById('card_expiry').setAttribute('required', 'required');
            document.getElementById('card_cvv').setAttribute('required', 'required');
        } else if (methodValue === 'online_banking') {
            bankingDetails.style.display = 'block';
        } else if (methodValue === 'e_wallet') {
            walletDetails.style.display = 'block';
        }
    }

    // Add event listeners
    paymentMethods.forEach(method => {
        method.addEventListener('change', togglePaymentFields);
    });

    // Initialize on page load
    togglePaymentFields();

    // Card number formatting
    const cardNumberInput = document.getElementById('card_number');
    if (cardNumberInput) {
        cardNumberInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s/g, '');
            let numericValue = value.replace(/\D/g, '');
            
            if (numericValue.length > 16) {
                numericValue = numericValue.substring(0, 16);
            }
            
            let formattedValue = numericValue.match(/.{1,4}/g)?.join(' ') || numericValue;
            e.target.value = formattedValue;
        });

        // Prevent non-numeric input
        cardNumberInput.addEventListener('keypress', function(e) {
            const char = String.fromCharCode(e.which);
            const currentValue = e.target.value.replace(/\s/g, '');
            
            // Allow backspace, delete, tab, etc.
            if (e.which === 8 || e.which === 46 || e.which === 9 || e.which === 37 || e.which === 39) {
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
    }

    // Expiry formatting
    const cardExpiryInput = document.getElementById('card_expiry');
    if (cardExpiryInput) {
        cardExpiryInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            e.target.value = value;
        });

        cardExpiryInput.addEventListener('keypress', function(e) {
            const char = String.fromCharCode(e.which);
            const currentValue = e.target.value.replace(/\D/g, '');
            
            // Allow control keys
            if (e.which === 8 || e.which === 46 || e.which === 9) {
                return true;
            }
            
            // Stop input if already 4 digits
            if (currentValue.length >= 4) {
                e.preventDefault();
                return false;
            }
            
            // Only allow digits
            if (!/\d/.test(char)) {
                e.preventDefault();
                return false;
            }
        });
    }

    // CVV validation
    const cardCvvInput = document.getElementById('card_cvv');
    if (cardCvvInput) {
        cardCvvInput.addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/\D/g, '').substring(0, 3);
        });

        cardCvvInput.addEventListener('keypress', function(e) {
            const char = String.fromCharCode(e.which);
            
            // Allow control keys
            if (e.which === 8 || e.which === 46 || e.which === 9) {
                return true;
            }
            
            // Stop input if already 3 digits
            if (e.target.value.length >= 3) {
                e.preventDefault();
                return false;
            }
            
            // Only allow digits
            if (!/\d/.test(char)) {
                e.preventDefault();
                return false;
            }
        });
    }

    // Form submission handling
    const paymentForm = document.getElementById('paymentForm');
    const payButton = document.getElementById('payButton');
    
    paymentForm.addEventListener('submit', function(e) {
        // Disable button to prevent double submission
        payButton.disabled = true;
        payButton.innerHTML = `
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Processing...
        `;
    });
});
</script>
@endsection