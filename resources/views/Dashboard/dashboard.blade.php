@extends('layouts.adminLayout')

@section('content')
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        @vite('resources/css/adminDashboard.css')
        <title>Admin Dashboard</title>

    </head>

    <body>

        <header>Admin Dashboard</header>

        <div class="dashboard">

            <!-- KPIs -->
            <div class="cards">
                <div class="card">
                    <h2>143</h2>
                    <p>Total Packages</p>
                </div>
                <div class="card">
                    <h2>42</h2>
                    <p>In Transit</p>
                </div>
                <div class="card">
                    <h2>73</h2>
                    <p>Completed Deliveries</p>
                </div>
                <div class="card">
                    <h2>12</h2>
                    <p>Failed Deliveries</p>
                </div>
                <div class="card">
                    <h2>9</h2>
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
                            <th>Updated At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>#PKG00128</td>
                            <td>Delivering</td>
                            <td>Maria Lopez</td>
                            <td>Today, 10:32 AM</td>
                        </tr>
                        <tr>
                            <td>#PKG00126</td>
                            <td>Completed</td>
                            <td>John Carter</td>
                            <td>Today, 09:58 AM</td>
                        </tr>
                        <tr>
                            <td>#PKG00122</td>
                            <td>Failed</td>
                            <td>James Kim</td>
                            <td>Yesterday, 5:12 PM</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Driver Panel -->
            <div class="section">
                <h3>Driver Status</h3>
                <div class="driver-status">
                    <div class="driver online">
                        🚚 Alex Johnson — Online
                    </div>
                    <div class="driver busy">
                        🚚 Lisa Wong — Delivering
                    </div>
                    <div class="driver offline">
                        🚚 Robert Blake — Offline
                    </div>
                </div>
            </div>

            <!-- Package Status Chart Placeholder -->
            <div class="section">
                <h3>Package Status Summary</h3>
                <p>[Insert chart here — use Chart.js or similar if needed]</p>
            </div>

        </div>

    </body>

    </html>
@endsection
