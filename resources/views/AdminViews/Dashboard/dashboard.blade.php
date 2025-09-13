@extends('layouts.adminLayout')

@section('content')
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        @vite('resources/css/adminDashboard.css')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <title>Admin Dashboard</title>

    </head>

    <body>

        <header>Admin Dashboard</header>

        <div class="dashboard">

            <!-- KPIs -->
            <div class="cards">
                <div class="card">
                    <h2>{{ $totalPackages }}</h2>
                    <p>Total Packages</p>
                </div>
                <div class="card">
                    <h2>{{ $totalInTransitDeliveries }}</h2>
                    <p>In Transit</p>
                </div>
                <div class="card">
                    <h2>{{ $totalCompletedDeliveries }}</h2>
                    <p>Completed Deliveries</p>
                </div>
                <div class="card">
                    <h2>{{ $totalPickedUpDeliveries }}</h2>
                    <p>Picked Up Deliveries</p>
                </div>
                <div class="card">
                    <h2>{{ $totalScheduledDeliveries }}</h2>
                    <p>Scheduled Deliveries</p>
                </div>
                <div class="card">
                    <h2>{{ $totalFailedDeliveries }}</h2>
                    <p>Failed Deliveries</p>
                </div>
                <div class="card">
                    <h2>{{ $totalAvailableDrivers }}</h2>
                    <p>Available Drivers</p>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="section">
                <h3>Recent Activity</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Package ID</th>
                            <th>Status</th>
                            <th>Customer</th>
                            <th>Created On</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentPackages as $package)
                            <td>{{ $package->package_id }}</td>
                            <td>{{ $package->package_status }}</td>
                            <td>{{ optional($package->customer)->first_name . ' ' . optional($package->customer)->last_name }}
                            </td>

                            <td>{{ $package->created_at }}</td>
                            </tr>
                        @endforeach

                    </tbody>
                </table>
            </div>

            <!-- Driver Panel -->
            <div class="section">
                <h3>Driver Status</h3>
                </br>
                <div class="driver-status">

                    @foreach ($driverList as $driver)
                        @if ($driver->driver_status == 'AVAILABLE')
                            <div class="driver available">
                                {{ $driver->first_name . ' ' . $driver->last_name }} — {{ $driver->driver_status }}
                            </div>
                        @elseif ($driver->driver_status == 'BUSY')
                            <div class="driver busy">
                                {{ $driver->first_name . ' ' . $driver->last_name }} — {{ $driver->driver_status }}
                            </div>
                        @elseif($driver->driver_status == 'BUSY')
                            <div class="driver unavailable">
                                {{ $driver->first_name . ' ' . $driver->last_name }} — {{ $driver->driver_status }}
                            </div>
                        @else
                            <div class="driver neutral">
                                {{ $driver->first_name . ' ' . $driver->last_name }} — {{ $driver->driver_status }}
                            </div>
                        @endif
                    @endforeach

                </div>
            </div>

            <!-- Package Status Chart Placeholder -->
            <div class="section">
                <h3>Package Status Summary</h3>
                </br>
                <form method="GET" action="{{ route('admin.dashboard') }}">
                    <label for="statusFilter">Show:</label>
                    <select name="displayData" id="statusFilter" onchange="this.form.submit()">
                        <option value="packages" {{ $displayData === 'packages' ? 'selected' : '' }}>All</option>
                        <option value="DELIVERED" {{ $displayData === 'DELIVERED' ? 'selected' : '' }}>Delivered</option>
                        <option value="IN_TRANSIT" {{ $displayData === 'IN_TRANSIT' ? 'selected' : '' }}>In Transit
                        </option>
                        <option value="PENDING" {{ $displayData === 'PENDING' ? 'selected' : '' }}>Pending</option>
                        <option value="PICKED_UP" {{ $displayData === 'PICKED_UP' ? 'selected' : '' }}>Picked Up</option>
                    </select>
                </form>


                </br>
                <canvas id="packageChart" width="400" height="200"></canvas>

            </div>

        </div>

    </body>

    </html>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const packageByStatus = @json($packageByStatus);

            const labels = Object.keys(packageByStatus);
            const counts = Object.values(packageByStatus);

            const maxCount = Math.max(...counts);
            const yMax = maxCount + 1;

            const ctx = document.getElementById('packageChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Packages by Status',
                        data: counts,
                        backgroundColor: [
                            '#4CAF50', // Available
                            '#FF9800', // In transit
                            '#fcdf00', // Delivered
                            '#F44336', // Failed
                            '#9C27B0' // Others
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            suggestedMax: yMax,
                            precision: 0
                        }
                    }
                }
            });
        });
    </script>
@endsection
