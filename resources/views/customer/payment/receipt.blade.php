<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt - {{ $payment->payment_id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #4a5568;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #4a5568;
        }
        .receipt-title {
            font-size: 20px;
            margin-top: 10px;
            color: #718096;
        }
        .info-section {
            margin-bottom: 30px;
        }
        .info-section h3 {
            color: #4a5568;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        .info-label {
            font-weight: 600;
            color: #718096;
        }
        .info-value {
            color: #2d3748;
        }
        .amount-section {
            background-color: #f7fafc;
            padding: 20px;
            border-radius: 8px;
            margin-top: 30px;
        }
        .total-amount {
            font-size: 24px;
            font-weight: bold;
            color: #2d3748;
            text-align: right;
        }
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            color: #718096;
            font-size: 12px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-completed {
            background-color: #c6f6d5;
            color: #22543d;
        }
        .status-pending {
            background-color: #fed7d7;
            color: #742a2a;
        }
        .status-refunded {
            background-color: #e9d5ff;
            color: #44337a;
        }
        @media print {
            body {
                margin: 0;
            }
            .container {
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="logo">TRACKPACK LOGISTICS</div>
            <div class="receipt-title">PAYMENT RECEIPT</div>
        </div>

        <!-- Receipt Info -->
        <div class="info-section">
            <h3>Receipt Information</h3>
            <div class="info-row">
                <span class="info-label">Receipt Number:</span>
                <span class="info-value">{{ $payment->payment_id }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Transaction ID:</span>
                <span class="info-value">{{ $payment->transaction_id }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Date:</span>
                <span class="info-value">{{ $payment->payment_date->format('F d, Y h:i A') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Status:</span>
                <span class="info-value">
                    <span class="status-badge status-{{ $payment->status }}">
                        {{ ucfirst($payment->status) }}
                    </span>
                </span>
            </div>
        </div>

        <!-- Customer Info -->
        <div class="info-section">
            <h3>Customer Information</h3>
            <div class="info-row">
                <span class="info-label">Customer ID:</span>
                <span class="info-value">{{ $payment->user_id }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Name:</span>
                <span class="info-value">{{ $payment->user->name ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Email:</span>
                <span class="info-value">{{ $payment->user->email ?? 'N/A' }}</span>
            </div>
        </div>

        <!-- Package Info -->
        <div class="info-section">
            <h3>Package Information</h3>
            <div class="info-row">
                <span class="info-label">Package ID:</span>
                <span class="info-value">{{ $payment->package_id }}</span>
            </div>
            @if($payment->package)
            <div class="info-row">
                <span class="info-label">Tracking Number:</span>
                <span class="info-value">{{ $payment->package->tracking_number }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Contents:</span>
                <span class="info-value">{{ $payment->package->package_contents }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Weight:</span>
                <span class="info-value">{{ $payment->package->package_weight }} kg</span>
            </div>
            @endif
        </div>

        <!-- Payment Details -->
        <div class="info-section">
            <h3>Payment Details</h3>
            <div class="info-row">
                <span class="info-label">Payment Method:</span>
                <span class="info-value">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</span>
            </div>
            @if($payment->notes)
            <div class="info-row">
                <span class="info-label">Notes:</span>
                <span class="info-value">{{ $payment->notes }}</span>
            </div>
            @endif
        </div>

        <!-- Amount Section -->
        <div class="amount-section">
            <div class="info-row">
                <span class="info-label">Shipping Cost:</span>
                <span class="info-value">RM {{ number_format($payment->amount, 2) }}</span>
            </div>
            <hr style="margin: 15px 0; border: none; border-top: 1px solid #cbd5e0;">
            <div class="info-row">
                <span class="info-label" style="font-size: 18px;">Total Amount Paid:</span>
                <span class="total-amount">RM {{ number_format($payment->amount, 2) }}</span>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Thank you for using TrackPack Logistics!</p>
            <p>This is a computer-generated receipt and does not require a signature.</p>
            <p>For inquiries, please contact support@trackpack.com</p>
            <p style="margin-top: 20px;">
                Generated on {{ now()->format('F d, Y h:i A') }}
            </p>
        </div>
    </div>

    <!-- Print Button (only shows on screen, not in print) -->
    <div class="no-print" style="text-align: center; margin-top: 30px;">
        <button onclick="window.print()" style="background-color: #4a5568; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin-right: 10px;">
            Print Receipt
        </button>
        <a href="{{ route('customer.billing.history') }}" style="background-color: #718096; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block;">
            Back to Billing
        </a>
    </div>

    <style>
        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>

    <script>
        // Optional: Auto-print when page loads
        // window.onload = function() { window.print(); }
        
        // Add keyboard shortcut for printing (Ctrl+P / Cmd+P already works by default)
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
                e.preventDefault();
                window.print();
            }
        });
    </script>
</body>
</html>