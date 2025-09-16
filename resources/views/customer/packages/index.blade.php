@extends('layouts.customerLayout')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">My Delivery Requests</h1>
        <a href="{{ route('customer.packages.create') }}" 
           class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            New Delivery Request
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <form method="GET" action="{{ route('customer.packages.index') }}" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-48">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Search by tracking number, contents, or address..."
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <select name="status" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All Statuses</option>
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">
                Filter
            </button>
            <a href="{{ route('customer.packages.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                Clear
            </a>
        </form>
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

    <!-- Packages List -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        @if($packages->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tracking Number
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Contents
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Priority
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Created
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($packages as $package)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $package->tracking_number }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ $package->package_id }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900 max-w-xs truncate">
                                        {{ $package->package_contents }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ $package->package_weight }} kg
                                        @if($package->package_dimensions)
                                            | {{ $package->package_dimensions }} cm
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
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
                                        {{ $statuses[$package->package_status] ?? $package->package_status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ ucfirst($package->priority) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $package->created_at->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('customer.packages.show', $package->package_id) }}" 
                                           class="text-indigo-600 hover:text-indigo-900">View</a>
                                        
                                        @if($package->canBeEdited())
                                            <a href="{{ route('customer.packages.edit', $package->package_id) }}" 
                                               class="text-green-600 hover:text-green-900">Edit</a>
                                        @endif
                                        
                                        @if($package->canBeCancelled())
                                            <form method="POST" action="{{ route('customer.packages.destroy', $package->package_id) }}" 
                                                  class="inline" onsubmit="return confirm('Are you sure you want to cancel this delivery?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">Cancel</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                {{ $packages->withQueryString()->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No delivery requests</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by creating a new delivery request.</p>
                <div class="mt-6">
                    <a href="{{ route('customer.packages.create') }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                        Create New Request
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection