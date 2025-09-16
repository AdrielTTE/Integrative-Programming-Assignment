@extends('layouts.adminLayout')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-2xl">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold text-gray-800">Create New Package</h1>
        <a href="{{ route('admin.search') }}" class="text-indigo-600 hover:text-indigo-900">&larr; Back to Packages</a>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-md">
        <form action="{{ route('admin.packages.store') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Customer -->
                <div class="md:col-span-2">
                    <label for="customer_id" class="block text-sm font-medium text-gray-700">Customer</label>
                    <select name="customer_id" id="customer_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                        <option value="">Select a customer</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer['customer_id'] }}" {{ old('customer_id') == $customer['customer_id'] ? 'selected' : '' }}>
                                {{ $customer['first_name'] }} {{ $customer['last_name'] }} ({{ $customer['customer_id'] }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Contents -->
                <div class="md:col-span-2">
                    <label for="package_contents" class="block text-sm font-medium text-gray-700">Package Contents</label>
                    <input type="text" name="package_contents" id="package_contents" value="{{ old('package_contents') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                </div>

                <!-- Weight -->
                <div>
                    <label for="package_weight" class="block text-sm font-medium text-gray-700">Weight (kg)</label>
                    <input type="number" step="0.1" name="package_weight" id="package_weight" value="{{ old('package_weight') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                </div>

                <!-- Priority -->
                <div>
                    <label for="priority" class="block text-sm font-medium text-gray-700">Priority</label>
                    <select name="priority" id="priority" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                        <option value="standard" {{ old('priority') == 'standard' ? 'selected' : '' }}>Standard</option>
                        <option value="express" {{ old('priority') == 'express' ? 'selected' : '' }}>Express</option>
                        <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                    </select>
                </div>

                <!-- Sender Address -->
                <div class="md:col-span-2">
                    <label for="sender_address" class="block text-sm font-medium text-gray-700">Sender Address</label>
                    <textarea name="sender_address" id="sender_address" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>{{ old('sender_address') }}</textarea>
                </div>

                <!-- Recipient Address -->
                <div class="md:col-span-2">
                    <label for="recipient_address" class="block text-sm font-medium text-gray-700">Recipient Address</label>
                    <textarea name="recipient_address" id="recipient_address" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>{{ old('recipient_address') }}</textarea>
                </div>
            </div>

            <div class="flex justify-end mt-6">
                <button type="submit" class="px-6 py-2 bg-indigo-600 text-white font-semibold rounded-md hover:bg-indigo-700">Create Package</button>
            </div>
        </form>
    </div>
</div>
@endsection