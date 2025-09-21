@extends('layouts.driverLayout')

@section('content')
    <div class="container mx-auto px-4 py-6 max-w-2xl">
        <h1 class="text-2xl font-bold text-gray-800 mb-4">Complete Delivery - Proof Required</h1>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="mb-6 border-b pb-4">
                <h2 class="text-lg font-semibold mb-2">Package Information</h2>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-gray-600">Tracking Number:</p>
                        <p class="font-mono font-semibold">{{ $package->tracking_number }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Package ID:</p>
                        <p class="font-mono">{{ $package->package_id }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Recipient:</p>
                        <p>{{ $package->recipient_name }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Delivery Address:</p>
                        <p>{{ $package->recipient_address }}</p>
                    </div>
                </div>
            </div>

            <!-- THIS IS THE CRUCIAL FORM ELEMENT -->
            <form action="{{ route('driver.proof.store', $package->package_id) }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Proof Type <span class="text-red-500">*</span>
                        </label>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="radio" name="proof_type" value="SIGNATURE" class="mr-2" checked>
                                <span>Signature</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="proof_type" value="PHOTO" class="mr-2">
                                <span>Photo</span>
                            </label>
                        </div>
                    </div>

                    <div id="signature_section">
                        <label for="recipient_signature_name" class="block text-sm font-medium text-gray-700 mb-1">
                            Recipient's Full Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="recipient_signature_name" id="recipient_signature_name"
                            class="w-full rounded-md border-gray-300 shadow-sm" placeholder="Enter recipient's full name">
                    </div>

                    <div id="photo_section" class="hidden">
                        <label for="proof_photo" class="block text-sm font-medium text-gray-700 mb-1">
                            Delivery Photo <span class="text-red-500">*</span>
                        </label>
                        <input type="file" name="proof_photo" id="proof_photo" accept="image/*"
                            class="w-full rounded-md border-gray-300 shadow-sm">
                    </div>

                    <div>
                        <label for="delivery_location" class="block text-sm font-medium text-gray-700 mb-1">
                            Delivery Location
                        </label>
                        <input type="text" name="delivery_location" id="delivery_location"
                            class="w-full rounded-md border-gray-300 shadow-sm"
                            placeholder="e.g., Front door, Reception desk">
                    </div>

                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                            Additional Notes
                        </label>
                        <textarea name="notes" id="notes" rows="3" class="w-full rounded-md border-gray-300 shadow-sm"
                            placeholder="Any special delivery instructions or notes"></textarea>
                    </div>

                    <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
                        <p class="text-sm text-blue-800">
                            <strong>Important:</strong> By submitting this form, you confirm that the package
                            has been successfully delivered to the recipient or authorized person.
                        </p>
                    </div>

                    <div class="flex space-x-4">
                        <button type="submit"
                            class="flex-1 px-4 py-2 bg-green-600 text-white font-semibold rounded-md hover:bg-green-700">
                            Complete Delivery
                        </button>
                        <a href="{{ route('driver.status.index') }}"
                            class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 font-semibold rounded-md hover:bg-gray-300 text-center">
                            Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Handle proof type switching
        document.querySelectorAll('input[name="proof_type"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const signatureSection = document.getElementById('signature_section');
                const photoSection = document.getElementById('photo_section');
                
                if (this.value === 'SIGNATURE') {
                    signatureSection.classList.remove('hidden');
                    photoSection.classList.add('hidden');
                } else {
                    signatureSection.classList.add('hidden');
                    photoSection.classList.remove('hidden');
                }
            });
        });
    </script>
@endsection