@extends('layouts.adminLayout')

@section('content')
    @vite('resources/css/adminDashboard.css')


    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const dataForGraph = @json($dataForGraph);

            const labels = Object.keys(dataForGraph);
            const counts = Object.values(dataForGraph);

            const maxCount = Math.max(...counts);
            const yMax = maxCount + 1;

            const ctx = document.getElementById('packageChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: labels,
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
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
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



    <header>Admin Dashboard</header>

    <div class="dashboard">

        <!-- KPIs -->
        <div class="cards">
            <div class="card">
                <h2>{{ $totalPackages }}</h2>
                <p>Total Packages</p>
            </div>
            <div class="card">

                <h2>{{ $totalInTransitDeliveries['IN_TRANSIT'] }}</h2>
                <p>In Transit</p>
            </div>
            <div class="card">
                <h2>{{ $totalCompletedDeliveries['DELIVERED'] }}</h2>
                <p>Completed Deliveries</p>
            </div>
            <div class="card">
                <h2>{{ $totalPickedUpDeliveries['PICKED_UP'] }}</h2>
                <p>Picked Up Deliveries</p>
            </div>
            <div class="card">
                <h2>{{ $totalScheduledDeliveries['SCHEDULED'] }}</h2>
                <p>Scheduled Deliveries</p>
            </div>
            <div class="card">
                <h2>{{ $totalFailedDeliveries['FAILED'] }}</h2>
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
                        <td>{{ $package['package_id'] }}</td>
                        <td>{{ $package['package_status'] }}</td>
                        <td>
                            {{ ($package['customer']['first_name'] ?? '') . ' ' . ($package['customer']['last_name'] ?? '') }}
                        </td>


                        <td>{{ $package['created_at'] }}</td>
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
                    @if ($driver['driver_status'] === 'AVAILABLE')
                        <div class="driver available">
                            {{ $driver['first_name'] }} — {{ $driver['driver_status'] }}
                        </div>
                    @elseif ($driver['driver_status'] === 'BUSY')
                        <div class="driver busy">
                            {{ $driver['first_name'] }} — {{ $driver['driver_status'] }}
                        </div>
                    @elseif ($driver['driver_status'] === 'UNAVAILABLE')
                        <div class="driver unavailable">
                            {{ $driver['first_name'] }} — {{ $driver['driver_status'] }}
                        </div>
                    @else
                        <div class="driver neutral">
                            {{ $driver['first_name'] }} — {{ $driver['driver_status'] }}
                        </div>
                    @endif
                @endforeach


            </div>
        </div>

        <!-- Package Status Chart Placeholder -->
        <div class="section">
            <h3>Status Summary</h3>
            </br>
            <form method="GET" action="{{ route('admin.dashboard') }}">
                <label for="statusFilter">Show:</label>
                <select name="displayData" id="statusFilter" onchange="this.form.submit()">
                    <option value="packages" {{ $displayData === 'packages' ? 'selected' : '' }}>Packages</option>
                    <option value="deliveries" {{ $displayData === 'deliveries' ? 'selected' : '' }}>Deliveries</option>
                    <option value="vehicles" {{ $displayData === 'vehicles' ? 'selected' : '' }}>Vehicles
                    </option>
                    <option value="customers" {{ $displayData === 'customers' ? 'selected' : '' }}>Customers</option>

                </select>
            </form>


            </br>
            <canvas id="packageChart" width="400" height="200"></canvas>

        </div>

    </div>
@endsection
