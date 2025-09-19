@extends('layouts.adminLayout')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2>Package Details: {{ $package->tracking_number }}</h2>
                <a href="{{ route('admin.packages.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif

            <div class="row">
                <!-- Package Information -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h4 class="card-title">Package Information</h4>
                        </div>
                        <div class="card-body">
                            <table class="table table-striped">
                                <tr>
                                    <th width="40%">Package ID:</th>
                                    <td><code>{{ $package->package_id }}</code></td>
                                </tr>
                                <tr>
                                    <th>Tracking Number:</th>
                                    <td><strong>{{ $package->tracking_number }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        <span class="badge badge-{{ $stateInfo['color'] }} badge-lg">
                                            {{ ucwords(str_replace('_', ' ', $stateInfo['name'])) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Current Location:</th>
                                    <td>{{ $stateInfo['location'] }}</td>
                                </tr>
                                <tr>
                                    <th>Priority:</th>
                                    <td>{{ ucfirst($package->priority ?? 'standard') }}</td>
                                </tr>
                                <tr>
                                    <th>Weight:</th>
                                    <td>{{ $package->package_weight }} kg</td>
                                </tr>
                                <tr>
                                    <th>Dimensions:</th>
                                    <td>{{ $package->package_dimensions ?: 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Contents:</th>
                                    <td>{{ $package->package_contents }}</td>
                                </tr>
                                <tr>
                                    <th>Shipping Cost:</th>
                                    <td>RM {{ number_format($package->shipping_cost ?? 0, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Created:</th>
                                    <td>{{ $package->created_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>Estimated Delivery:</th>
                                    <td>{{ $package->estimated_delivery ? $package->estimated_delivery->format('Y-m-d') : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Actual Delivery:</th>
                                    <td>{{ $package->actual_delivery ? $package->actual_delivery->format('Y-m-d H:i:s') : 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Customer Information -->
                    <div class="card mt-3">
                        <div class="card-header bg-info text-white">
                            <h4 class="card-title">Customer Information</h4>
                        </div>
                        <div class="card-body">
                            @if($package->user)
                                <table class="table table-striped">
                                    <tr>
                                        <th width="40%">Customer ID:</th>
                                        <td>{{ $package->user->user_id }}</td>
                                    </tr>
                                    <tr>
                                        <th>Username:</th>
                                        <td>{{ $package->user->username }}</td>
                                    </tr>
                                    <tr>
                                        <th>Email:</th>
                                        <td>{{ $package->user->email }}</td>
                                    </tr>
                                    <tr>
                                        <th>Phone:</th>
                                        <td>{{ $package->user->phone_number ?: 'N/A' }}</td>
                                    </tr>
                                </table>
                            @else
                                <p class="text-muted">Customer information not available</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="col-md-6">
                    <!-- State Management and Actions remain the same -->
                    <!-- ... rest of the view code ... -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection