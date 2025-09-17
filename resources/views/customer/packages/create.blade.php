@extends('layouts.customerLayout')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Create Delivery Request</h1>

    <div class="bg-white rounded-lg shadow-md p-6">
        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                <p class="font-bold">Success</p>
                <p>{{ session('success') }}</p>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                <p class="font-bold">Error</p>
                <p>{{ session('error') }}</p>
            </div>
        @endif

        <form action="{{ route('customer.packages.store') }}" method="POST" id="createPackageForm">
            @csrf
            
            <!-- Hidden fields for payment integration -->
            <input type="hidden" name="payment_transaction_id" id="payment_transaction_id">
            <input type="hidden" name="payment_amount" id="payment_amount">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Package Details -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-700">Package Information</h3>
                    
                    <div>
                        <label for="package_contents" class="block text-sm font-medium text-gray-700">Contents Description *</label>
                        <textarea name="package_contents" id="package_contents" rows="3" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                                required>{{ old('package_contents') }}</textarea>
                        @error('package_contents')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="package_weight" class="block text-sm font-medium text-gray-700">Weight (kg) *</label>
                            <input type="number" name="package_weight" id="package_weight" step="0.01" min="0.01"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                                   value="{{ old('package_weight') }}" required>
                            @error('package_weight')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="package_dimensions" class="block text-sm font-medium text-gray-700">Dimensions (LxWxH cm)</label>
                            <input type="text" name="package_dimensions" id="package_dimensions" placeholder="e.g., 30x20x10"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                                   value="{{ old('package_dimensions') }}">
                            @error('package_dimensions')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="priority" class="block text-sm font-medium text-gray-700">Delivery Priority *</label>
                        <select name="priority" id="priority" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            @foreach($priorities as $value => $label)
                                <option value="{{ $value }}" {{ old('priority') == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('priority')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Addresses -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-700">Delivery Addresses</h3>
                    
                    <div>
                        <label for="sender_address" class="block text-sm font-medium text-gray-700">Pickup Address *</label>
                        <textarea name="sender_address" id="sender_address" rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                                required>{{ old('sender_address') }}</textarea>
                        @error('sender_address')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="recipient_address" class="block text-sm font-medium text-gray-700">Delivery Address *</label>
                        <textarea name="recipient_address" id="recipient_address" rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                                required>{{ old('recipient_address') }}</textarea>
                        @error('recipient_address')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700">Special Instructions</label>
                        <textarea name="notes" id="notes" rows="2"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                                placeholder="Any special handling instructions...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Cost Estimation -->
            <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                <h4 class="text-md font-semibold text-gray-700 mb-2">Estimated Cost</h4>
                <div id="costEstimation" class="text-lg font-bold text-indigo-600">
                    Calculate cost based on weight and priority
                </div>
                <small class="text-gray-500">You will pay this amount before creating the delivery request</small>
            </div>

            <!-- Form Actions -->
            <div class="mt-6 flex justify-end space-x-4">
                <a href="{{ route('customer.packages.index') }}" 
                   class="px-6 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancel
                </a>
                
                <!-- Show different buttons based on whether payment is required -->
                <button type="button" id="proceedToPayment"
                        class="px-6 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Proceed to Payment
                </button>
                
                <!-- Hidden submit button for direct form submission after payment -->
                <button type="submit" id="directSubmit" style="display: none;">
                    Create Delivery Request
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Payment Modal -->
<div id="paymentModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Complete Payment
                        </h3>
                        <div class="mt-4">
                            <div class="bg-gray-50 p-4 rounded-lg mb-4">
                                <p class="text-sm text-gray-600">Amount to Pay:</p>
                                <p class="text-2xl font-bold text-indigo-600" id="paymentAmount">RM0.00</p>
                            </div>

                            <!-- Payment Method Selection -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Select Payment Method</label>
                                <div class="space-y-2">
                                    <label class="flex items-center">
                                        <input type="radio" name="payment_method" value="card" checked class="mr-2">
                                        <span>Credit/Debit Card</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" name="payment_method" value="paypal" class="mr-2">
                                        <span>PayPal</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" name="payment_method" value="wallet" class="mr-2">
                                        <span>Digital Wallet</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Card Details (shown when card is selected) -->
                            <div id="cardDetails" class="space-y-3">
                                <div>
                                    <label for="card_number" class="block text-sm font-medium text-gray-700">Card Number</label>
                                    <input type="text" id="card_number" placeholder="1234 5678 9012 3456" maxlength="19"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="card_expiry" class="block text-sm font-medium text-gray-700">Expiry</label>
                                        <input type="text" id="card_expiry" placeholder="MM/YY" maxlength="5"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                    <div>
                                        <label for="card_cvv" class="block text-sm font-medium text-gray-700">CVV</label>
                                        <input type="text" id="card_cvv" placeholder="123" maxlength="3"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                </div>
                                <div>
                                    <label for="card_holder" class="block text-sm font-medium text-gray-700">Cardholder Name</label>
                                    <input type="text" id="card_holder" placeholder="John Doe"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                            </div>

                            <!-- Payment Status Messages -->
                            <div id="paymentStatus" class="hidden mt-4">
                                <div id="paymentProcessing" class="hidden">
                                    <div class="flex items-center">
                                        <svg class="animate-spin h-5 w-5 mr-3 text-indigo-600" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Processing payment...
                                    </div>
                                </div>
                                <div id="paymentSuccess" class="hidden bg-green-100 p-4 rounded">
                                    <p class="text-green-700 font-semibold">✓ Payment successful!</p>
                                    <p class="text-sm text-green-600 mt-1">Creating delivery request...</p>
                                </div>
                                <div id="paymentError" class="hidden bg-red-100 p-4 rounded">
                                    <p class="text-red-700 font-semibold">✗ Payment failed</p>
                                    <p class="text-sm text-red-600 mt-1" id="paymentErrorMessage">Please try again</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" id="confirmPayment"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Pay Now
                </button>
                <button type="button" id="cancelPayment"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Payment Facade Pattern Implementation
class PaymentFacade {
    constructor() {
        this.paymentGateways = {
            card: new CardPaymentGateway(),
            paypal: new PayPalGateway(),
            wallet: new DigitalWalletGateway()
        };
        this.currentAmount = 0;
        this.transactionId = null;
    }

    setAmount(amount) {
        this.currentAmount = amount;
    }

    async processPayment(method, details) {
        try {
            const gateway = this.paymentGateways[method];
            if (!gateway) {
                throw new Error('Invalid payment method');
            }

            // Validate payment details
            const isValid = await gateway.validate(details);
            if (!isValid) {
                throw new Error('Invalid payment details');
            }

            // Process payment
            const result = await gateway.processPayment(this.currentAmount, details);
            
            if (result.success) {
                this.transactionId = result.transactionId;
                return {
                    success: true,
                    transactionId: result.transactionId
                };
            } else {
                throw new Error(result.error || 'Payment failed');
            }
        } catch (error) {
            return {
                success: false,
                error: error.message
            };
        }
    }

    getTransactionId() {
        return this.transactionId;
    }
}

// Payment Gateway Implementations
class CardPaymentGateway {
    async validate(details) {
        // Validate card number (basic validation)
        const cardNumber = details.cardNumber.replace(/\s/g, '');
        if (!/^\d{16}$/.test(cardNumber)) return false;
        
        // Validate expiry
        if (!/^\d{2}\/\d{2}$/.test(details.expiry)) return false;
        
        // Validate CVV
        if (!/^\d{3}$/.test(details.cvv)) return false;
        
        // Validate cardholder name
        if (!details.cardHolder || details.cardHolder.trim().length < 2) return false;
        
        return true;
    }

    async processPayment(amount, details) {
        // Simulate API call to payment processor
        return new Promise((resolve) => {
            setTimeout(() => {
                // Simulate 90% success rate
                if (Math.random() > 0.1) {
                    resolve({
                        success: true,
                        transactionId: 'TXN' + Date.now() + Math.floor(Math.random() * 1000)
                    });
                } else {
                    resolve({
                        success: false,
                        error: 'Card declined'
                    });
                }
            }, 2000);
        });
    }
}

class PayPalGateway {
    async validate(details) {
        return true; // PayPal handles its own validation
    }

    async processPayment(amount, details) {
        // Simulate PayPal payment
        return new Promise((resolve) => {
            setTimeout(() => {
                resolve({
                    success: true,
                    transactionId: 'PP' + Date.now() + Math.floor(Math.random() * 1000)
                });
            }, 1500);
        });
    }
}

class DigitalWalletGateway {
    async validate(details) {
        return true; // Wallet handles its own validation
    }

    async processPayment(amount, details) {
        // Simulate digital wallet payment
        return new Promise((resolve) => {
            setTimeout(() => {
                resolve({
                    success: true,
                    transactionId: 'DW' + Date.now() + Math.floor(Math.random() * 1000)
                });
            }, 1000);
        });
    }
}

// Initialize Payment System
document.addEventListener('DOMContentLoaded', function() {
    const paymentFacade = new PaymentFacade();
    const form = document.getElementById('createPackageForm');
    const weightInput = document.getElementById('package_weight');
    const prioritySelect = document.getElementById('priority');
    const costEstimation = document.getElementById('costEstimation');
    const proceedButton = document.getElementById('proceedToPayment');
    const paymentModal = document.getElementById('paymentModal');
    const confirmPaymentBtn = document.getElementById('confirmPayment');
    const cancelPaymentBtn = document.getElementById('cancelPayment');
    let estimatedCost = 0;

    // Cost calculation function (combined from both versions)
    function updateCostEstimation() {
        const weight = parseFloat(weightInput.value) || 0;
        const priority = prioritySelect.value;
        
        if (weight > 0) {
            let baseCost = 8.00; // Using the original base cost
            let weightCost = weight * 3.50; // Using the original weight multiplier
            let priorityMultiplier = priority === 'express' ? 1.5 : priority === 'urgent' ? 2 : 1;
            
            estimatedCost = (baseCost + weightCost) * priorityMultiplier;
            costEstimation.textContent = `RM${estimatedCost.toFixed(2)}`;
        } else {
            estimatedCost = 0;
            costEstimation.textContent = 'Enter weight to calculate';
        }
    }

    // Payment method change handler
    document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
        radio.addEventListener('change', function() {
            document.getElementById('cardDetails').style.display = 
                this.value === 'card' ? 'block' : 'none';
        });
    });

    // Format card number input
    document.getElementById('card_number').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\s/g, '');
        let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
        if (formattedValue.length <= 19) {
            e.target.value = formattedValue;
        }
    });

    // Format expiry input
    document.getElementById('card_expiry').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length >= 2) {
            value = value.substring(0, 2) + '/' + value.substring(2, 4);
        }
        e.target.value = value;
    });

    // CVV input validation
    document.getElementById('card_cvv').addEventListener('input', function(e) {
        e.target.value = e.target.value.replace(/\D/g, '');
    });

    // Proceed to payment button handler
    proceedButton.addEventListener('click', function() {
        // Validate form first
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        if (estimatedCost <= 0) {
            alert('Please enter package weight to calculate cost');
            return;
        }

        // Show payment modal
        document.getElementById('paymentAmount').textContent = `RM${estimatedCost.toFixed(2)}`;
        paymentFacade.setAmount(estimatedCost);
        paymentModal.classList.remove('hidden');
    });

    // Confirm payment handler
    confirmPaymentBtn.addEventListener('click', async function() {
        const selectedMethod = document.querySelector('input[name="payment_method"]:checked').value;
        let paymentDetails = {};

        if (selectedMethod === 'card') {
            paymentDetails = {
                cardNumber: document.getElementById('card_number').value,
                expiry: document.getElementById('card_expiry').value,
                cvv: document.getElementById('card_cvv').value,
                cardHolder: document.getElementById('card_holder').value
            };
        }

        // Show processing status
        document.getElementById('paymentStatus').classList.remove('hidden');
        document.getElementById('paymentProcessing').classList.remove('hidden');
        document.getElementById('paymentSuccess').classList.add('hidden');
        document.getElementById('paymentError').classList.add('hidden');
        confirmPaymentBtn.disabled = true;
        cancelPaymentBtn.disabled = true;

        // Process payment through facade
        const result = await paymentFacade.processPayment(selectedMethod, paymentDetails);

        document.getElementById('paymentProcessing').classList.add('hidden');

        if (result.success) {
            // Show success message
            document.getElementById('paymentSuccess').classList.remove('hidden');
            
            // Set transaction details in form
            document.getElementById('payment_transaction_id').value = result.transactionId;
            document.getElementById('payment_amount').value = estimatedCost.toFixed(2);

            // Submit form after delay
            setTimeout(() => {
                paymentModal.classList.add('hidden');
                form.submit();
            }, 2000);
        } else {
            // Show error message
            document.getElementById('paymentError').classList.remove('hidden');
            document.getElementById('paymentErrorMessage').textContent = result.error;
            confirmPaymentBtn.disabled = false;
            cancelPaymentBtn.disabled = false;
        }
    });

    // Cancel payment handler
    cancelPaymentBtn.addEventListener('click', function() {
        paymentModal.classList.add('hidden');
        resetPaymentModal();
    });

    // Function to reset payment modal
    function resetPaymentModal() {
        // Reset payment status
        document.getElementById('paymentStatus').classList.add('hidden');
        document.getElementById('paymentProcessing').classList.add('hidden');
        document.getElementById('paymentSuccess').classList.add('hidden');
        document.getElementById('paymentError').classList.add('hidden');
        confirmPaymentBtn.disabled = false;
        cancelPaymentBtn.disabled = false;
        
        // Clear card details
        document.getElementById('card_number').value = '';
        document.getElementById('card_expiry').value = '';
        document.getElementById('card_cvv').value = '';
        document.getElementById('card_holder').value = '';
        
        // Reset to card payment method
        document.querySelector('input[name="payment_method"][value="card"]').checked = true;
        document.getElementById('cardDetails').style.display = 'block';
    }

    // Close modal when clicking outside
    paymentModal.addEventListener('click', function(e) {
        if (e.target === paymentModal) {
            cancelPaymentBtn.click();
        }
    });

    // Initialize cost estimation event listeners
    weightInput.addEventListener('input', updateCostEstimation);
    prioritySelect.addEventListener('change', updateCostEstimation);
    
    // Initialize cost estimation on page load
    updateCostEstimation();
});
</script>

<style>
/* Payment modal styles */
#paymentModal input {
    border: 1px solid #d1d5db;
    padding: 0.5rem;
}

#paymentModal input:focus {
    outline: none;
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.animate-spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* Ensure modal is properly styled */
#paymentModal .space-y-2 > * + * {
    margin-top: 0.5rem;
}

#paymentModal .space-y-3 > * + * {
    margin-top: 0.75rem;
}

/* Card details visibility */
#cardDetails {
    display: block;
}
</style>
@endsection