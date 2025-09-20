@extends('layouts.customerLayout')

@section('content')
    @vite('resources/css/customerFeedback.css')

    <div class="container">
        <header>
            <h1>Your Delivered Packages</h1>
        </header>

        <main>
            @if (isset($packages['error']))
                <p>Error: {{ $packages['message'] ?? 'Unknown error' }}</p>
            @else
                <table class="packages-table">
                    <thead>
                        <tr>
                            <th>Package ID</th>
                            <th>Tracking Number</th>
                            <th>Status</th>
                            <th>Delivered On</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($packages as $package)
                            <tr>
                                <td>{{ $package['package_id'] }}</td>
                                <td>{{ $package['tracking_number'] }}</td>
                                <td>{{ $package['package_status'] }}</td>
                                <td>
                                    {{ $package['actual_delivery'] ? \Carbon\Carbon::parse($package['actual_delivery'])->format('Y-m-d') : 'N/A' }}
                                </td>

                                @if ($package['is_rated'])
                                    <td>Rated</td>
                                @else
                                    <td>
                                        <button type="button" class="btn btn-primary rate-btn" data-bs-toggle="modal"
                                            data-bs-target="#feedbackModal" data-package-id="{{ $package['package_id'] }}"
                                            data-delivery-id="{{ $package['delivery_id'] ?? '' }}">
                                            Rate
                                        </button>


                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </main>
    </div>


    @include('CustomerViews.feedback_form')


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const feedbackModal = document.getElementById('feedbackModal');

            if (feedbackModal) {
                feedbackModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;

                    const packageId = button.getAttribute('data-package-id');
                    const deliveryId = button.getAttribute('data-delivery-id');

                    const packageInput = document.getElementById('package_id');
                    const deliveryInput = document.getElementById('delivery_id');
                    const labelSpan = document.getElementById('modalPackageIdLabel');

                    if (packageInput) packageInput.value = packageId ?? '';
                    if (deliveryInput) deliveryInput.value = deliveryId ?? '';
                    if (labelSpan) labelSpan.textContent = packageId ?? '';
                });
            }
        });
    </script>
@endsection
