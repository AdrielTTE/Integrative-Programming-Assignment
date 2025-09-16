@extends('layouts.adminLayout')

@section('content')
    <div class="container mx-auto px-4 py-6 max-w-2xl">
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-2xl font-bold text-gray-800">Assign Driver to Package</h1>
            <a href="{{ route('admin.packages.assign') }}" class="text-indigo-600 hover:text-indigo-900">&larr; Back to
                Assignment List</a>
        </div>

        <!-- Package Summary -->
        <div class="bg-white p-6 rounded-lg shadow-md mb-6">
            <h2 class="text-lg font-semibold text-gray-700 mb-3">Package Details</h2>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div><strong class="text-gray-600">Package ID:</strong> <span
                        class="font-mono">{{ $package['package_id'] }}</span></div>
                <div><strong class="text-gray-600">Status:</strong> {{ $package['package_status'] }}</div>
                <div class="col-span-2"><strong class="text-gray-600">Recipient:</strong>
                    {{ $package['recipient_address'] }}</div>
            </div>
        </div>

        <!-- Assignment Form -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <form action="{{ route('admin.packages.assign_driver', $package['package_id']) }}" method="POST">
                @csrf
                <div class="space-y-6">
                    <!-- Driver Selection -->
                    <div>
                        <label for="driver_id" class="block text-sm font-medium text-gray-700">Available Drivers</label>
                        <select name="driver_id" id="driver_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                            <option value="">Select a driver</option>
                            @forelse($drivers as $driver)
                                <option value="{{ $driver['driver_id'] }}">
                                    {{ $driver['first_name'] }} {{ $driver['last_name'] }} ({{ $driver['driver_id'] }})
                                </option>
                            @empty
                                <option value="" disabled>No available drivers found</option>
                            @endforelse
                        </select>
                    </div>

                    <!-- Vehicle Selection -->
                    <div>
                        <label for="vehicle_id" class="block text-sm font-medium text-gray-700">Available Vehicles</label>
                        <select name="vehicle_id" id="vehicle_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                            <option value="">Select a vehicle</option>
                            @forelse($vehicles as $vehicle)
                                <option value="{{ $vehicle['vehicle_id'] }}">
                                    {{ $vehicle['vehicle_id'] }} ({{ $vehicle['vehicle_type'] }})
                                </option>
                            @empty
                                <option value="" disabled>No available vehicles found</option>
                            @endforelse
                        </select>
                    </div>

                    <!-- Pickup Time -->
                    <div>
                        <label for="pickup_time" class="block text-sm font-medium text-gray-700">Scheduled Pickup
                            Time</label>
                        <input type="datetime-local" name="pickup_time" id="pickup_time"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                    </div>

                    <!-- Estimated Delivery Time -->
                    <div>
                        <label for="delivery_time" class="block text-sm font-medium text-gray-700">Estimated Delivery
                            Time</label>
                        <input type="datetime-local" name="delivery_time" id="delivery_time"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                    </div>
                </div>

                <div class="flex justify-end mt-6">
                    <button type="submit"
                        class="px-6 py-2 bg-indigo-600 text-white font-semibold rounded-md hover:bg-indigo-700">Confirm
                        Assignment</button>
                </div>
            </form>
        </div>
    </div>
@endsection