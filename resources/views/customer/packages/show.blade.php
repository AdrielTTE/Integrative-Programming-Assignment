@extends('layouts.customerLayout')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Delivery Details</h1>
        <a href="{{ route('customer.packages.index') }}" class="text-indigo-600 hover:text-indigo-900">
            &larr; Back to My Requests
        </a>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
            @if(session('show_undo'))
                <form method="POST" action="{{ route('customer.packages.undo') }}" class="inline ml-4">
                    @csrf
                    <button type="submit" class="underline text-green-800 hover:text-green-600">
                        Undo Last Action
                    </button>
                </form>
            @endif
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Package Information -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Package Overview -->
            <div class="bg-white shadow-lg rounded-lg p-6">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <p class="text-sm text-gray-500">Tracking Number</p>
                        <p class="text-2xl font-mono text-indigo-600 font-bold">{{ $package->tracking_number }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-500">Status</p>
                        <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full
                            @switch($package->package_status)
                                @case('pending') bg-yellow-100 text-yellow-800 @break
                                @case('processing') bg-blue-100 text-blue-800 @break
                                @case('in_transit') bg-indigo-100 text-indigo-800 @break
                                @case('out_for_delivery') bg-purple-100 text-purple-800 @break
                                @case('delivered') bg-green-100 text-green-800 @break
                                @case('cancelled') bg-red-100 text-red-800 @break
                                @case('returned') bg-gray-100 text-gray-800 @break
                                @case('failed') bg-red-100 text-red-800 @break
                                @default bg-gray-100 text-gray-800
                            @endswitch">
                            {{ ucwords(str_replace('_', ' ', $package->package_status)) }}
                        </span>
                    </div>
                </div>

                <hr class="my-4">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="font-semibold text-lg mb-3">Package Information</h3>
                        <div class="space-y-2 text-sm">
                            <div><strong>Contents:</strong> {{ $package->package_contents }}</div>
                            <div><strong>Weight:</strong> {{ $package->package_weight }} kg</div>
                            @if($package->package_dimensions)
                                <div><strong>Dimensions:</strong> {{ $package->package_dimensions }} cm</div>
                            @endif
                            <div><strong>Priority:</strong> {{ ucfirst($package->priority) }}</div>
                            @if($package->shipping_cost)
                                <div><strong>Cost:</strong> RM{{ number_format($package->shipping_cost, 2) }}</div>
                            @endif
                        </div>
                    </div>

                    <div>
                        <h3 class="font-semibold text-lg mb-3">Delivery Information</h3>
                        <div class="space-y-2 text-sm">
                            <div><strong>Created:</strong> {{ $package->created_at->format('M d, Y g:i A') }}</div>
                            @if($package->estimated_delivery)
                                <div><strong>Estimated Delivery:</strong> {{ $package->estimated_delivery->format('M d, Y') }}</div>
                            @endif
                            @if($package->actual_delivery)
                                <div><strong>Delivered:</strong> {{ $package->actual_delivery->format('M d, Y g:i A') }}</div>
                            @endif
                            @if($package->delivery && $package->delivery->driver)
                                <div><strong>Driver:</strong> {{ $package->delivery->driver->first_name ?? 'Assigned' }}</div>
                            @endif
                        </div>
                    </div>
                </div>

                @if($package->notes)
                    <div class="mt-4 p-3 bg-gray-50 rounded-md">
                        <strong class="text-sm">Special Instructions:</strong>
                        <p class="text-sm mt-1">{{ $package->notes }}</p>
                    </div>
                @endif
            </div>

            <!-- Addresses -->
            <div class="bg-white shadow-lg rounded-lg p-6">
                <h3 class="font-semibold text-lg mb-4">Addresses</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="font-bold text-gray-600 mb-2">Pickup Address</p>
                        <p class="text-gray-800 text-sm leading-relaxed">{{ $package->sender_address }}</p>
                    </div>
                    <div>
                        <p class="font-bold text-gray-600 mb-2">Delivery Address</p>
                        <p class="text-gray-800 text-sm leading-relaxed">{{ $package->recipient_address }}</p>
                    </div>
                </div>
            </div>

            <!-- Proof of Delivery (if delivered) -->
            @if($package->package_status === 'delivered' && isset($proof))
                <div class="bg-white shadow-lg rounded-lg p-6">
                    <h3 class="font-semibold text-lg mb-4">Proof of Delivery</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            @if($proof->proof_type == 'PHOTO' && $proof->proof_url)
                                <img src="{{ $proof->proof_url }}" alt="Proof of Delivery" 
                                     class="w-full h-auto rounded-md border shadow-sm">
                            @elseif($proof->proof_type == 'SIGNATURE')
                                <div class="border rounded-md p-6 bg-gray-50 text-center">
                                    <p class="text-gray-500 mb-2">Signed by:</p>
                                    <p class="text-2xl font-handwriting text-gray-800">
                                        {{ $proof->recipient_signature_name }}
                                    </p>
                                </div>
                            @else
                                <p class="text-gray-500 italic">No visual proof available.</p>
                            @endif
                        </div>

                        <div>
                            <div class="space-y-3 text-sm">
                                <div><strong>Proof Type:</strong> {{ $proof->proof_type }}</div>
                                <div><strong>Timestamp:</strong> {{ $proof->timestamp_created->format('M d, Y g:i A') }}</div>
                                
                                @if(isset($verificationDetails))
                                    <div class="mt-4 p-3 rounded-md {{ $verificationDetails['is_valid'] ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }}">
                                        <p class="font-semibold {{ $verificationDetails['is_valid'] ? 'text-green-800' : 'text-red-800' }} text-sm mb-2">
                                            {{ $verificationDetails['is_valid'] ? '✓ Proof Verified' : '✗ Proof Issues Detected' }}
                                        </p>
                                        @if(isset($metadata) && !empty($metadata))
                                            <div class="text-xs text-gray-600 space-y-1">
                                                @foreach($metadata as $key => $value)
                                                    <div><strong>{{ $key }}:</strong> {{ $value }}</div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Actions -->
            <div class="bg-white shadow-lg rounded-lg p-6">
                <h3 class="font-semibold text-lg mb-4">Actions</h3>
                <div class="space-y-2">
                    @if($package->canBeEdited())
                        <a href="{{ route('customer.packages.edit', $package->package_id) }}" 
                           class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                            Edit Package
                        </a>
                    @endif
                    
                    @if($package->canBeCancelled())
                        <form method="POST" action="{{ route('customer.packages.destroy', $package->package_id) }}" 
                              onsubmit="return confirm('Are you sure you want to cancel this delivery?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="w-full inline-flex justify-center items-center px-4 py-2 border border-red-300 text-sm font-medium rounded-md text-red-700 bg-white hover:bg-red-50">
                                Cancel Delivery
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Tracking History -->
            <div class="bg-white shadow-lg rounded-lg p-6">
                <h3 class="font-semibold text-lg mb-4">Tracking History</h3>
                <div class="flow-root">
                    <ul role="list" class="-mb-8">
                        @forelse($history as $index => $event)
                            <li>
                                <div class="relative pb-8">
                                    @if(!$loop->last)
                                        <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                    @endif
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full 
                                                @if($loop->first) bg-indigo-500 @else bg-gray-400 @endif 
                                                flex items-center justify-center ring-8 ring-white">
                                                <span class="text-white text-xs font-medium">
                                                    {{ count($history) - $index }}
                                                </span>
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1.5">
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">
                                                    {{ $event['status'] ?? 'Status Update' }}
                                                </p>
                                                @if(isset($event['action']))
                                                    <p class="text-xs text-gray-500">{{ $event['action'] }}</p>
                                                @elseif(isset($event['description']))
                                                    <p class="text-xs text-gray-500">{{ $event['description'] }}</p>
                                                @else
                                                    <p class="text-xs text-gray-500">{{ ucwords(str_replace('_', ' ', $event['status'] ?? 'unknown')) }}</p>
                                                @endif
                                            </div>
                                            <div class="mt-1 text-xs text-gray-400">
                                                @if(isset($event['timestamp']))
                                                    {{ $event['timestamp']->diffForHumans() }}
                                                @elseif(isset($event['created_at']))
                                                    {{ $event['created_at']->diffForHumans() }}
                                                @else
                                                    {{ now()->diffForHumans() }}
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @empty
                            <li><p class="text-gray-500 text-sm">No history available yet.</p></li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection