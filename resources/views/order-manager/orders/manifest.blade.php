<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Delivery Run-Sheet #{{ $order->order_number }} — Bengaluru Local Fleet</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            font-family: system-ui, -apple-system, sans-serif;
            background: #f1f5f9;
            color: #1e293b;
            padding: 2rem 0;
        }
        .manifest-sheet {
            max-width: 800px;
            margin: 0 auto;
            background: #ffffff;
            padding: 2.5rem;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            border: 2px solid #0284c7;
        }
        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }
            .manifest-sheet {
                box-shadow: none;
                padding: 1.5rem;
                border: 1.5px solid #000;
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
        <button onclick="window.print()" class="btn btn-info text-white btn-sm rounded-pill px-4 fw-bold shadow-sm" style="background: #0284c7;">
            <i class="bi bi-printer-fill me-1"></i> Print Delivery Run-Sheet
        </button>
        <button onclick="window.close()" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            Close
        </button>
    </div>

    <div class="manifest-sheet">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
            <div>
                <span class="badge bg-info text-white px-3 py-1.5 rounded-pill fw-bold mb-2" style="background: #0284c7 !important;">
                    <i class="bi bi-geo-fill me-1"></i> BENGALURU LOCAL FLEET RUN-SHEET
                </span>
                <h4 class="fw-bolder text-dark mb-0">ShopCalm Express Dispatch</h4>
                <div class="text-muted small">Hub: Bengaluru South Fulfillment Node</div>
            </div>
            <div class="text-end">
                <div class="fw-bolder font-monospace fs-5 text-primary">#{{ $order->order_number }}</div>
                <div class="text-muted small">Date: {{ now()->format('d M, Y h:i A') }}</div>
            </div>
        </div>

        <!-- Rider & Slot Details -->
        <div class="row g-3 mb-4">
            <div class="col-6">
                <div class="p-3 bg-light rounded-3 border">
                    <div class="text-muted small fw-bold text-uppercase mb-1"><i class="bi bi-person-badge me-1"></i> Assigned Delivery Executive</div>
                    <div class="fw-bold fs-6 text-dark">{{ $order->fulfillment?->rider_name ?? 'Unassigned Rider' }}</div>
                    <div class="text-primary fw-bold small"><i class="bi bi-telephone-fill me-1"></i> {{ $order->fulfillment?->rider_phone ?? 'N/A' }}</div>
                </div>
            </div>
            <div class="col-6">
                <div class="p-3 bg-light rounded-3 border">
                    <div class="text-muted small fw-bold text-uppercase mb-1"><i class="bi bi-clock-history me-1"></i> Delivery Window / Slot</div>
                    <div class="fw-bold fs-6 text-dark">{{ $order->fulfillment?->delivery_slot ?? 'Local Fleet Express (1-2 Days)' }}</div>
                    <div class="text-muted small">Dispatched: {{ $order->fulfillment?->dispatched_at ? $order->fulfillment->dispatched_at->format('d M, h:i A') : now()->format('d M, h:i A') }}</div>
                </div>
            </div>
        </div>

        <!-- Customer Delivery Info -->
        <div class="p-3 rounded-3 border mb-4" style="background: #f8fafc;">
            <div class="row">
                <div class="col-7">
                    <div class="text-muted small fw-bold text-uppercase mb-1"><i class="bi bi-geo-alt-fill text-danger me-1"></i> Delivery Destination</div>
                    <h6 class="fw-bold mb-1">{{ $order->shipping_name }}</h6>
                    <div class="small text-dark">{{ $order->shipping_address }}</div>
                    <div class="small fw-semibold text-secondary">{{ $order->shipping_city }}, {{ $order->shipping_state }} - <span class="font-monospace fw-bold text-dark">{{ $order->shipping_zip }}</span></div>
                    <div class="small text-dark mt-2"><i class="bi bi-phone-fill text-muted me-1"></i> Phone: <b>{{ $order->shipping_phone }}</b></div>
                </div>
                <div class="col-5 border-start ps-4 d-flex flex-column justify-content-center">
                    <div class="text-muted small fw-bold text-uppercase mb-1">Payment & Collection</div>
                    @if(strtoupper($order->payment_method) === 'COD' && $order->payment_status !== 'paid' && $order->total_amount > 0)
                        <div class="p-2.5 rounded-3 bg-warning bg-opacity-15 border border-warning text-center">
                            <span class="badge bg-warning text-dark fw-bold mb-1">CASH ON DELIVERY</span>
                            <div class="h5 fw-bolder text-dark mb-0">Collect: ₹{{ number_format($order->total_amount, 2) }}</div>
                        </div>
                    @else
                        <div class="p-2.5 rounded-3 bg-success bg-opacity-15 border border-success text-center">
                            <span class="badge bg-success fw-bold mb-1">PREPAID ORDER</span>
                            <div class="h6 fw-bolder text-success mb-0">Do Not Collect Cash (Paid)</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Items Checklist -->
        <h6 class="fw-bold text-uppercase small text-muted mb-2"><i class="bi bi-boxes me-1"></i> Package Items Checklist ({{ $order->items->sum('quantity') }} Total Units)</h6>
        <div class="table-responsive mb-4">
            <table class="table table-bordered table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 40px;" class="text-center">Check</th>
                        <th>Product Item</th>
                        <th>SKU</th>
                        <th class="text-center" style="width: 70px;">Qty</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                        <tr>
                            <td class="text-center"><input type="checkbox" style="width: 16px; height: 16px;"></td>
                            <td>
                                <div class="fw-bold small">{{ $item->product?->name ?? 'Product Item' }}</div>
                            </td>
                            <td class="font-monospace small text-muted">{{ $item->product?->sku ?? '—' }}</td>
                            <td class="text-center fw-bold">{{ $item->quantity }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Rider & Customer Signatures -->
        <div class="row g-4 pt-3 border-top mt-2">
            <div class="col-6">
                <div class="border rounded-3 p-3 text-center" style="height: 110px;">
                    <div class="text-muted small text-uppercase fw-semibold mb-4">Delivery Rider Signature</div>
                    <div class="border-bottom mx-auto" style="width: 80%;"></div>
                </div>
            </div>
            <div class="col-6">
                <div class="border rounded-3 p-3 text-center" style="height: 110px;">
                    <div class="text-muted small text-uppercase fw-semibold mb-4">Customer Handover Signature</div>
                    <div class="border-bottom mx-auto" style="width: 80%;"></div>
                </div>
            </div>
        </div>

        <div class="text-center text-muted small mt-4 pt-2 border-top" style="font-size: 0.75rem;">
            ShopCalm Bengaluru Logistics Node • Support Hotline: +91 80 1234 5678 • support@shopcalm.in
        </div>
    </div>
</div>

</body>
</html>
