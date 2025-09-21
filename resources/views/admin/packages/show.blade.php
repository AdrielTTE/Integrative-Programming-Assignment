@extends('layouts.adminLayout')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Package Details: {{ $package->tracking_number }}</h1>
        <a href="{{ route('admin.packages.index') }}" class="text-indigo-600 hover:text-indigo-900">
            &larr; Back to Package Management
        </a>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    @if(session('info'))
        <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('info') }}</span>
        </div>
    @endif

    <!-- Validation Errors -->
    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content Area -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Main Package Information Form -->
            <div class="bg-white shadow-lg rounded-lg p-6">
                <form method="POST" action="{{ route('admin.packages.update', $package->package_id) }}" id="mainUpdateForm">
                    @csrf
                    @method('PUT')
                    
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <p class="text-sm text-gray-500">Package ID / Tracking Number</p>
                            <p class="text-lg font-mono text-gray-700 font-semibold">{{ $package->package_id }}</p>
                            <p class="text-2xl font-mono text-indigo-600 font-bold">{{ $package->tracking_number }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-500 mb-1">Current Status</p>
                            <select name="package_status" class="rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="pending" {{ strtolower($package->package_status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="processing" {{ strtolower($package->package_status) == 'processing' ? 'selected' : '' }}>Processing</option>
                                <option value="in_transit" {{ strtolower($package->package_status) == 'in_transit' ? 'selected' : '' }}>In Transit</option>
                                <option value="out_for_delivery" {{ strtolower($package->package_status) == 'out_for_delivery' ? 'selected' : '' }}>Out for Delivery</option>
                                <option value="delivered" {{ strtolower($package->package_status) == 'delivered' ? 'selected' : '' }}>Delivered</option>
                                <option value="cancelled" {{ strtolower($package->package_status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                <option value="returned" {{ strtolower($package->package_status) == 'returned' ? 'selected' : '' }}>Returned</option>
                                <option value="failed" {{ strtolower($package->package_status) == 'failed' ? 'selected' : '' }}>Failed</option>
                            </select>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Left Column -->
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Package Contents</label>
                                <input type="text" name="package_contents" 
                                       value="{{ old('package_contents', $package->package_contents) }}" 
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Weight (kg)</label>
                                <input type="number" step="0.01" name="package_weight" 
                                       value="{{ old('package_weight', $package->package_weight) }}" 
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Dimensions</label>
                                <input type="text" name="package_dimensions" 
                                       value="{{ old('package_dimensions', $package->package_dimensions) }}" 
                                       placeholder="e.g., 30x20x10"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Priority</label>
                                <select name="priority" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    <option value="standard" {{ $package->priority == 'standard' ? 'selected' : '' }}>Standard</option>
                                    <option value="express" {{ $package->priority == 'express' ? 'selected' : '' }}>Express</option>
                                    <option value="urgent" {{ $package->priority == 'urgent' ? 'selected' : '' }}>Urgent</option>
                                </select>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Shipping Cost (RM)</label>
                                <input type="number" step="0.01" name="shipping_cost" 
                                       value="{{ old('shipping_cost', $package->shipping_cost) }}" 
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Estimated Delivery</label>
                                <input type="date" name="estimated_delivery" 
                                       value="{{ old('estimated_delivery', $package->estimated_delivery ? $package->estimated_delivery->format('Y-m-d') : '') }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Actual Delivery</label>
                                <input type="datetime-local" name="actual_delivery" 
                                       value="{{ old('actual_delivery', $package->actual_delivery ? $package->actual_delivery->format('Y-m-d\TH:i') : '') }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Created At</label>
                                <input type="text" value="{{ $package->created_at->format('M d, Y H:i') }}" 
                                       class="mt-1 block w-full rounded-md border-gray-200 bg-gray-50 text-sm" readonly>
                            </div>
                        </div>
                    </div>

                    <!-- Addresses Section -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                <i class="fas fa-map-marker-alt text-blue-500"></i> Sender Address
                            </label>
                            <textarea name="sender_address" rows="3" 
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">{{ old('sender_address', $package->sender_address) }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                <i class="fas fa-map-pin text-green-500"></i> Recipient Address
                            </label>
                            <textarea name="recipient_address" rows="3" 
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">{{ old('recipient_address', $package->recipient_address) }}</textarea>
                        </div>
                    </div>

                    <!-- Notes Section -->
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700">Special Instructions / Notes</label>
                        <textarea name="notes" rows="2" 
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">{{ old('notes', $package->notes) }}</textarea>
                    </div>

                    <!-- Submit Button -->
                    <div class="mt-6 flex justify-end">
                        <button type="submit" 
                                class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                            Save All Changes
                        </button>
                    </div>
                </form>
            </div>

            <!-- Customer Information (Read-only) -->
            <div class="bg-white shadow-lg rounded-lg p-6">
                <h3 class="font-semibold text-lg mb-4">
                    <i class="fas fa-user text-gray-500 mr-2"></i> Customer Information
                </h3>
                @if($package->user)
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Customer ID</p>
                            <p class="text-gray-800 font-mono">{{ $package->user->user_id }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-600">Username</p>
                            <p class="text-gray-800">{{ $package->user->username }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-600">Email</p>
                            <p class="text-gray-800">
                                <a href="mailto:{{ $package->user->email }}" class="text-indigo-600 hover:underline">
                                    {{ $package->user->email }}
                                </a>
                            </p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-600">Phone</p>
                            <p class="text-gray-800">{{ $package->user->phone_number ?: 'Not provided' }}</p>
                        </div>
                    </div>
                @else
                    <p class="text-gray-500 italic">Customer information not available</p>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="bg-white shadow-lg rounded-lg p-6">
                <h3 class="font-semibold text-lg mb-4">
                    <i class="fas fa-bolt text-yellow-500 mr-2"></i> Quick Actions
                </h3>
                <div class="space-y-2">
                    @if(strtolower($package->package_status) == 'pending')
                        <form method="POST" action="{{ route('admin.packages.update', $package->package_id) }}">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="action" value="process">
                            <button type="submit"
                                    class="w-full px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700">
                                <i class="fas fa-play-circle mr-2"></i> Process Package
                            </button>
                        </form>
                    @endif

                    @if($stateInfo['can_cancel'] ?? false)
                        <form method="POST" action="{{ route('admin.packages.update', $package->package_id) }}">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="action" value="cancel">
                            <button type="submit"
                                    class="w-full px-4 py-2 bg-orange-600 text-white text-sm font-medium rounded-md hover:bg-orange-700"
                                    onclick="return confirm('Cancel this package?')">
                                <i class="fas fa-times-circle mr-2"></i> Cancel Package
                            </button>
                        </form>
                    @endif

                    @if(strtolower($package->package_status) == 'out_for_delivery')
                        <form method="POST" action="{{ route('admin.packages.update', $package->package_id) }}">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="action" value="deliver">
                            <button type="submit"
                                    class="w-full px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700">
                                <i class="fas fa-check-circle mr-2"></i> Mark as Delivered
                            </button>
                        </form>
                    @endif

                    @if(!in_array(strtolower($package->package_status), ['delivered', 'in_transit']))
                        <form method="POST" action="{{ route('admin.packages.destroy', $package->package_id) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="w-full px-4 py-2 border border-red-300 text-sm font-medium rounded-md text-red-700 bg-white hover:bg-red-50"
                                    onclick="return confirm('Delete this package permanently?')">
                                <i class="fas fa-trash-alt mr-2"></i> Delete Package
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Package History Timeline -->
            <div class="bg-white shadow-lg rounded-lg p-6">
                <h3 class="font-semibold text-lg mb-4">Package History</h3>
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
                                                <i class="text-white text-xs fas fa-box"></i>
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1.5">
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">
                                                    {{ $event['status'] ?? 'Status Update' }}
                                                </p>
                                                <p class="text-xs text-gray-500">
                                                    {{ $event['description'] ?? ucwords(str_replace('_', ' ', $event['status'] ?? 'unknown')) }}
                                                </p>
                                            </div>
                                            <div class="mt-1 text-xs text-gray-400">
                                                {{ isset($event['timestamp']) ? $event['timestamp']->diffForHumans() : 'Unknown time' }}
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

@push('scripts')
<script>
// Debug form submission
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('mainUpdateForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            console.log('Form is being submitted');
            const formData = new FormData(this);
            for (let [key, value] of formData.entries()) {
                console.log(key + ': ' + value);
            }
        });
    }
});
</script>
@endpush

@endsection