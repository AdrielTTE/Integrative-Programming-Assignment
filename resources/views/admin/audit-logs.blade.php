@extends('layouts.adminLayout')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Admin Activity Logs</h1>
        <a href="{{ route('admin.dashboard') }}" class="text-indigo-600 hover:text-indigo-900">
            &larr; Back to Dashboard
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white shadow rounded-lg p-4 mb-6">
        <form method="GET" action="{{ route('admin.audit.logs') }}" class="flex flex-wrap gap-4">
            <input type="text" 
                   name="admin_id" 
                   placeholder="Admin ID (e.g., AD001)"
                   value="{{ request('admin_id') }}"
                   class="rounded-md border-gray-300 shadow-sm text-sm px-3 py-2">
            
            <select name="action" class="rounded-md border-gray-300 shadow-sm text-sm px-3 py-2">
                <option value="">All Actions</option>
                <option value="create" {{ request('action') == 'create' ? 'selected' : '' }}>Create</option>
                <option value="update" {{ request('action') == 'update' ? 'selected' : '' }}>Update</option>
                <option value="delete" {{ request('action') == 'delete' ? 'selected' : '' }}>Delete</option>
                <option value="view" {{ request('action') == 'view' ? 'selected' : '' }}>View</option>
                <option value="process" {{ request('action') == 'process' ? 'selected' : '' }}>Process</option>
                <option value="cancel" {{ request('action') == 'cancel' ? 'selected' : '' }}>Cancel</option>
                <option value="deliver" {{ request('action') == 'deliver' ? 'selected' : '' }}>Deliver</option>
                <option value="assign" {{ request('action') == 'assign' ? 'selected' : '' }}>Assign</option>
                <option value="return" {{ request('action') == 'return' ? 'selected' : '' }}>Return</option>
            </select>

            <select name="target_type" class="rounded-md border-gray-300 shadow-sm text-sm px-3 py-2">
                <option value="">All Types</option>
                <option value="package" {{ request('target_type') == 'package' ? 'selected' : '' }}>Package</option>
                <option value="user" {{ request('target_type') == 'user' ? 'selected' : '' }}>User</option>
                <option value="delivery" {{ request('target_type') == 'delivery' ? 'selected' : '' }}>Delivery</option>
            </select>

            <select name="status" class="rounded-md border-gray-300 shadow-sm text-sm px-3 py-2">
                <option value="">All Status</option>
                <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Success</option>
                <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
            </select>

            <input type="date" 
                   name="date_from" 
                   value="{{ request('date_from') }}"
                   max="{{ date('Y-m-d') }}"
                   class="rounded-md border-gray-300 shadow-sm text-sm px-3 py-2">
            
            <input type="date" 
                   name="date_to" 
                   value="{{ request('date_to') }}"
                   max="{{ date('Y-m-d') }}"
                   class="rounded-md border-gray-300 shadow-sm text-sm px-3 py-2">

            <input type="text" 
                   name="search" 
                   placeholder="Search in descriptions..."
                   value="{{ request('search') }}"
                   class="rounded-md border-gray-300 shadow-sm text-sm px-3 py-2">

            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                </svg>
                Filter
            </button>
            <a href="{{ route('admin.audit.logs') }}" class="px-4 py-2 bg-gray-300 text-gray-700 text-sm rounded-md hover:bg-gray-400">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Reset
            </a>
        </form>

        @if(request()->hasAny(['admin_id', 'action', 'target_type', 'status', 'date_from', 'date_to', 'search']))
            <div class="mt-3 text-sm text-gray-600">
                Active filters: 
                @if(request('admin_id'))
                    <span class="inline-block px-2 py-1 bg-blue-100 text-blue-800 rounded mr-1">
                        Admin: {{ request('admin_id') }}
                        <a href="{{ route('admin.audit.logs', request()->except('admin_id')) }}" class="ml-1 text-red-500">×</a>
                    </span>
                @endif
                @if(request('action'))
                    <span class="inline-block px-2 py-1 bg-blue-100 text-blue-800 rounded mr-1">
                        Action: {{ request('action') }}
                        <a href="{{ route('admin.audit.logs', request()->except('action')) }}" class="ml-1 text-red-500">×</a>
                    </span>
                @endif
                @if(request('target_type'))
                    <span class="inline-block px-2 py-1 bg-blue-100 text-blue-800 rounded mr-1">
                        Type: {{ request('target_type') }}
                        <a href="{{ route('admin.audit.logs', request()->except('target_type')) }}" class="ml-1 text-red-500">×</a>
                    </span>
                @endif
                @if(request('status'))
                    <span class="inline-block px-2 py-1 bg-blue-100 text-blue-800 rounded mr-1">
                        Status: {{ request('status') }}
                        <a href="{{ route('admin.audit.logs', request()->except('status')) }}" class="ml-1 text-red-500">×</a>
                    </span>
                @endif
                @if(request('date_from') || request('date_to'))
                    <span class="inline-block px-2 py-1 bg-blue-100 text-blue-800 rounded mr-1">
                        Date: {{ request('date_from') }} - {{ request('date_to') ?? 'Today' }}
                        <a href="{{ route('admin.audit.logs', request()->except(['date_from', 'date_to'])) }}" class="ml-1 text-red-500">×</a>
                    </span>
                @endif
                @if(request('search'))
                    <span class="inline-block px-2 py-1 bg-blue-100 text-blue-800 rounded mr-1">
                        Search: {{ request('search') }}
                        <a href="{{ route('admin.audit.logs', request()->except('search')) }}" class="ml-1 text-red-500">×</a>
                    </span>
                @endif
            </div>
        @endif
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-500">Total Logs</div>
            <div class="text-2xl font-bold text-gray-800">{{ $logs->total() }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-500">This Page</div>
            <div class="text-2xl font-bold text-gray-800">{{ $logs->count() }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-500">Success Rate</div>
            <div class="text-2xl font-bold text-green-600">
                @php
                    $successCount = 0;
                    $totalCount = 0;
                    foreach($logs as $log) {
                        $totalCount++;
                        if($log->status == 'success') $successCount++;
                    }
                    $rate = $totalCount > 0 ? round(($successCount / $totalCount) * 100, 1) : 0;
                @endphp
                {{ $rate }}%
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-500">Current Page</div>
            <div class="text-2xl font-bold text-gray-800">{{ $logs->currentPage() }}/{{ $logs->lastPage() }}</div>
        </div>
    </div>

    <!-- Audit Logs Table -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        @if($logs->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Admin</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Target</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">IP</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($logs as $log)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 text-sm text-gray-600">
                                    {{ $log->created_at->format('M d, H:i:s') }}
                                    <br>
                                    <span class="text-xs text-gray-400">{{ $log->created_at->diffForHumans() }}</span>
                                </td>
                                <td class="px-4 py-2 text-sm">
                                    <div class="font-medium text-gray-900">{{ $log->admin_id }}</div>
                                    <div class="text-gray-500">{{ $log->admin_username }}</div>
                                </td>
                                <td class="px-4 py-2">
                                    <span class="px-2 py-1 text-xs rounded-full 
                                        @if(in_array($log->action, ['create', 'process', 'deliver']))
                                            bg-green-100 text-green-800
                                        @elseif(in_array($log->action, ['update', 'assign']))
                                            bg-blue-100 text-blue-800
                                        @elseif(in_array($log->action, ['delete', 'cancel', 'return']))
                                            bg-red-100 text-red-800
                                        @else
                                            bg-gray-100 text-gray-800
                                        @endif">
                                        {{ ucfirst($log->action) }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 text-sm">
                                    <div class="font-mono text-gray-700">{{ $log->target_type }}</div>
                                    <div class="text-indigo-600">{{ $log->target_id }}</div>
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-600">
                                    {{ $log->description ?: 'No description' }}
                                    @if($log->error_message)
                                        <br><span class="text-red-500 text-xs">Error: {{ \Illuminate\Support\Str::limit($log->error_message, 50) }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2">
                                    <span class="px-2 py-1 text-xs rounded-full 
                                        {{ $log->status == 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ ucfirst($log->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 text-xs text-gray-500">
                                    {{ $log->ip_address }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="bg-gray-50 px-4 py-3 border-t border-gray-200">
                {{ $logs->withQueryString()->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No audit logs found</h3>
                <p class="mt-1 text-sm text-gray-500">
                    @if(request()->hasAny(['admin_id', 'action', 'target_type', 'status', 'date_from', 'date_to', 'search']))
                        Try adjusting your filters to see more results.
                    @else
                        Activity logs will appear here once admins perform actions.
                    @endif
                </p>
            </div>
        @endif
    </div>
</div>

<script>
// Auto-refresh page every 30 seconds if no filters are active
@if(!request()->hasAny(['admin_id', 'action', 'target_type', 'status', 'date_from', 'date_to', 'search']))
    setTimeout(function() {
        window.location.reload();
    }, 30000);
@endif
</script>
@endsection