@extends('layouts.customerLayout')

@section('content')
    @vite('resources/css/customerDashboard.css')

    {{-- CSRF Token for AJAX requests --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Set up CSRF token for all AJAX requests
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            // Package status data from backend (escaped for XSS prevention)
            const defaultData = {
                'pending': 0,
                'processing': 0,
                'in_transit': 0,
                'out_for_delivery': 0,
                'delivered': 0,
                'failed': 0,
                'cancelled': 0,
                'returned': 0
            };
            const packageData = @json($packageStatusData ?? null) || defaultData;

            // Validate data before using
            const validatedData = {};
            const allowedStatuses = ['pending', 'processing', 'in_transit', 'out_for_delivery', 'delivered', 'failed', 'cancelled', 'returned'];
            
            allowedStatuses.forEach(status => {
                validatedData[status] = Number(packageData[status]) || 0;
            });

            const labels = Object.keys(validatedData).map(key => key.replace('_', ' ').toUpperCase());
            const counts = Object.values(validatedData);

            // Only create chart if we have data
            if (counts.some(count => count > 0)) {
                const ctx = document.getElementById('packageStatusChart');
                if (ctx) {
                    new Chart(ctx.getContext('2d'), {
                        type: 'doughnut',
                        data: {
                            labels: labels,
                            datasets: [{
                                data: counts,
                                backgroundColor: [
                                    '#f59e0b', // Pending
                                    '#3b82f6', // Processing
                                    '#ef4444', // In Transit
                                    '#8b5cf6', // Out for Delivery
                                    '#10b981', // Delivered
                                    '#dc2626', // Failed
                                    '#6b7280', // Cancelled
                                    '#f97316'  // Returned
                                ],
                                borderWidth: 0,
                                cutout: '60%'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'right',
                                    labels: {
                                        padding: 20,
                                        font: { 
                                            size: 13,
                                            family: 'Inter, system-ui, -apple-system, sans-serif'
                                        },
                                        usePointStyle: true,
                                        pointStyle: 'circle',
                                        boxWidth: 8
                                    }
                                }
                            }
                        }
                    });
                }
            }

            // Monthly activity chart with validated data
            const monthlyData = @json($monthlyActivityData ?? []);
            const timelineCtx = document.getElementById('deliveryTimelineChart');
            if (timelineCtx) {
                // If no data, create sample data or empty chart
                let validMonthlyData = [];
                
                if (Array.isArray(monthlyData) && monthlyData.length > 0) {
                    validMonthlyData = monthlyData.filter(item => 
                        item && typeof item.month === 'string' && typeof item.count === 'number'
                    ).slice(0, 12);
                } else {
                    // Create empty data for the last 6 months
                    const months = ['6 months ago', '5 months ago', '4 months ago', '3 months ago', '2 months ago', 'Last month'];
                    validMonthlyData = months.map(month => ({ month, count: 0 }));
                }

                new Chart(timelineCtx.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: validMonthlyData.map(item => item.month),
                        datasets: [{
                            label: 'Packages Sent',
                            data: validMonthlyData.map(item => Math.max(0, item.count)),
                            borderColor: '#3b82f6',
                            backgroundColor: 'rgba(59, 130, 246, 0.05)',
                            tension: 0.3,
                            fill: true,
                            borderWidth: 2,
                            pointBackgroundColor: '#3b82f6',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2,
                            pointRadius: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: { 
                                beginAtZero: true,
                                grid: {
                                    color: '#f3f4f6'
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            }

            // Auto-refresh notifications every 5 minutes (optional)
            setInterval(function() {
                fetch('{{ route("customer.notification") }}', {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    }
                })
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    // Update notification count if needed
                    const notificationBadge = document.querySelector('.notification-badge');
                    if (notificationBadge && data.unread_count) {
                        notificationBadge.textContent = Math.min(99, data.unread_count);
                        notificationBadge.style.display = data.unread_count > 0 ? 'inline' : 'none';
                    }
                })
                .catch(error => console.log('Notification refresh failed:', error));
            }, 300000); // 5 minutes
        });
    </script>

    <div class="page-header">
        <div>
            <h1>Dashboard</h1>
            <p>Welcome back, {{ e(auth()->user()->username ?? 'Customer') }}! Here's your package overview.</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('customer.packages.create') }}" class="btn-primary">
                <span>Create Package</span>
            </a>
        </div>
    </div>

    <div class="dashboard-layout">
        
        <!-- Stats Overview -->
        <div class="stats-section">
            <div class="stat-item">
                <div class="stat-value">{{ $totalPackages ?? 0 }}</div>
                <div class="stat-label">Total Packages</div>
            </div>

            <div class="stat-item">
                <div class="stat-value">{{ $activeDeliveries ?? 0 }}</div>
                <div class="stat-label">Active Deliveries</div>
            </div>

            <div class="stat-item">
                <div class="stat-value">{{ $deliveredPackages ?? 0 }}</div>
                <div class="stat-label">Delivered</div>
            </div>

            <div class="stat-item">
                <div class="stat-value">RM{{ number_format($totalSpent ?? 0, 2) }}</div>
                <div class="stat-label">Total Spent</div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="content-grid">
            
            <!-- Recent Packages -->
            <div class="content-card packages-card">
                <div class="card-header">
                    <h3>Recent Packages</h3>
                    <a href="{{ route('customer.packages.index') }}" class="text-link">View all</a>
                </div>
                <div class="packages-container">
                    @forelse($recentPackages ?? [] as $package)
                        @php
                            // Get state information securely
                            $state = $package->getState();
                            $statusColor = $state->getStatusColor();
                            $location = $state->getCurrentLocation();
                        @endphp
                        <div class="package-row">
                            <div class="package-main">
                                <div class="package-header">
                                    <span class="package-number">{{ e($package->package_id) }}</span>
                                    <span class="tracking-code">{{ e($package->tracking_number) }}</span>
                                </div>
                                <div class="package-destination">{{ e(Str::limit($package->recipient_address, 45)) }}</div>
                                <div class="package-meta">
                                    <span class="package-date">{{ $package->created_at->format('M d, Y') }}</span>
                                    @if($location)
                                        <span class="package-location">{{ e($location) }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="package-status">
                                <span class="status-pill {{ e($statusColor) }}">
                                    {{ e(ucwords(str_replace('_', ' ', $package->package_status))) }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state">
                            <div class="empty-icon">📦</div>
                            <p>No packages yet</p>
                            <a href="{{ route('customer.packages.create') }}">Create your first package</a>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Package Status Chart -->
            <div class="content-card chart-card">
                <div class="card-header">
                    <h3>Package Status Distribution</h3>
                </div>
                <div class="chart-wrapper">
                    <canvas id="packageStatusChart"></canvas>
                </div>
            </div>

            <!-- Monthly Activity Timeline -->
            <div class="content-card timeline-card">
                <div class="card-header">
                    <h3>Monthly Activity</h3>
                </div>
                <div class="chart-wrapper timeline-wrapper">
                    <canvas id="deliveryTimelineChart"></canvas>
                </div>
            </div>

        </div>

        <!-- Notifications Sidebar -->
        <div class="notifications-sidebar">
            <h4>Recent Updates</h4>
            <div class="notifications-list">
                @forelse($recentNotifications ?? [] as $notification)
                    <div class="notification-row">
                        <div class="notification-text">{{ e($notification['message']) }}</div>
                        <div class="notification-date">
                            {{ Carbon\Carbon::parse($notification['created_at'])->diffForHumans() }}
                        </div>
                    </div>
                @empty
                    <div class="empty-notifications">
                        <p>No recent updates</p>
                    </div>
                @endforelse
            </div>
        </div>

    </div>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif;
            background: #fafbfc;
            color: #1f2937;
            line-height: 1.5;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 32px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e5e7eb;
        }

        .page-header h1 {
            font-size: 28px;
            font-weight: 700;
            margin: 0 0 4px 0;
            color: #111827;
        }

        .page-header p {
            font-size: 16px;
            color: #6b7280;
            margin: 0;
        }

        .header-actions {
            flex-shrink: 0;
        }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            background: #3b82f6;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            transition: background 0.2s ease;
        }

        .btn-primary:hover {
            background: #2563eb;
            color: white;
        }

        .dashboard-layout {
            display: grid;
            grid-template-columns: 1fr 280px;
            gap: 24px;
        }

        .stats-section {
            grid-column: 1 / -1;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-item {
            background: white;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
        }

        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 4px;
        }

        .stat-label {
            font-size: 14px;
            color: #6b7280;
            font-weight: 500;
        }

        .content-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 24px;
        }

        .content-card {
            background: white;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            overflow: hidden;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid #f3f4f6;
        }

        .card-header h3 {
            font-size: 18px;
            font-weight: 600;
            color: #111827;
            margin: 0;
        }

        .text-link {
            color: #3b82f6;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }

        .text-link:hover {
            color: #2563eb;
        }

        /* Package Card */
        .packages-card {
            min-height: 400px;
        }

        .packages-container {
            padding: 0;
        }

        .package-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 20px;
            border-bottom: 1px solid #f3f4f6;
            transition: background 0.15s ease;
        }

        .package-row:hover {
            background: #f9fafb;
        }

        .package-row:last-child {
            border-bottom: none;
        }

        .package-main {
            flex: 1;
        }

        .package-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 6px;
        }

        .package-number {
            font-weight: 600;
            color: #111827;
            font-size: 14px;
        }

        .tracking-code {
            font-family: 'JetBrains Mono', 'Courier New', monospace;
            font-size: 12px;
            color: #6b7280;
            background: #f3f4f6;
            padding: 2px 6px;
            border-radius: 4px;
        }

        .package-destination {
            font-size: 14px;
            color: #374151;
            margin-bottom: 6px;
        }

        .package-meta {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .package-date, .package-location {
            font-size: 13px;
            color: #9ca3af;
        }

        .package-location::before {
            content: '•';
            margin-right: 8px;
        }

        .package-status {
            flex-shrink: 0;
        }

        .status-pill {
            padding: 4px 10px;
            border-radius: 16px;
            font-size: 12px;
            font-weight: 500;
            text-transform: capitalize;
        }

        .status-pill.warning { background: #fef3c7; color: #92400e; }
        .status-pill.info { background: #dbeafe; color: #1e40af; }
        .status-pill.primary { background: #dbeafe; color: #1d4ed8; }
        .status-pill.success { background: #dcfce7; color: #166534; }
        .status-pill.danger { background: #fee2e2; color: #991b1b; }
        .status-pill.secondary { background: #f3f4f6; color: #374151; }

        /* Chart Cards */
        .chart-card {
            height: 320px;
        }

        .chart-wrapper {
            padding: 20px;
            height: calc(100% - 61px);
        }

        .timeline-card {
            height: 280px;
        }

        .timeline-wrapper {
            height: calc(100% - 61px);
        }

        /* Notifications Sidebar */
        .notifications-sidebar {
            background: white;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            padding: 20px;
            height: fit-content;
            position: sticky;
            top: 20px;
        }

        .notifications-sidebar h4 {
            font-size: 16px;
            font-weight: 600;
            color: #111827;
            margin: 0 0 16px 0;
        }

        .notifications-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .notification-row {
            padding: 12px 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .notification-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .notification-text {
            font-size: 14px;
            color: #374151;
            margin-bottom: 4px;
            line-height: 1.4;
        }

        .notification-date {
            font-size: 12px;
            color: #9ca3af;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #6b7280;
        }

        .empty-icon {
            font-size: 48px;
            margin-bottom: 12px;
            opacity: 0.5;
        }

        .empty-state p {
            margin: 0 0 8px 0;
            font-size: 16px;
        }

        .empty-state a {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 500;
        }

        .empty-notifications {
            text-align: center;
            padding: 20px;
            color: #9ca3af;
            font-size: 14px;
        }

        .empty-notifications p {
            margin: 0;
        }

        @media (max-width: 1024px) {
            .dashboard-layout {
                grid-template-columns: 1fr;
            }
            
            .notifications-sidebar {
                position: static;
            }
        }

        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                gap: 16px;
                align-items: stretch;
            }

            .header-actions {
                align-self: flex-start;
            }

            .stats-section {
                grid-template-columns: repeat(2, 1fr);
            }

            .package-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }

            .package-status {
                align-self: flex-end;
            }
        }
    </style>

@endsection