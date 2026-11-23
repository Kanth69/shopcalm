<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Dispatch Run-Sheet ({{ $orders->count() }} Orders) — ShopCalm Logistics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            background: #f1f5f9;
            color: #0f172a;
            padding: 24px;
            font-size: 13px;
        }
        .manifest-container {
            max-width: 1100px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 14px;
            padding: 32px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.06);
            border: 1px solid #e2e8f0;
        }
        .table th {
            background-color: #0f172a !important;
            color: #ffffff !important;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 10px 8px;
        }
        .table td {
            padding: 10px 8px;
            vertical-align: middle;
            border-bottom: 1px solid #e2e8f0;
        }
        .badge-cod {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
            font-weight: 800;
            padding: 3px 6px;
            border-radius: 4px;
            font-size: 10.5px;
        }
        .badge-prepaid {
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
            font-weight: 800;
            padding: 3px 6px;
            border-radius: 4px;
            font-size: 10.5px;
        }
        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }
            .manifest-container {
                box-shadow: none;
                border: none;
                padding: 10px;
                max-width: 100%;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

<div class="manifest-container">
    <!-- Screen Header Action Bar -->
    <div class="d-flex justify-content-between align-items-center mb-4 no-print border-bottom pb-3">
        <div>
            <h5 class="fw-bold text-dark mb-0">Daily Delivery Run-Sheet Manifest</h5>
            <div class="text-muted small">Generated on {{ now()->format('d M Y, h:i A') }} &bull; {{ $orders->count() }} Orders</div>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-primary fw-bold px-4 rounded-pill shadow-sm" style="background: #0284c7; border-color: #0284c7;">
                <i class="bi bi-printer-fill me-1"></i> Print Manifest Sheet
            </button>
            <button onclick="window.close()" class="btn btn-outline-secondary rounded-pill px-3">
                Close
            </button>
        </div>
    </div>

    <!-- Printable Header Banner -->
    <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="fs-4 fw-bolder text-dark" style="letter-spacing: -0.5px;">Shop<span style="color: #0284c7;">Calm</span></span>
                <span class="badge bg-dark text-white font-monospace" style="font-size: 10px;">EXPRESS DISPATCH MANIFEST</span>
            </div>
            <div class="text-secondary small">Warehouse Hub #01 &bull; Bengaluru Local Fleet</div>
        </div>
        <div class="text-end">
            <div class="fw-bold text-dark fs-6">Date: {{ now()->format('d M Y') }}</div>
            <div class="text-muted small">Time Window: Express Delivery</div>
        </div>
    </div>

    <!-- KPI Summary Pills -->
    @php
        $totalAmt = $orders->sum('total_amount');
        $codAmt = $orders->where('payment_method', 'cod')->sum('total_amount');
        $codCount = $orders->where('payment_method', 'cod')->count();
        $prepaidCount = $orders->where('payment_method', '!=', 'cod')->count();
    @endphp
    <div class="row g-2 mb-3">
        <div class="col-3">
            <div class="p-2 border rounded bg-light text-center">
                <div class="text-muted small" style="font-size: 10.5px;">TOTAL ORDERS</div>
                <div class="fs-5 fw-bold text-dark">{{ $orders->count() }} Parcels</div>
            </div>
        </div>
        <div class="col-3">
            <div class="p-2 border rounded bg-light text-center">
                <div class="text-muted small" style="font-size: 10.5px;">CASH TO COLLECT (COD)</div>
                <div class="fs-5 fw-bold text-amber-600" style="color: #b45309;">₹{{ number_format($codAmt, 2) }}</div>
            </div>
        </div>
        <div class="col-3">
            <div class="p-2 border rounded bg-light text-center">
                <div class="text-muted small" style="font-size: 10.5px;">COD PARCELS</div>
                <div class="fs-5 fw-bold text-dark">{{ $codCount }} Boxes</div>
            </div>
        </div>
        <div class="col-3">
            <div class="p-2 border rounded bg-light text-center">
                <div class="text-muted small" style="font-size: 10.5px;">PREPAID PARCELS</div>
                <div class="fs-5 fw-bold text-success">{{ $prepaidCount }} Boxes (₹0)</div>
            </div>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="table-responsive">
        <table class="table table-bordered mb-0">
            <thead>
                <tr>
                    <th style="width: 40px;" class="text-center">Stop</th>
                    <th style="width: 120px;">Order #</th>
                    <th style="width: 170px;">Customer Details</th>
                    <th>Delivery Address</th>
                    <th style="width: 130px;">Collect Cash</th>
                    <th style="width: 120px;">Rider / Status</th>
                    <th style="width: 140px;">Customer Sig / Ack</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $idx => $order)
                    @php
                        $isCod = $order->payment_method === 'cod';
                    @endphp
                    <tr>
                        <td class="text-center fw-bold">{{ $idx + 1 }}</td>
                        <td>
                            <div class="fw-bold font-monospace text-primary">#{{ $order->order_number }}</div>
                            <div class="text-muted" style="font-size: 10px;">{{ $order->created_at->format('h:i A') }}</div>
                        </td>
                        <td>
                            <div class="fw-bold text-dark">{{ $order->shipping_name }}</div>
                            <div class="text-muted font-monospace" style="font-size: 11px;">{{ $order->shipping_phone }}</div>
                        </td>
                        <td>
                            <div class="text-dark" style="font-size: 11.5px; line-height: 1.25;">
                                {{ $order->shipping_address }}, {{ $order->shipping_city }} - {{ $order->shipping_zip }}
                            </div>
                            @if($order->notes)
                                <div class="text-muted small fst-italic mt-0.5" style="font-size: 10px;">Note: {{ $order->notes }}</div>
                            @endif
                        </td>
                        <td>
                            @if($isCod)
                                <div class="fw-bold text-danger fs-6">₹{{ number_format($order->total_amount, 2) }}</div>
                                <span class="badge-cod">COLLECT CASH</span>
                            @else
                                <div class="fw-bold text-success">₹0.00</div>
                                <span class="badge-prepaid">PREPAID ✓</span>
                            @endif
                        </td>
                        <td>
                            <div class="fw-semibold text-dark small">{{ $order->rider_name ?? 'Unassigned' }}</div>
                            <div class="text-muted text-uppercase" style="font-size: 9.5px;">{{ $order->status }}</div>
                        </td>
                        <td style="height: 48px; border: 1.5px dashed #cbd5e1; background: #fafafa;">
                            <!-- Blank box for physical customer signature -->
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No orders found for this run-sheet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Footer Signatures -->
    <div class="row mt-4 pt-3 border-top g-3">
        <div class="col-4">
            <div class="border-top border-dark pt-1 small fw-bold text-center">
                Hub Dispatcher Signature
            </div>
        </div>
        <div class="col-4">
            <div class="border-top border-dark pt-1 small fw-bold text-center">
                Fleet Rider Handover Signature
            </div>
        </div>
        <div class="col-4">
            <div class="border-top border-dark pt-1 small fw-bold text-center">
                Total Cash Reconciled (₹{{ number_format($codAmt, 2) }})
            </div>
        </div>
    </div>
</div>

</body>
</html>
