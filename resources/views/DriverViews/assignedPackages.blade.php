@extends('layouts.driverLayout')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-4">My Assigned Packages</h1>
        <p class="text-gray-600 mb-6">This is a list of all active delivery tasks assigned to you.</p>

        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="p-4 text-left">Tracking #</th>
                            <th class="p-4 text-left">Customer Name</th>
                            <th class="p-4 text-left">Recipient Address</th>
                            <th class="p-4 text-left">Status</th>
                            <th class="p-4 text-left">Est. Delivery</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($packages as $package)
                            <tr class="hover:bg-gray-50">
                                <td class="p-4 font-mono">{{ $package->tracking_number }}</td>
                                {{-- This now works because we manually selected the customer name --}}
                                <td class="p-4">{{ $package->customer_first_name }} {{ $package->customer_last_name }}</td>
                                <td class="p-4 truncate max-w-xs">{{ $package->recipient_address }}</td>
                                <span class="px-2 py-1 font-semibold text-xs rounded-full bg-blue-100 text-blue-800">
                                    {{ $package->package_status }}
                                </span>
                                <td class="p-4">
                                    {{ \Carbon\Carbon::parse($package->estimated_delivery_time)->format('Y-m-d H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center p-10 text-gray-500">You have no active packages assigned.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($packages->hasPages())
                <div class="p-4 bg-white border-t">{{ $packages->links() }}</div>
            @endif


        </div>
    </div>
@endsection