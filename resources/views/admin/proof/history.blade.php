@extends('layouts.adminLayout')

@section('content')
<div class="container mx-auto px-4">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Proof Verification History</h1>
        <a href="{{ route('admin.proof.index') }}" class="text-indigo-600 hover:text-indigo-900">&larr; Back to Verification Queue</a>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full leading-normal">
            <thead>
                <tr>
                    <th class="px-5 py-3 border-b-2 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase">Proof ID</th>
                    <th class="px-5 py-3 border-b-2 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase">Package ID</th>
                    <th class="px-5 py-3 border-b-2 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                    <th class="px-5 py-3 border-b-2 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase">Verified By</th>
                    <th class="px-5 py-3 border-b-2 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase">Verified At</th>
                    <th class="px-5 py-3 border-b-2 bg-gray-100"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($proofs as $proof)
                <tr>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">{{ $proof->proof_id }}</td>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">{{ optional($proof->delivery)->package_id ?? 'N/A' }}</td>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                        @if($proof->verification_status == 'APPROVED')
                            <span class="px-2 py-1 font-semibold leading-tight text-green-700 bg-green-100 rounded-full">Approved</span>
                        @elseif($proof->verification_status == 'REJECTED')
                            <span class="px-2 py-1 font-semibold leading-tight text-red-700 bg-red-100 rounded-full">Rejected</span>
                        @else
                            <span class="px-2 py-1 font-semibold leading-tight text-gray-700 bg-gray-100 rounded-full">{{ $proof->verification_status }}</span>
                        @endif
                    </td>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">{{ optional($proof->verifier)->username ?? 'N/A' }}</td>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">{{ $proof->verified_at ? $proof->verified_at->format('Y-m-d H:i') : 'N/A' }}</td>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-right">
                        <a href="{{ route('admin.proof.show', $proof->proof_id) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-10 text-gray-500">No processed proofs found in history.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-5 py-5 bg-white border-t">
            {{ $proofs->links() }}
        </div>
    </div>
</div>
@endsection