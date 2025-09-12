@extends('layouts.adminLayout')

@section('content')
<div class="container mx-auto px-4">
    <h1 class="text-2xl font-bold mb-4">Proof Verification Queue</h1>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full leading-normal">
            <thead>
                <tr>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Proof ID</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Package ID</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Type</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Date</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($proofs as $proof)
                <tr>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">{{ $proof->proof_id }}</td>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">{{ $proof->delivery->package_id ?? 'N/A' }}</td>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                         <span class="relative inline-block px-3 py-1 font-semibold leading-tight
                            {{ $proof->proof_type == 'PHOTO' ? 'text-green-900' : 'text-blue-900' }}">
                            <span aria-hidden class="absolute inset-0 {{ $proof->proof_type == 'PHOTO' ? 'bg-green-200' : 'bg-blue-200' }} opacity-50 rounded-full"></span>
                            <span class="relative">{{ $proof->proof_type }}</span>
                        </span>
                    </td>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">{{ $proof->timestamp_created->format('Y-m-d H:i') }}</td>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-right">
                        <a href="{{ route('admin.proof.show', $proof->proof_id) }}" class="text-indigo-600 hover:text-indigo-900">Verify</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-10 text-gray-500">No proofs are currently awaiting verification.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
         <div class="px-5 py-5 bg-white border-t flex flex-col xs:flex-row items-center xs:justify-between">
            {{ $proofs->links() }}
        </div>
    </div>
</div>
@endsection