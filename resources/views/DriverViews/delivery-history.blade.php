@extends('layouts.driverLayout')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-4">My Delivery History</h1>

        <p class="text-gray-600 mb-6">A log of all your completed and failed deliveries.</p>

        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="p-4 text-left text-xs font-semibold text-gray-600 uppercase">Tracking ID</th>
                            <th class="p-4 text-left text-xs font-semibold text-gray-600 uppercase">Final Status</th>
                            <th class="p-4 text-left text-xs font-semibold text-gray-600 uppercase">Completed On</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($packages as $package)
                            <tr class="hover:bg-gray-50">
                                <td class="p-4 font-mono">{{ $package->tracking_number }}</td>
                                <td class="p-4">
                                    @if($package->package_status == 'DELIVERED')
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">DELIVERED</span>
                                    @else
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">{{ $package->package_status }}</span>
                                    @endif
                                </td>
                                <td class="p-4">
                                    {{ $package->actual_delivery_time ? \Carbon\Carbon::parse($package->actual_delivery_time)->format('Y-m-d H:i') : 'N/A' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center p-10 text-gray-500">You have no delivery history yet.</td>
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