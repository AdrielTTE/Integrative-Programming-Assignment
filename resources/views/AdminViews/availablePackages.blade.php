@extends('layouts.adminLayout')

@section('content')
    <header class="text-2xl font-bold mb-4">Available Packages</header>

    @if(session('success'))
        <div class="bg-green-500 text-white p-4 rounded-lg mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="package-list bg-white p-6 rounded-lg shadow-lg">
        <h2 class="text-xl font-semibold mb-4">Available Packages</h2>

        <table class="min-w-full table-auto bg-white rounded-lg shadow-lg">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Package ID</th>
                    <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Delivery Address</th>
                    <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Assign to Driver</th>
                    <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($availablePackages as $package)
                    <tr>
                        <td class="px-4 py-2 border-b text-sm">{{ $package->id }}</td>
                        <td class="px-4 py-2 border-b text-sm">{{ $package->delivery_address }}</td>
                        <td class="px-4 py-2 border-b text-sm">
                            <form action="{{ route('admin.assignPackage', $package->id) }}" method="POST">
                                @csrf
                                <select name="driver_id" class="p-2 border rounded-md">
                                    <option value="" disabled selected>Select Driver</option>
                                    @foreach($drivers as $driver)
                                        <option value="{{ $driver->id }}">{{ $driver->name }}</option>
                                    @endforeach
                                </select>
                        </td>
                        <td class="px-4 py-2 border-b text-sm">
                            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md">Assign</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
