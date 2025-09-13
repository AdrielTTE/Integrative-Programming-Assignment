@extends('layouts.customerLayout')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-4">Proof of Delivery</h1>

    @if($proof)
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <div class="grid grid-cols-1 md:grid-cols-2">
                <!-- Proof Image or Signature -->
                <div class="p-6">
                    <h2 class="text-xl font-semibold text-gray-700 mb-4">Delivery Evidence</h2>
                    @if($proof->proof_type == 'PHOTO' && $proof->proof_url)
                        <img src="{{ $proof->proof_url }}" alt="Proof of Delivery" class="w-full h-auto rounded-md border">
                    @elseif($proof->proof_type == 'SIGNATURE')
                        <div class="border rounded-md p-4 bg-gray-50 text-center">
                            <p class="text-gray-500 mb-2">Signed by:</p>
                            <p class="text-2xl font-handwriting text-gray-800">{{ $proof->recipient_signature_name }}</p>
                        </div>
                    @else
                        <p class="text-gray-500">No visual proof available.</p>
                    @endif
                </div>

                <!-- Details and Verification -->
                <div class="p-6 bg-gray-50">
                    <h2 class="text-xl font-semibold text-gray-700 mb-4">Verification Details</h2>
                    <div class="space-y-4">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Delivery ID</p>
                            <p class="text-md text-gray-900">{{ $proof->delivery_id }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Proof Type</p>
                            <p class="text-md text-gray-900">{{ $proof->proof_type }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Timestamp</p>
                            <p class="text-md text-gray-900">{{ $proof->timestamp_created->format('F j, Y, g:i a') }}</p>
                        </div>
                        <div class="border-t pt-4">
                            <p class="text-sm font-medium text-gray-500 mb-2">System Validation ({{ $verificationDetails['strategy'] }})</p>
                            @if($verificationDetails['is_valid'])
                                <p class="text-green-600 font-semibold">✓ Proof is valid.</p>
                            @else
                                <p class="text-red-600 font-semibold">✗ Proof has issues.</p>
                            @endif
                            <ul class="list-disc list-inside text-sm text-gray-600 mt-2">
                                @foreach($verificationDetails['details'] as $detail)
                                    <li>{{ $detail }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                     <div class="mt-6">
                        <button class="w-full bg-red-500 text-white py-2 rounded-md hover:bg-red-600">Report an Issue</button>
                    </div>
                </div>
            </div>
        </div>
    @else
        <p class="text-center text-gray-500">No proof of delivery found for this delivery.</p>
    @endif
</div>
@endsection