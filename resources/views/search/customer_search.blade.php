@extends('layouts.customerLayout')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-4">Search My Packages</h1>

    {{-- Search Form --}}
    <div class="bg-white p-6 rounded-lg shadow-md mb-6">
        <form action="{{ route('customer.search') }}" method="GET">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="md:col-span-2">
                    <label for="keyword" class="block text-sm font-medium text-gray-700">Keyword</label>
                    <input
                        type="text"
                        name="keyword"
                        id="keyword"
                        value="{{ $input['keyword'] ?? '' }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                        placeholder="Tracking #, recipient address..."
                    >
                </div>

                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700">From</label>
                    <input
                        type="date"
                        name="date_from"
                        id="date_from"
                        value="{{ $input['date_from'] ?? '' }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                    >
                </div>

                <div>
                    <label for="date_to" class="block text-sm font-medium text-gray-700">To</label>
                    <input
                        type="date"
                        name="date_to"
                        id="date_to"
                        value="{{ $input['date_to'] ?? '' }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                    >
                </div>
            </div>

            <div class="flex justify-end mt-4">
                <a
                    href="{{ route('customer.search') }}"
                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 mr-2"
                >
                    Clear
                </a>
                <button
                    type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                >
                    Search
                </button>
            </div>
        </form>
    </div>

    {{-- Search Results --}}
    @if(isset($results))
        <h2 class="text-xl font-semibold mb-3">
            Search Results ({{ $results->total() }})
        </h2>

        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="p-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tracking #</th>
                            <th class="p-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Recipient</th>
                            <th class="p-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="p-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="p-4"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($results as $package)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="p-4">{{ $package->tracking_number }}</td>
                                <td class="p-4">{{ $package->recipient_address }}</td>
                                <td class="p-4">{{ $package->package_status }}</td>
                                <td class="p-4">{{ $package->created_at->format('Y-m-d') }}</td>
                                <td class="p-4 text-right">
                                    <a
                                        href="{{ route('customer.package.show', ['packageId' => $package->package_id]) }}"
                                        class="text-blue-500 hover:underline"
                                    >
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center p-10 text-gray-500">
                                    No packages found matching your criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4">
                {{ $results->withQueryString()->links() }}
            </div>
        </div>
    @endif
</div>
@endsection