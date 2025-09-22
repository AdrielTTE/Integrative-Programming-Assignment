@extends('layouts.adminLayout')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-4">Assign Pending Packages</h1>
        <p class="text-gray-600 mb-6">Below is a list of all packages waiting for a driver assignment.</p>

        {{-- Display Success or Error Messages --}}
        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                <p>{{ session('success') }}</p>
            </div>
        @endif
        @if(session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                <p>{{ session('error') }}</p>
            </div>
        @endif

        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="p-4 text-left text-xs font-semibold text-gray-600 uppercase">Package ID</th>
                            <th class="p-4 text-left text-xs font-semibold text-gray-600 uppercase">Customer ID</th>
                            <th class="p-4 text-left text-xs font-semibold text-gray-600 uppercase">Recipient Address</th>
                            <th class="p-4 text-left text-xs font-semibold text-gray-600 uppercase">Created at</th>
                            <th class="p-4 text-left text-xs font-semibold text-gray-600 uppercase">Priority Level</th>
                            <th class="p-4 text-left text-xs font-semibold text-gray-600 uppercase">Assign Driver</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($packages as $package)
                            <tr class="hover:bg-gray-50">
                                <td class="p-4 font-mono">{{ $package->package_id }}</td>
                                <td class="p-4">{{ $package->user_id}}</td>
                                <td class="p-4 truncate max-w-xs">{{ $package->recipient_address }}</td>
                                <td class="p-4">{{ $package->created_at->format('Y-m-d') }}</td>
                                <td class="p-4">{{ $package->priority}}</td>
                                <td class="p-4">
                                    {{-- Each row has its own form for direct assignment --}}
                                    <form action="{{ route('admin.assignments.assign', $package->package_id) }}" method="POST"
                                        class="flex items-center space-x-2">
                                        @csrf
                                        <select name="driver_id"
                                            class="block w-full rounded-md border-gray-300 shadow-sm text-sm" required>
                                            <option value="">Select Driver...</option>
                                            @foreach($drivers as $driver)
                                                <option value="{{ $driver->driver_id }}">
                                                    {{ trim($driver->first_name . ' ' . ($driver->last_name ?? '')) }}
                                                    ({{ $driver->driver_id }})
                                                </option>

                                            @endforeach
                                        </select>
                                        <button type="submit"
                                            class="px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-md hover:bg-indigo-700 whitespace-nowrap">
                                            Assign
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center p-10 text-gray-500">
                                    <p class="font-bold">No pending packages to assign.</p>
                                    <p class="text-sm">New customer delivery requests will appear here.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($packages->hasPages())
                <div class="p-4 bg-white border-t">
                    {{ $packages->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection