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
            <input type="text" name="admin_id" placeholder="Admin ID (e.g., AD001)"
                value="{{ request('admin_id') }}"
                class="rounded-md border-gray-300 shadow-sm text-sm">
            
            <select name="action" class="rounded-md border-gray-300 shadow-sm text-sm">
                <option value="">All Actions</option>
                <option value="create" {{ request('action') == 'create' ? 'selected' : '' }}>Create</option>
                <option value="update" {{ request('action') == 'update' ? 'selected' : '' }}>Update</option>
                <option value="delete" {{ request('action') == 'delete' ? 'selected' : '' }}>Delete</option>
                <option value="view" {{ request('action') == 'view' ? 'selected' : '' }}>View</option>
                <option value="process" {{ request('action') == 'process' ? 'selected' : '' }}>Process</option>
                <option value="cancel" {{ request('action') == 'cancel' ? 'selected' : '' }}>Cancel</option>
                <option value="deliver" {{ request('action') == 'deliver' ? 'selected' : '' }}>Deliver</option>
            </select>

            <select name="target_type" class="rounded-md border-gray-300 shadow-sm text-sm">
                <option value="">All Types</option>
                <option value="package" {{ request('target_type') == 'package' ? 'selected' : '' }}>Package</option>
                <option value="user" {{ request('target_type') == 'user' ? 'selected' : '' }}>User</option>
                <option value="delivery" {{ request('target_type') == 'delivery' ? 'selected' : '' }}>Delivery</option>
            </select>

            <input type="date" name="date_from" value="{{ request('date_from') }}"
                class="rounded-md border-gray-300 shadow-sm text-sm">
            
            <input type="date" name="date_to" value="{{ request('date_to') }}"
                class="rounded-md border-gray-300 shadow-sm text-sm">

            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700">
                Filter
            </button>
            <a href="{{ route('admin.audit.logs') }}" class="px-4 py-2 bg-gray-300 text-gray-700 text-sm rounded-md hover:bg-gray-400">
                Reset
            </a>
        </form>
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
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Details</th>
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
                                        <br><span class="text-red-500 text-xs">Error: {{ Str::limit($log->error_message, 50) }}</span>
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
                                <td class="px-4 py-2 text-center">
                                    <button onclick="showLogDetails({{ $log->id }})" 
                                            class="text-indigo-600 hover:text-indigo-900 text-sm">
                                        View
                                    </button>
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
                <p class="mt-1 text-sm text-gray-500">Activity logs will appear here once admins perform actions.</p>
            </div>
        @endif
    </div>
</div>

<!-- Log Details Modal -->
<div id="logDetailsModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
    <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-3xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Log Details</h3>
            <div id="logDetailsContent" class="mt-2 space-y-3 text-sm">
                <!-- Content will be loaded here -->
            </div>
            <div class="mt-6">
                <button onclick="closeLogDetails()" 
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Store logs data for modal display
const logsData = @json($logs->items());

function showLogDetails(logId) {
    const log = logsData.find(l => l.id === logId);
    if (log) {
        const content = document.getElementById('logDetailsContent');
        content.innerHTML = `
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="font-semibold">Admin:</p>
                    <p>${log.admin_id} (${log.admin_username || 'N/A'})</p>
                </div>
                <div>
                    <p class="font-semibold">Timestamp:</p>
                    <p>${new Date(log.created_at).toLocaleString()}</p>
                </div>
                <div>
                    <p class="font-semibold">Action:</p>
                    <p>${log.action}</p>
                </div>
                <div>
                    <p class="font-semibold">Target:</p>
                    <p>${log.target_type}: ${log.target_id}</p>
                </div>
                <div>
                    <p class="font-semibold">IP Address:</p>
                    <p>${log.ip_address || 'N/A'}</p>
                </div>
                <div>
                    <p class="font-semibold">Method:</p>
                    <p>${log.method || 'N/A'}</p>
                </div>
            </div>
            
            ${log.description ? `
                <div class="mt-3">
                    <p class="font-semibold">Description:</p>
                    <p>${log.description}</p>
                </div>
            ` : ''}
            
            ${log.old_values ? `
                <div class="mt-3">
                    <p class="font-semibold">Original Values:</p>
                    <pre class="bg-gray-100 p-2 rounded text-xs overflow-x-auto">${JSON.stringify(log.old_values, null, 2)}</pre>
                </div>
            ` : ''}
            
            ${log.new_values ? `
                <div class="mt-3">
                    <p class="font-semibold">New Values:</p>
                    <pre class="bg-gray-100 p-2 rounded text-xs overflow-x-auto">${JSON.stringify(log.new_values, null, 2)}</pre>
                </div>
            ` : ''}
            
            ${log.error_message ? `
                <div class="mt-3">
                    <p class="font-semibold text-red-600">Error:</p>
                    <p class="text-red-600">${log.error_message}</p>
                </div>
            ` : ''}
            
            <div class="mt-3">
                <p class="font-semibold">URL:</p>
                <p class="text-xs break-all">${log.url || 'N/A'}</p>
            </div>
        `;
        document.getElementById('logDetailsModal').classList.remove('hidden');
    }
}

function closeLogDetails() {
    document.getElementById('logDetailsModal').classList.add('hidden');
}
</script>
@endpush

@endsection