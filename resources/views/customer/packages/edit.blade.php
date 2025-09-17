@extends('layouts.customerLayout')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Edit Delivery Request</h1>

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

        <form action="{{ route('customer.packages.update', $package->package_id) }}" method="POST" id="editPackageForm">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Package Details -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-700">Package Information</h3>
                    
                    <div>
                        <label for="package_contents" class="block text-sm font-medium text-gray-700">Contents Description *</label>
                        <textarea name="package_contents" id="package_contents" rows="3" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                                required>{{ old('package_contents', $package->package_contents) }}</textarea>
                        @error('package_contents')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="package_weight" class="block text-sm font-medium text-gray-700">Weight (kg) *</label>
                            <input type="number" name="package_weight" id="package_weight" step="0.01" min="0.01"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-100 cursor-not-allowed" 
                                value="{{ old('package_weight', $package->package_weight) }}" readonly>
                            <small class="text-gray-500">Weight cannot be changed after creation. Delete package and add a new one instead.</small>
                            @error('package_weight')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="package_dimensions" class="block text-sm font-medium text-gray-700">Dimensions (LxWxH cm)</label>
                            <input type="text" name="package_dimensions" id="package_dimensions" placeholder="e.g., 30x20x10"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                                   value="{{ old('package_dimensions', $package->package_dimensions) }}">
                            @error('package_dimensions')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="priority" class="block text-sm font-medium text-gray-700">Delivery Priority *</label>
                        <select name="priority" id="priority" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-100 cursor-not-allowed" 
                                disabled required>
                            @foreach($priorities as $value => $label)
                                <option value="{{ $value }}" {{ old('priority', $package->priority) == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-gray-500">Priority cannot be changed after creation. Delete package and add a new one instead.</small>
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
                                required>{{ old('sender_address', $package->sender_address) }}</textarea>
                        @error('sender_address')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="recipient_address" class="block text-sm font-medium text-gray-700">Delivery Address *</label>
                        <textarea name="recipient_address" id="recipient_address" rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                                required>{{ old('recipient_address', $package->recipient_address) }}</textarea>
                        @error('recipient_address')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700">Special Instructions</label>
                        <textarea name="notes" id="notes" rows="2"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                                placeholder="Any special handling instructions...">{{ old('notes', $package->notes) }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Cost Display (No longer editable) -->
            <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                <h4 class="text-md font-semibold text-gray-700 mb-2">Package Cost</h4>
                <div id="costDisplay" class="text-lg font-bold text-indigo-600">
                    RM{{ number_format($package->shipping_cost, 2) }}
                </div>
                <small class="text-gray-500">Cost is locked and cannot be modified after creation</small>
            </div>

            <!-- Form Actions -->
            <div class="mt-6 flex justify-end space-x-4">
                <a href="{{ route('customer.packages.show', $package->package_id) }}" 
                   class="px-6 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Update Delivery Request
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('editPackageForm');
    
    // Form validation before submission
    form.addEventListener('submit', function(e) {
        const requiredFields = [
            { id: 'package_contents', name: 'Contents Description' },
            { id: 'sender_address', name: 'Pickup Address' },
            { id: 'recipient_address', name: 'Delivery Address' }
        ];
        
        let isValid = true;
        let errorMessages = [];
        
        // Reset previous error styling
        document.querySelectorAll('.border-red-500').forEach(field => {
            field.classList.remove('border-red-500');
        });
        
        // Validate required fields
        requiredFields.forEach(field => {
            const element = document.getElementById(field.id);
            if (!element.value.trim()) {
                isValid = false;
                element.classList.add('border-red-500');
                errorMessages.push(field.name + ' is required');
            }
        });
        
        // Validate dimensions format if provided
        const dimensionsField = document.getElementById('package_dimensions');
        if (dimensionsField.value.trim()) {
            const dimensionPattern = /^\d+x\d+x\d+$/;
            if (!dimensionPattern.test(dimensionsField.value.trim())) {
                isValid = false;
                dimensionsField.classList.add('border-red-500');
                errorMessages.push('Dimensions must be in format: LengthxWidthxHeight (e.g., 30x20x10)');
            }
        }
        
        if (!isValid) {
            e.preventDefault();
            alert('Please fix the following errors:\n\n' + errorMessages.join('\n'));
        }
    });
    
    // Add input event listeners to remove error styling when user starts typing
    ['package_contents', 'sender_address', 'recipient_address', 'package_dimensions'].forEach(fieldId => {
        const field = document.getElementById(fieldId);
        field.addEventListener('input', function() {
            this.classList.remove('border-red-500');
        });
    });
});
</script>
@endsection