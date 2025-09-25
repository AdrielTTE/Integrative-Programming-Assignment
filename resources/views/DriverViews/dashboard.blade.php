@extends('layouts.driverLayout')
@section('content')
    @vite('resources/css/driverDashboard.css')
    <header class="text-2xl font-bold text-gray-100">Driver Dashboard</header>
    <div class="dashboard-content mt-6">

        <div class="bg-white p-6 rounded-lg shadow-md mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-semibold text-gray-800">Welcome,
                        {{ trim(($driver->first_name ?? '') . ' ' . ($driver->last_name ?? Auth::user()->username)) }}!</h2>
                    <p class="text-gray-600 mt-1">Your current status is:
                        @if ($driver->driver_status === 'AVAILABLE')
                            <span class="font-bold text-green-600">AVAILABLE</span>
                        @else
                            <span class="font-bold text-red-600">BUSY</span>
                        @endif
                    </p>
                </div>
                <div>
                    <form action="{{ route('driver.dashboard.update-status') }}" method="POST">
                        @csrf
                        <input type="hidden" name="current_status" value="{{ $driver->driver_status }}">
                        @if ($driver->driver_status === 'AVAILABLE')
                            <button type="submit"
                                class="px-5 py-2 bg-red-500 text-white font-semibold rounded-md hover:bg-red-600 shadow-sm">
                                Set Status to BUSY
                            </button>
                        @else
                            <button type="submit"
                                class="px-5 py-2 bg-green-500 text-white font-semibold rounded-md hover:bg-green-600 shadow-sm">
                                Set Status to AVAILABLE
                            </button>
                        @endif
                    </form>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                <p>{{ session('success') }}</p>
            </div>
        @endif

        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <div class="bg-white p-6 rounded-lg shadow-md text-center">
                <h2 class="text-3xl font-bold">{{ $totalAssigned }}</h2>
                <p class="text-sm text-gray-500 mt-1">Total Assigned Packages to you</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md text-center">
                {{-- --- THIS IS THE FIX for KPI cards --- --}}
                <h2 class="text-3xl font-bold text-blue-600">
                    {{ $scheduled + $inTransit }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">Active Deliveries to you</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md text-center">
                <h2 class="text-3xl font-bold text-green-600">{{ $delivered }}</h2>
                <p class="text-sm text-gray-500 mt-1">Completed Deliveries by you</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md text-center">
                <h2 class="text-3xl font-bold text-red-600">{{ $failedPackages }}</h2>
                <p class="text-sm text-gray-500 mt-1">Total Failed Deliveries</p>
            </div>
        </div>

        <!-- Recent Activity Section -->
        <div class="mt-8 bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-xl font-semibold mb-4">Your Current Active Packages</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="p-4 text-left">Package ID</th>
                            <th class="p-4 text-left">Recipient</th>
                            <th class="p-4 text-left">Status</th>
                            <th class="p-4 text-left">Recipient Address</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        {{-- --- THIS IS THE FIX for the table --- --}}
                        @forelse($recentPackages as $package)
                            <tr>
                                {{-- Use object syntax '->' instead of array syntax '[]' --}}
                                <td class="p-4 font-mono">{{ $package->package_id }}</td>
                                <td class="p-4 truncate max-w-xs">{{ $package->recipient_address }}</td>
                                <td class="p-4"><span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">{{ $package->package_status }}</span>
                                </td>
                                <td class="p-4">{{ $package->recipient_address }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center p-10 text-gray-500">You have no active deliveries.
                                </td>
                            </tr>
                        @endforelse
                        {{-- --- END OF FIX --- --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
