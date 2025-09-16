@extends('layouts.adminLayout')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Assign Packages</h1>
            <p class="text-gray-600">Below are packages ready for driver assignment.</p>
        </div>
        <a href="{{ route('admin.packages.create') }}" class="px-4 py-2 bg-green-500 text-white font-semibold rounded-md hover:bg-green-600">
            + Create New Package
        </a>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-4 text-left text-xs font-semibold text-gray-600 uppercase">Package ID</th>
                        <th class="p-4 text-left text-xs font-semibold text-gray-600 uppercase">Customer</th>
                        <th class="p-4 text-left text-xs font-semibold text-gray-600 uppercase">Recipient Address</th>
                        <th class="p-4 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                        <th class="p-4 text-left text-xs font-semibold text-gray-600 uppercase">Created On</th>
                        <th class="p-4 text-left text-xs font-semibold text-gray-600 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($packages as $package)
                    <tr class="hover:bg-gray-50">
                        <td class="p-4 whitespace-nowrap font-mono text-sm">{{ $package->package_id }}</td>
                        <td class="p-4 whitespace-nowrap text-sm">{{ optional($package->customer)->first_name . ' ' . optional($package->customer)->last_name }}</td>
                        <td class="p-4 text-sm truncate max-w-xs">{{ $package->recipient_address }}</td>
                        <td class="p-4 whitespace-nowrap text-sm">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                @if($package->package_status == 'PENDING') bg-yellow-100 text-yellow-800 @else bg-blue-100 text-blue-800 @endif">
                                {{ $package->package_status }}
                            </span>
                        </td>
                        <td class="p-4 whitespace-nowrap text-sm">{{ \Carbon\Carbon::parse($package->created_at)->format('Y-m-d H:i') }}</td>
                        <td class="p-4 whitespace-nowrap text-sm">
                            <a href="{{ route('admin.packages.show_assign_form', $package->package_id) }}" class="text-indigo-600 hover:text-indigo-900 font-semibold">Assign Driver</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center p-10 text-gray-500">There are no available packages to assign.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 bg-white border-t">
            {{ $packages->links() }}
        </div>
    </div>
</div>
@endsection