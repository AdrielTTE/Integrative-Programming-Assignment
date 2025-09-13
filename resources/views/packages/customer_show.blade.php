@extends('layouts.customerLayout')

@section('content')
<div class="container mx-auto px-4 py-8">
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="flex justify-between items-center mb-4">
        <h1 class="text-3xl font-bold text-gray-800">Package Details</h1>
        <a href="{{ route('customer.search') }}" class="text-blue-600 hover:underline">&larr; Back to Search</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white shadow-lg rounded-lg p-6">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm text-gray-500">Tracking Number</p>
                        <p class="text-2xl font-mono text-indigo-600">{{ $package->tracking_number }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-500">Status</p>
                        <p class="font-bold text-lg {{ $package->package_status == 'DELIVERED' ? 'text-green-600' : 'text-yellow-600' }}">
                            {{ $package->package_status }}
                        </p>
                    </div>
                </div>

                <hr class="my-6">

                <h3 class="font-semibold text-lg mb-4">Package Information</h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div><strong class="text-gray-600">Contents:</strong> {{ $package->package_contents }}</div>
                    <div><strong class="text-gray-600">Weight:</strong> {{ $package->package_weight }} kg</div>
                    <div><strong class="text-gray-600">Dimensions:</strong> {{ $package->package_dimensions }}</div>
                </div>

                <hr class="my-6">

                <h3 class="font-semibold text-lg mb-4">Addresses</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="font-bold text-gray-600 mb-1">Sender Address</p>
                        <p class="text-gray-800">{{ $package->sender_address }}</p>
                    </div>
                    <div>
                        <p class="font-bold text-gray-600 mb-1">Recipient Address</p>
                        <p class="text-gray-800">{{ $package->recipient_address }}</p>
                    </div>
                </div>
            </div>

            @if($package->package_status === 'DELIVERED' && isset($proof))
                <div class="bg-white shadow-lg rounded-lg p-6" id="proof-section">
                    <h3 class="font-semibold text-lg mb-4">Proof of Delivery</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-gray-50 p-4 rounded-lg">
                        <div>
                            @if($proof->proof_type == 'PHOTO' && $proof->proof_url)
                                <img src="{{ $proof->proof_url }}" alt="Proof of Delivery" class="w-full h-auto rounded-md border">
                            @elseif($proof->proof_type == 'SIGNATURE')
                                <div class="border rounded-md p-4 bg-white text-center h-full flex flex-col justify-center">
                                    <p class="text-gray-500 mb-2">Signed by:</p>
                                    <p class="text-2xl font-handwriting text-gray-800">{{ $proof->recipient_signature_name }}</p>
                                </div>
                            @else
                                <p class="text-gray-500">No visual proof available.</p>
                            @endif
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500 mb-2">System Validation</p>
                            @if($verificationDetails['is_valid'])
                                <p class="text-green-600 font-semibold text-sm">✓ Proof Authenticity Verified</p>
                            @else
                                <p class="text-red-600 font-semibold text-sm">✗ Proof has issues.</p>
                            @endif

                            <hr class="my-3">

                            <p class="text-sm font-medium text-gray-500 mb-2">Proof Metadata</p>
                            <div class="text-xs text-gray-700 space-y-1">
                                @forelse($metadata as $key => $value)
                                    <p><strong class="font-semibold">{{ $key }}:</strong> {{ $value }}</p>
                                @empty
                                    <p>No metadata available.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h4 class="font-semibold text-md mb-2">Report an Issue with this Delivery</h4>
                        <form action="{{ route('customer.proof.report', $proof->proof_id) }}" method="POST">
                            @csrf
                            <textarea name="reason" rows="3" class="w-full rounded-md border-gray-300 shadow-sm" required minlength="10" placeholder="e.g., This is not my house, the signature is incorrect..."></textarea>
                            <button type="submit" class="mt-2 w-full bg-red-500 text-white font-bold py-2 px-4 rounded-md hover:bg-red-600">Submit Report</button>
                        </form>
                    </div>
                </div>
            @endif
        </div>

        <div class="bg-white shadow-lg rounded-lg p-6">
            <h3 class="font-semibold text-lg mb-4">Tracking History</h3>
            <div class="flow-root">
                <ul role="list" class="-mb-8">
                    @forelse($history as $index => $event)
                        <li>
                            <div class="relative pb-8">
                                @if(!$loop->last)
                                    <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                @endif
                                <div class="relative flex space-x-3">
                                    <div>
                                        <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white"></span>
                                    </div>
                                    <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ $event['status'] }}</p>
                                            <p class="text-xs text-gray-500">{{ $event['action'] }}</p>
                                        </div>
                                        <div class="text-right text-xs whitespace-nowrap text-gray-500">
                                            <time>{{ $event['timestamp']->format('M d, Y') }}</time>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    @empty
                        <li><p class="text-gray-500">No history available yet.</p></li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection