<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Packing Slip #{{ $order->order_number }} — ShopCalm Warehouse</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            font-family: system-ui, -apple-system, sans-serif;
            background: #f1f5f9;
            color: #1e293b;
            padding: 2rem 0;
        }
        .slip-sheet {
            max-width: 800px;
            margin: 0 auto;
            background: #ffffff;
            padding: 2.5rem;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            border: 2px dashed #cbd5e1;
        }
        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }
            .slip-sheet {
                box-shadow: none;
                padding: 0;
                border: 1px solid #94a3b8;
                border-radius: 0;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="d-flex justify-content-end gap-2 mb-3 no-print max-w-800 mx-auto" style="max-width: 800px;">
        <button onclick="window.print()" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold">
            <i class="bi bi-printer-fill me-1"></i> Print Packing Slip
        </button>
        <button onclick="window.close()" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            Close
        </button>
    </div>

    <div class="slip-sheet">
        <!-- Slip Header -->
        <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
            <div>
                <h4 class="fw-bolder text-dark mb-0">WAREHOUSE PACKING SLIP</h4>
                <div class="text-muted small">ShopCalm Fulfillment Center #01</div>
            </div>
            <div class="text-end">
                <div class="h5 fw-bolder font-monospace text-primary mb-0">#{{ $order->order_number }}</div>
                <div class="text-muted small">{{ $order->created_at->format('d M Y, h:i A') }}</div>
            </div>
        </div>

        <!-- Shipping Label Box -->
        <div class="p-3 bg-light border rounded-3 mb-4">
            <div class="row">
                <div class="col-8">
                    <div class="small fw-bold text-uppercase text-muted mb-1">Ship To Customer:</div>
                    <h5 class="fw-bold text-dark mb-1">{{ $order->shipping_name }}</h5>
                    <div class="small text-dark" style="line-height: 1.4;">
                        {{ $order->shipping_address }}<br>
                        <strong>{{ $order->shipping_city }}, {{ $order->shipping_state }} - {{ $order->shipping_zip }}</strong><br>
                        Phone: <strong>{{ $order->shipping_phone }}</strong>
                    </div>
                </div>
                <div class="col-4 border-start text-end">
                    <div class="small fw-bold text-uppercase text-muted mb-1">Package Info:</div>
                    <div class="small text-dark">
                        <strong>Items:</strong> {{ $order->items->sum('quantity') }} Units<br>
                        <strong>Method:</strong> {{ ($order->payment_method === 'wallet' || ($order->total_amount <= 0 && $order->wallet_amount_used > 0)) ? 'SHOPCALM WALLET (100% PAID)' : strtoupper($order->payment_method) }}<br>
                        <strong>Courier:</strong> {{ $order->courier_partner ?? 'Pending' }}<br>
                        <strong>AWB:</strong> {{ $order->tracking_number ?? 'Pending' }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Item Checklist Table -->
        <table class="table table-bordered align-middle mb-4">
            <thead class="table-light small">
                <tr>
                    <th style="width: 10%;" class="text-center">Check</th>
                    <th style="width: 25%;">SKU Code</th>
                    <th style="width: 50%;">Product Name</th>
                    <th style="width: 15%;" class="text-center">Pick Qty</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                    <tr>
                        <td class="text-center">
                            <div style="width: 20px; height: 20px; border: 2px solid #64748b; border-radius: 4px; margin: 0 auto;"></div>
                        </td>
                        <td class="font-monospace fw-bold small text-primary">
                            {{ $item->product?->sku ?? 'SKU-N/A' }}
                        </td>
                        <td>
                            <div class="fw-bold text-dark small">{{ $item->product_name }}</div>
                        </td>
                        <td class="text-center h5 fw-bolder text-dark mb-0">
                            {{ $item->quantity }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Sign-off & Verification -->
        <div class="row pt-3 border-top text-muted small">
            <div class="col-6">
                <div>Picked By: ___________________</div>
                <div class="mt-2">Time: _________________________</div>
            </div>
            <div class="col-6 text-end">
                <div>Packed & Checked By: ___________________</div>
                <div class="mt-2">Quality Seal Verified: [ &nbsp; ] YES</div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
