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
                <small class="text-gray-500">Final cost will be calculated after submission</small>
            </div>

            <!-- Form Actions -->
            <div class="mt-6 flex justify-end space-x-4">
                <a href="{{ route('customer.packages.index') }}" 
                   class="px-6 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Create Delivery Request
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('createPackageForm');
    const weightInput = document.getElementById('package_weight');
    const prioritySelect = document.getElementById('priority');
    const costEstimation = document.getElementById('costEstimation');

    function updateCostEstimation() {
        const weight = parseFloat(weightInput.value) || 0;
        const priority = prioritySelect.value;
        
        if (weight > 0) {
            let baseCost = 8.00;
            let weightCost = weight * 3.50;
            let priorityMultiplier = priority === 'express' ? 1.5 : priority === 'urgent' ? 2 : 1;
            
            let estimatedCost = (baseCost + weightCost) * priorityMultiplier;
            costEstimation.textContent = `RM${estimatedCost.toFixed(2)}`;
        } else {
            costEstimation.textContent = 'Enter weight to calculate';
        }
    }

    weightInput.addEventListener('input', updateCostEstimation);
    prioritySelect.addEventListener('change', updateCostEstimation);
    
    updateCostEstimation();
});
</script>
@endsection