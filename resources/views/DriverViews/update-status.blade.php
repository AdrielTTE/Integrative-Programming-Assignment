@extends('layouts.driverLayout')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-4">Update Delivery Status</h1>
    <p class="text-gray-600 mb-6">Select a new status for a package and click "Update" to confirm the change.</p>

    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert"><p>{{ session('success') }}</p></div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert"><p>{{ session('error') }}</p></div>
    @endif

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-4 text-left text-xs font-semibold text-gray-600 uppercase">Package ID</th>
                        <th class="p-4 text-left text-xs font-semibold text-gray-600 uppercase">Tracking ID</th>
                        <th class="p-4 text-left text-xs font-semibold text-gray-600 uppercase">Current Status</th>
                        <th class="p-4 text-left text-xs font-semibold text-gray-600 uppercase">Update Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($packages as $package)
                    <tr class="hover:bg-gray-50">
                        <td class="p-4 font-mono">
                            {{ $package->package_id }}
                        </td>
                        <td class="p-4 font-mono">
                            {{ $package->tracking_number }}
                            <br>
                            <span class="text-xs text-gray-500">{{ $package->recipient_address }}</span>
                        </td>
                        <td class="p-4">
                            <span class="px-2 py-1 font-semibold text-xs rounded-full 
                                @if($package->package_status === 'DELIVERED') bg-green-100 text-green-800
                                @elseif($package->package_status === 'IN_TRANSIT') bg-blue-100 text-blue-800
                                @elseif($package->package_status === 'FAILED') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ $package->package_status }}
                            </span>
                        </td>
                        <td class="p-4">
                            @if($package->package_status === 'DELIVERED')
                                <a href="{{ route('driver.proof.show', $package->package_id) }}" 
                                   class="px-4 py-2 bg-gray-600 text-white text-sm font-semibold rounded-md hover:bg-gray-700">
                                    View Proof
                                </a>
                            @else
                                <form action="{{ route('driver.status.update', $package->package_id) }}" method="POST" class="flex items-center space-x-2">
                                    @csrf
                                    <select name="status" class="block w-full rounded-md border-gray-300 shadow-sm text-sm" required>
                                        <option value="">Select new status...</option>
                                        <option value="IN_TRANSIT">In Transit</option>
                                        <option value="DELIVERED">Complete Delivery</option>
                                        <option value="FAILED">Failed</option>
                                    </select>
                                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-md hover:bg-indigo-700 whitespace-nowrap">
                                        Update
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center p-10 text-gray-500">You have no active packages to update.</td>
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