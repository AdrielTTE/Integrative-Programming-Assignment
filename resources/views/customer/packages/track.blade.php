{{-- resources/views/packages/track.blade.php --}}
@extends('layouts.customerLayout')
@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-gray-900 mb-2">Track Your Package</h2>
        <p class="text-gray-600">Enter your tracking number to get updates on your package delivery status</p>
    </div>

    <!-- Tracking Form -->
    <div class="bg-white rounded-lg p-6 mb-6 border border-gray-200 shadow-sm">
        <form method="POST" action="{{ route('packages.track.submit') }}" class="space-y-4">
            @csrf
            <div>
                <label for="tracking_number" class="block text-sm font-medium text-gray-700 mb-2">
                    Tracking Number
                </label>
                <input type="text" 
                       name="tracking_number" 
                       id="tracking_number"
                       class="w-full px-4 py-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                       placeholder="Enter tracking number (e.g., 1Z999AA1234567890)"
                       value="{{ old('trackingNumber', $trackingNumber ?? '') }}">
            </div>
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-md transition-colors duration-200">
                Track Package
            </button>
        </form>
    </div>

    <!-- Error Message -->
    @if(!empty($error))
        <div class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">Error</h3>
                    <p class="text-sm text-red-700 mt-1">{{ $error }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Package Information -->
    @if(!empty($package))
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm mb-6">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Package Details</h3>
                <p class="text-sm text-gray-600 mt-1">Current status and tracking information</p>
            </div>
            <div class="p-6">
                <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Tracking Number</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-mono bg-gray-100 px-2 py-1 rounded">{{ $package->tracking_number }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                        <dd class="mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                @if(strtolower($package->status) == 'delivered') bg-green-100 text-green-800
                                @elseif(strtolower($package->status) == 'in transit') bg-blue-100 text-blue-800
                                @elseif(strtolower($package->status) == 'pending') bg-yellow-100 text-yellow-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ $package->status }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Customer</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $package->customer->name ?? 'Not specified' }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- History Section -->
        @if(!empty($history))
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Tracking History</h3>
                    <p class="text-sm text-gray-600 mt-1">Package status updates over time</p>
                </div>
                <div class="p-6">
                    <div class="flow-root">
                        <ul role="list" class="-mb-8">
                            @foreach($history as $index => $event)
                                <li>
                                    <div class="relative pb-8">
                                        @if($index < count($history) - 1)
                                            <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                        @endif
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                                                    <span class="text-white text-xs font-medium">{{ count($history) - $index }}</span>
                                                </span>
                                            </div>
                                            <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900">{{ $event->status }}</p>
                                                </div>
                                                <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                    <time>{{ $event->created_at }}</time>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>
@endsection