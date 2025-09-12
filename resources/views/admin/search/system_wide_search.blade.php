@extends('layouts.adminLayout')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-4">Package Search</h1>

    <!-- Search Form -->
    <div class="bg-white p-6 rounded-lg shadow-md mb-6">
        <form action="{{ route('admin.search') }}" method="GET">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-3">
                    <label for="keyword" class="block text-sm font-medium text-gray-700">Keyword</label>
                    <input type="text" name="keyword" value="{{ $input['keyword'] ?? '' }}" class="mt-1 block w-full rounded-md" placeholder="Package, Customer, Driver Name, Address...">
                </div>
                <div>
                    <label for="package_status" class="block text-sm font-medium text-gray-700">Package Status</label>
                    <select name="package_status" class="mt-1 block w-full rounded-md">
                        <option value="">Any</option>
                        <option value="PENDING" @selected(isset($input['package_status']) && $input['package_status'] == 'PENDING')>Pending</option>
                        <option value="IN_TRANSIT" @selected(isset($input['package_status']) && $input['package_status'] == 'IN_TRANSIT')>In Transit</option>
                        <option value="DELIVERED" @selected(isset($input['package_status']) && $input['package_status'] == 'DELIVERED')>Delivered</option>
                    </select>
                </div>
                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700">From</label>
                    <input type="date" name="date_from" value="{{ $input['date_from'] ?? '' }}" class="mt-1 block w-full rounded-md">
                </div>
                <div>
                    <label for="date_to" class="block text-sm font-medium text-gray-700">To</label>
                    <input type="date" name="date_to" value="{{ $input['date_to'] ?? '' }}" class="mt-1 block w-full rounded-md">
                </div>
            </div>
            <div class="flex justify-end mt-4">
                <a href="{{ route('admin.search') }}" class="px-4 py-2 bg-gray-200 rounded-md mr-2">Clear</a>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md">Search</button>
            </div>
        </form>
    </div>

    <!-- Search Results -->
    @if(isset($results))
    <form action="{{ route('admin.search.bulk') }}" method="POST">
        @csrf
        <div class="flex justify-between items-center mb-3">
            <h2 class="text-xl font-semibold">Search Results ({{ $results->total() }})</h2>
            <div>
                 <select name="action" class="rounded-md">
                    <option value="">Bulk Actions</option>
                    <option value="cancel">Cancel Selected</option>
                </select>
                <button type="submit" class="px-3 py-2 bg-red-500 text-white text-sm rounded-md ml-2">Apply</button>
            </div>
        </div>

        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <table class="min-w-full">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-3 w-4"><input type="checkbox" id="select-all"></th>
                        <th class="p-3 text-left">Tracking #</th>
                        <th class="p-3 text-left">Customer</th>
                        <th class="p-3 text-left">Recipient</th>
                        <th class="p-3 text-left">Status</th>
                        <th class="p-3 text-left">Driver</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($results as $package)
                    <tr class="border-b">
                        <td class="p-3"><input type="checkbox" name="package_ids[]" value="{{ $package->package_id }}" class="pkg-checkbox"></td>
                        <td class="p-3 font-mono text-sm">{{ $package->tracking_number }}</td>
                        <td class="p-3">{{ optional($package->customer)->first_name }}</td>
                        <td class="p-3 truncate max-w-xs">{{ $package->recipient_address }}</td>
                        <td class="p-3">{{ $package->package_status }}</td>
                        <td class="p-3">{{ optional($package->delivery->driver)->first_name }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="p-10 text-center text-gray-500">No results found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
         <div class="mt-4">{{ $results->withQueryString()->links() }}</div>
    </form>
    <script>
        document.getElementById('select-all').addEventListener('click', function(event) {
            document.querySelectorAll('.pkg-checkbox').forEach(function(checkbox) {
                checkbox.checked = event.target.checked;
            });
        });
    </script>
    @endif
</div>
@endsection