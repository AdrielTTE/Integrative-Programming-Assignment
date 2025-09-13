@extends('layouts.customerLayout')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-4">My Proof of Delivery History</h1>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Package ID</th>
                        <th class="p-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tracking #</th>
                        <th class="p-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Delivered On</th>
                        <th class="p-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Proof Type</th>
                        <th class="p-4"></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($proofs as $proof)
                        <tr class="hover:bg-gray-50">
                            <td class="p-4 whitespace-nowrap">{{ optional($proof->delivery->package)->package_id }}</td>
                            <td class="p-4 whitespace-nowrap font-mono">{{ optional($proof->delivery->package)->tracking_number }}</td>
                            <td class="p-4 whitespace-nowrap">{{ $proof->timestamp_created->format('F j, Y, g:i a') }}</td>
                            <td class="p-4 whitespace-nowrap">{{ $proof->proof_type }}</td>
                            <td class="p-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('customer.package.show', optional($proof->delivery->package)->package_id) }}" class="text-indigo-600 hover:text-indigo-900">
                                    View Details
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center p-10 text-gray-500">You have no proofs of delivery on record.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 bg-white border-t">
            {{ $proofs->links() }}
        </div>
    </div>
</div>
@endsection