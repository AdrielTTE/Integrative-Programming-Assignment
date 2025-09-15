@extends('layouts.driverLayout')
@vite('resources/css/DriverSide/driverAssigned.css')

@section('content')
    <header class="text-2xl font-bold mb-4">Driver Dashboard</header>

    <div class="dashboard-content">
        <h2 class="text-xl font-semibold mb-4">Assigned Packages to You</h2>
        
        <!-- Table for displaying assigned packages -->
        <table class="min-w-full table-auto bg-white rounded-lg shadow-lg">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Package ID</th>
                    <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Delivery Address</th>
                    <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Status</th>
                    <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Action</th>
                </tr>
            </thead>
            <tbody>
                <!-- Sample rows, you can loop through actual data here -->
                <tr>
                    <td class="px-4 py-2 border-b text-sm">TRY ONLY</td>
                    <td class="px-4 py-2 border-b text-sm">TRY ONLY</td>
                    <td class="px-4 py-2 border-b text-sm text-green-600">TRY ONLY</td>
                    <td class="px-4 py-2 border-b text-sm">
                        <a href="#" class="text-blue-500 hover:underline">TRY ONLY</a>
                    </td>
                </tr>
                <tr>
                    <td class="px-4 py-2 border-b text-sm">TRY ONLY</td>
                    <td class="px-4 py-2 border-b text-sm">TRY ONLY</td>
                    <td class="px-4 py-2 border-b text-sm text-red-600">TRY ONLY</td>
                    <td class="px-4 py-2 border-b text-sm">
                        <a href="#" class="text-blue-500 hover:underline">TRY ONLY</a>
                    </td>
                </tr>
                <!-- More rows can be added dynamically based on the data -->
            </tbody>
        </table>
    </div>
@endsection
