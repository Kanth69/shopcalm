<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settlement Receipt - {{ $settlement->settlement_number }} - ShopCalm Logistics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8fafc;
            color: #0f172a;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            padding: 2rem 0;
        }
        .receipt-card {
            max-width: 680px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.06);
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }
        @media print {
            body { background: #ffffff; padding: 0; }
            .receipt-card { box-shadow: none; border: 1px solid #000; max-width: 100%; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="no-print text-center mb-3">
        <a href="{{ route('order-manager.settlements.index') }}" class="btn btn-sm btn-light border me-2">
            <i class="bi bi-arrow-left me-1"></i> Back to Settlements
        </a>
        <button onclick="window.print()" class="btn btn-sm btn-dark px-3">
            <i class="bi bi-printer me-1"></i> Print / Save PDF
        </button>
    </div>

    <div class="receipt-card p-4 p-md-5">
        <!-- Header -->
        <div class="d-flex align-items-center justify-content-between border-bottom pb-4 mb-4">
            <div class="d-flex align-items-center gap-2.5">
                <div class="rounded-3 d-flex align-items-center justify-content-center text-white fw-bolder" style="width: 44px; height: 44px; background: #0284c7; font-size: 1.25rem;">
                    <i class="bi bi-box-seam-fill"></i>
                </div>
                <div>
                    <h5 class="fw-bolder mb-0 text-dark" style="letter-spacing: -0.3px;">Shop<span style="color: #0284c7;">Calm</span> Logistics</h5>
                    <span class="text-secondary small">Central Fulfillment Hub #01 &bull; Bengaluru</span>
                </div>
            </div>
            <div class="text-end">
                <span class="badge bg-success bg-opacity-10 text-success border border-success rounded-pill px-3 py-1 fw-bold">
                    ✓ CASH SETTLED
                </span>
                <div class="fw-bold font-monospace text-dark mt-1" style="font-size: 0.85rem;">{{ $settlement->settlement_number }}</div>
            </div>
        </div>

        <!-- Settlement Summary -->
        <div class="row g-3 mb-4 p-3 bg-light rounded-4 border">
            <div class="col-sm-6">
                <div class="text-secondary small fw-bold text-uppercase" style="font-size: 0.68rem;">Rider (Depositor)</div>
                <div class="fw-bolder text-dark" style="font-size: 0.95rem;">🛵 {{ $settlement->rider?->name ?? 'Delivery Partner' }}</div>
                <div class="text-secondary small font-monospace">{{ $settlement->rider?->mobile_number }}</div>
            </div>
            <div class="col-sm-6 text-sm-end">
                <div class="text-secondary small fw-bold text-uppercase" style="font-size: 0.68rem;">Received By (Warehouse Staff)</div>
                <div class="fw-bold text-dark">{{ $settlement->receivedBy?->name ?? 'Order Manager' }}</div>
                <div class="text-secondary small font-monospace">{{ $settlement->created_at->format('d M Y, h:i A') }}</div>
            </div>
        </div>

        <!-- Total Handover Highlight -->
        <div class="p-3.5 rounded-4 bg-success bg-opacity-10 border border-success border-opacity-30 mb-4 text-center">
            <div class="text-secondary small fw-bold text-uppercase" style="font-size: 0.72rem;">Total Cash Deposited & Reconciled</div>
            <div class="h1 fw-bolder text-success mb-0 font-monospace mt-0.5" style="font-size: 2.2rem;">
                ₹{{ number_format($settlement->total_amount, 2) }}
            </div>
            <div class="text-secondary small mt-1">
                Payment Mode: <strong class="text-uppercase text-dark">{{ $settlement->payment_mode }}</strong> &bull; {{ $settlement->order_count }} Order(s) Reconciled
            </div>
        </div>

        <!-- Itemized Order Table -->
        <div class="mb-4">
            <h6 class="fw-bold text-dark small text-uppercase mb-2">Reconciled COD Orders Breakdown</h6>
            <div class="table-responsive rounded-3 border">
                <table class="table table-sm align-middle mb-0" style="font-size: 0.8rem;">
                    <thead class="bg-light text-secondary text-uppercase fw-bold" style="font-size: 0.68rem;">
                        <tr>
                            <th class="ps-3">Order Ref</th>
                            <th>Customer Name</th>
                            <th>Delivered Timestamp</th>
                            <th class="text-end pe-3">COD Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach($settlement->fulfillments as $fulf)
                            <tr>
                                <td class="ps-3 fw-bold font-monospace text-primary">#{{ $fulf->order?->order_number ?? 'N/A' }}</td>
                                <td>{{ $fulf->order?->shipping_name ?? 'Customer' }}</td>
                                <td>{{ $fulf->delivered_at?->format('d M, h:i A') ?? 'Delivered' }}</td>
                                <td class="text-end pe-3 fw-bold font-monospace">₹{{ number_format($fulf->order?->total_amount ?? 0, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-light fw-bold">
                        <tr>
                            <td colspan="3" class="ps-3">Total Reconciled Cash:</td>
                            <td class="text-end pe-3 font-monospace text-success">₹{{ number_format($settlement->total_amount, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        @if($settlement->notes)
            <div class="p-3 bg-light rounded-3 border mb-4">
                <div class="text-secondary small fw-bold text-uppercase" style="font-size: 0.65rem;">Staff Notes</div>
                <div class="text-dark small mt-0.5">{{ $settlement->notes }}</div>
            </div>
        @endif

        <!-- Signatures & Disclaimer Footer -->
        <div class="row pt-4 border-top text-center" style="font-size: 0.78rem;">
            <div class="col-6">
                <div style="height: 35px;"></div>
                <div class="border-top pt-1 text-secondary fw-semibold">Rider Signature</div>
            </div>
            <div class="col-6">
                <div style="height: 35px;"></div>
                <div class="border-top pt-1 text-secondary fw-semibold">Warehouse Counter Staff Signature</div>
            </div>
        </div>

        <div class="text-center text-muted small mt-4 pt-2 border-top" style="font-size: 0.68rem;">
            ShopCalm Logistics Central Hub &bull; Computer Generated Reconciliation Voucher &bull; {{ now()->format('Y-m-d H:i:s') }}
        </div>
    </div>
</div>

</body>
</html>
