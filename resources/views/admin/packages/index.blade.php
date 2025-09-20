@extends('layouts.adminLayout')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Package Management Dashboard</h1>
        <div class="flex space-x-2">
            <button class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700" onclick="exportData()">
                <i class="fas fa-download mr-2"></i> Export
            </button>
            <button class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700" data-modal-target="#bulkActionsModal">
                <i class="fas fa-tasks mr-2"></i> Bulk Actions
            </button>
            <button class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700" onclick="importFeedback()">
                <i class="fas fa-sync mr-2"></i> Import Feedback
            </button>
        </div>
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
                            <th class="px-4 py-3">
                                <input type="checkbox" id="selectAll" class="form-checkbox">
                            </th>
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
                                <td class="px-4 py-2">
                                    <input type="checkbox" class="form-checkbox package-checkbox" value="{{ $package->package_id }}">
                                </td>
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
                                    <a href="{{ route('admin.packages.show', $package->package_id) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                    @if($package->getState()->canBeEdited())
                                        <button onclick="editPackage('{{ $package->package_id }}')" class="text-yellow-600 hover:text-yellow-900">Edit</button>
                                    @endif
                                    @if($package->getState()->canBeAssigned())
                                        <button onclick="assignDriver('{{ $package->package_id }}')" class="text-blue-600 hover:text-blue-900">Assign</button>
                                    @endif
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

<!-- Bulk Actions Modal (Tailwind style) -->
<div id="bulkActionsModal" class="hidden fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6">
        <h3 class="text-lg font-semibold mb-4">Bulk Actions</h3>
        <form method="POST" action="{{ route('admin.packages.bulk') }}">
            @csrf
            <p class="mb-2">Selected Packages: <span id="selectedCount">0</span></p>
            <div id="selectedPackageIds"></div>
            <select name="action" id="bulkAction" required class="w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 mb-4">
                <option value="">Select Action</option>
                <option value="delete">Delete Selected</option>
                <option value="update_status">Update Status</option>
                <option value="assign_driver">Assign Driver</option>
            </select>
            <div id="bulkValueGroup" class="mb-4 hidden">
                <label id="bulkValueLabel" class="block text-sm font-medium text-gray-700">Value</label>
                <input type="text" name="value" id="bulkValue" class="w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-300 rounded-md hover:bg-gray-400">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Execute</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
// CSRF token for AJAX requests
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

// Select all functionality
$('#selectAll').change(function() {
    $('.package-checkbox').prop('checked', $(this).is(':checked'));
    updateSelectedCount();
});

// Update selected count
$('.package-checkbox').change(function() {
    updateSelectedCount();
});

function updateSelectedCount() {
    const selectedPackages = [];
    $('.package-checkbox:checked').each(function() {
        selectedPackages.push($(this).val());
    });
    
    $('#selectedCount').text(selectedPackages.length);
    
    // Create hidden inputs for each selected package
    const container = $('#selectedPackageIds');
    container.empty();
    selectedPackages.forEach(function(packageId) {
        container.append('<input type="hidden" name="package_ids[]" value="' + packageId + '">');
    });
}

// Bulk action change handler
$('#bulkAction').change(function() {
    const action = $(this).val();
    const valueGroup = $('#bulkValueGroup');
    const valueLabel = $('#bulkValueLabel');
    const valueInput = $('#bulkValue');
    
    if (action === 'update_status') {
        valueGroup.show();
        valueLabel.text('New Status');
        valueInput.replaceWith(`
            <select class="form-control" name="value" id="bulkValue">
                <option value="pending">Pending</option>
                <option value="processing">Processing</option>
                <option value="in_transit">In Transit</option>
                <option value="delivered">Delivered</option>
                <option value="cancelled">Cancelled</option>
                <option value="returned">Returned</option>
            </select>
        `);
    } else if (action === 'assign_driver') {
        valueGroup.show();
        valueLabel.text('Driver ID');
        valueInput.replaceWith('<input type="text" class="form-control" name="value" id="bulkValue" placeholder="D001">');
    } else {
        valueGroup.hide();
    }
});

// Edit package function
function editPackage(packageId) {
    // Load package data via AJAX
    $.get(`/admin/packages/${packageId}`)
        .done(function(data) {
            // This would normally return JSON data
            // For now, we'll just open the modal
            $('#editPackageForm').attr('action', `/admin/packages/${packageId}`);
            $('#editPackageModal').modal('show');
        })
        .fail(function(xhr) {
            alert('Error loading package data');
        });
}

// Assign driver function
function assignDriver(packageId) {
    const driverId = prompt('Enter Driver ID (e.g., D001):');
    if (driverId && /^D\d{3,}$/.test(driverId)) {
        $.post(`/admin/packages/${packageId}`, {
            _method: 'PUT',
            action: 'assign',
            driver_id: driverId
        })
        .done(function() {
            location.reload();
        })
        .fail(function(xhr) {
            alert('Error: ' + (xhr.responseJSON?.message || 'Assignment failed'));
        });
    } else if (driverId) {
        alert('Invalid driver ID format. Must be like D001, D002, etc.');
    }
}

// Export data function
function exportData() {
    const params = new URLSearchParams(window.location.search);
    window.open('/admin/packages/export?' + params.toString(), '_blank');
}

// Import feedback function
function importFeedback() {
    if (confirm('Import customer feedback from Feedback module?')) {
        $.post('{{ route("admin.packages.import.feedback") }}')
            .done(function(response) {
                alert(response.message || 'Feedback imported successfully');
                location.reload();
            })
            .fail(function(xhr) {
                alert('Error: ' + (xhr.responseJSON?.error || 'Import failed'));
            });
    }
}

// Initialize tooltips
$(function () {
    $('[data-toggle="tooltip"]').tooltip();
});

// Auto-refresh statistics every 5 minutes
setInterval(function() {
    $.get('/admin/packages/statistics/data')
        .done(function(data) {
            // Update statistics cards with new data
            Object.keys(data).forEach(function(key) {
                $(`[data-stat="${key}"]`).text(data[key]);
            });
        });
}, 300000); // 5 minutes
</script>
@endpush

@endsection