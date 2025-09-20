@extends('layouts.adminLayout')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Package Management Dashboard</h1>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
            {{ session('error') }}
        </div>
    @endif
    @if(session('warning'))
        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative mb-4">
            {{ session('warning') }}
        </div>
    @endif

    <!-- Statistics -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
        <div class="bg-white shadow rounded-lg p-4 text-center">
            <p class="text-sm text-gray-500">Total Packages</p>
            <p class="text-xl font-bold">{{ $statistics['total'] ?? 0 }}</p>
        </div>
        <div class="bg-white shadow rounded-lg p-4 text-center">
            <p class="text-sm text-gray-500">Pending</p>
            <p class="text-xl font-bold">{{ $statistics['pending'] ?? 0 }}</p>
        </div>
        <div class="bg-white shadow rounded-lg p-4 text-center">
            <p class="text-sm text-gray-500">In Transit</p>
            <p class="text-xl font-bold">{{ $statistics['in_transit'] ?? 0 }}</p>
        </div>
        <div class="bg-white shadow rounded-lg p-4 text-center">
            <p class="text-sm text-gray-500">Delivered</p>
            <p class="text-xl font-bold">{{ $statistics['delivered'] ?? 0 }}</p>
        </div>
        <div class="bg-white shadow rounded-lg p-4 text-center">
            <p class="text-sm text-gray-500">Failed/Cancelled</p>
            <p class="text-xl font-bold">
                {{ ($statistics['failed'] ?? 0) + ($statistics['cancelled'] ?? 0) }}
            </p>
        </div>
        <div class="bg-white shadow rounded-lg p-4 text-center">
            <p class="text-sm text-gray-500">Today's Revenue</p>
            <p class="text-xl font-bold">RM{{ number_format($statistics['revenue_today'] ?? 0, 2) }}</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white shadow rounded-lg p-4 mb-6">
        <form method="GET" action="{{ route('admin.packages.index') }}" class="flex flex-wrap gap-4">
            <input type="text" name="search" placeholder="Tracking #, Package ID, Customer..."
                value="{{ request('search') }}"
                class="flex-1 rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            
            <select name="status" class="rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">All Status</option>
                @foreach($statuses as $value => $label)
                    <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>

            <input type="date" name="date_from" value="{{ request('date_from') }}" max="{{ date('Y-m-d') }}"
                class="rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            <input type="date" name="date_to" value="{{ request('date_to') }}" max="{{ date('Y-m-d') }}"
                class="rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">

            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                Filter
            </button>
            <a href="{{ route('admin.packages.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                Reset
            </a>
        </form>
    </div>

    <!-- Packages Table -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        @if($packages->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Package ID</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tracking</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Priority</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cost</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($packages as $package)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 text-sm font-mono">{{ $package->package_id }}</td>
                                <td class="px-4 py-2 font-medium">{{ $package->tracking_number }}</td>
                                <td class="px-4 py-2">
                                    {{ $package->user->username ?? 'N/A' }}
                                </td>
                                <td class="px-4 py-2">
                                    <span class="px-2 py-1 text-xs rounded-full bg-gray-100">
                                        {{ ucwords(str_replace('_', ' ', $package->package_status)) }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-500">{{ ucfirst($package->priority ?? 'standard') }}</td>
                                <td class="px-4 py-2 text-sm">RM{{ number_format($package->shipping_cost ?? 0, 2) }}</td>
                                <td class="px-4 py-2 text-sm text-gray-500">{{ $package->created_at->format('Y-m-d H:i') }}</td>
                                <td class="px-4 py-2 text-sm space-x-2">
                                    <a href="{{ route('admin.packages.show', $package->package_id) }}" 
                                       class="text-indigo-600 hover:text-indigo-900">View/Edit</a>
                                    @if(!in_array($package->package_status, ['delivered', 'in_transit']))
                                        <form method="POST" action="{{ route('admin.packages.destroy', $package->package_id) }}" 
                                              class="inline" onsubmit="return confirm('Delete this package?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="bg-gray-50 px-4 py-3 border-t border-gray-200">
                {{ $packages->withQueryString()->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No packages found</h3>
                <p class="mt-1 text-sm text-gray-500">Try adjusting your filters or add new packages.</p>
            </div>
        @endif
    </div>
</div>
@endsection