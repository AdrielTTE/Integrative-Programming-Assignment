@extends('layouts.adminLayout')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-3xl font-bold text-gray-800 mb-4">Verify Proof of Delivery</h1>
        <a href="{{ route('admin.proof.history') }}" class="text-indigo-600 hover:text-indigo-900">&larr; Back</a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="md:col-span-2 bg-white shadow-lg rounded-lg p-6">
            <h2 class="text-xl font-semibold text-gray-700 mb-4">Delivery Evidence</h2>

            @if($proof->proof_type == 'PHOTO' && $proof->proof_url)
                <img src="{{ $proof->proof_url }}" alt="Proof of Delivery" class="w-full h-auto rounded-md border">
            @elseif($proof->proof_type == 'SIGNATURE')
                <div class="border rounded-md p-8 bg-gray-50 text-center">
                    <p class="text-gray-500 mb-2">Signed by:</p>
                    <p class="text-4xl font-handwriting text-gray-800">{{ $proof->recipient_signature_name }}</p>
                </div>
            @else
                <p class="text-gray-500">No visual proof available for this entry.</p>
            @endif
        </div>

        <div class="bg-white shadow-lg rounded-lg p-6">
            <h2 class="text-xl font-semibold text-gray-700 mb-4">Verification & Actions</h2>

            <div class="space-y-3 text-sm">
                <p><strong>Package ID:</strong> {{ optional($proof->delivery->package)->package_id ?? 'N/A' }}</p>
                <p><strong>Customer:</strong> {{ optional($proof->delivery->package->customer)->first_name ?? 'N/A' }} {{ optional($proof->delivery->package->customer)->last_name ?? '' }}</p>
                <p><strong>Proof Type:</strong> {{ $proof->proof_type }}</p>
                <p><strong>Timestamp:</strong> {{ $proof->timestamp_created->format('F j, Y, g:i a') }}</p>
            </div>

            <div class="border-t my-4"></div>

            <div>
                <h3 class="font-semibold text-gray-600 mb-2">System Validation ({{ $verificationDetails['strategy'] }})</h3>

                @if($verificationDetails['is_valid'])
                    <p class="text-green-600 font-semibold text-sm">✓ Validation Passed</p>
                @else
                    <p class="text-red-600 font-semibold text-sm">✗ Validation Failed</p>
                @endif

                <ul class="list-disc list-inside text-xs text-gray-600 mt-2">
                    @foreach($verificationDetails['details'] as $detail)
                        <li>{{ $detail }}</li>
                    @endforeach
                </ul>
            </div>

            <div class="border-t my-4"></div>

            @if(
                $proof->verification_status == 'PENDING' ||
                $proof->verification_status == 'NEEDS_RESUBMISSION' ||
                $proof->verification_status == 'REJECTED'
            )
                <form action="{{ route('admin.proof.updateStatus', $proof->proof_id) }}" method="POST">
                    @csrf
                    <div class="space-y-3">
                        @if($proof->verification_status != 'APPROVED')
                            <div>
                                <label for="rejection_reason" class="block text-sm font-medium text-gray-700">Reason / Notes</label>
                                <textarea name="rejection_reason" id="rejection_reason" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ $proof->notes }}</textarea>
                            </div>
                        @endif

                        <button type="submit" name="action" value="approve" class="w-full bg-green-500 text-white py-2 rounded-md hover:bg-green-600 font-bold">Approve</button>

                        @if($proof->verification_status != 'REJECTED')
                            <button type="submit" name="action" value="resubmit" class="w-full bg-yellow-500 text-white py-2 rounded-md hover:bg-yellow-600">Request Resubmission</button>
                            <button type="submit" name="action" value="reject" class="w-full bg-red-500 text-white py-2 rounded-md hover:bg-red-600">Reject Proof</button>
                        @endif
                    </div>
                </form>
            @else
                <div class="text-center p-4 bg-gray-100 rounded-md">
                    <p class="font-semibold text-gray-700">This proof has already been processed.</p>
                    <p class="text-sm text-gray-500">Status: {{ $proof->verification_status }}</p>
                    <a href="{{ route('admin.proof.history') }}" class="text-indigo-600 hover:underline mt-2 inline-block">View History</a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection