<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tax Invoice #{{ $order->order_number }} — ShopCalm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap');

        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            background: #f1f5f9;
            color: #0f172a;
            padding: 2.5rem 0;
            -webkit-font-smoothing: antialiased;
        }

        .invoice-sheet {
            max-width: 860px;
            margin: 0 auto;
            background: #ffffff;
            padding: 3.5rem;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.07);
        }

        .font-mono {
            font-family: 'JetBrains Mono', monospace;
        }

        .table-invoice th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: 700;
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 0.75rem 0.75rem;
            border-color: #e2e8f0;
        }

        .table-invoice td {
            padding: 0.85rem 0.75rem;
            border-color: #f1f5f9;
            font-size: 0.84rem;
            vertical-align: middle;
        }

        .invoice-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.25rem 0.65rem;
            border-radius: 9999px;
            font-size: 0.72rem;
            font-weight: 700;
        }

        .invoice-badge-paid {
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .invoice-badge-pending {
            background: #fffbeb;
            color: #b45309;
            border: 1px solid #fde68a;
        }

        .watermark-paid {
            position: absolute;
            top: 45%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-25deg);
            font-size: 5.5rem;
            font-weight: 900;
            color: rgba(16, 185, 129, 0.06);
            letter-spacing: 0.2em;
            pointer-events: none;
            text-transform: uppercase;
            border: 8px dashed rgba(16, 185, 129, 0.08);
            padding: 10px 40px;
            border-radius: 20px;
        }

        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }
            .invoice-sheet {
                box-shadow: none;
                padding: 1.5rem;
                border-radius: 0;
                border: none;
                max-width: 100%;
            }
            .no-print {
                display: none !important;
            }
            .page-break {
                page-break-after: always;
            }
        }
    </style>
</head>
<body>

@php
    $payment = $order->primaryPayment ?? $order->latestPayment;
    $isPaid = in_array(strtolower($order->payment_status ?? ''), ['paid', 'success']);
    
    // State Code Map
    $stateCodes = [
        'telangana' => '36',
        'andhra pradesh' => '37',
        'karnataka' => '29',
        'tamil nadu' => '33',
        'maharashtra' => '27',
        'delhi' => '07',
        'kerala' => '32',
        'gujarat' => '24',
        'rajasthan' => '08',
        'west bengal' => '19',
        'uttar pradesh' => '09',
    ];
    $customerState = strtolower(trim($order->shipping_state ?? ''));
    $customerStateCode = $stateCodes[$customerState] ?? '29';
    $isInterState = ($customerStateCode !== '36'); // ShopCalm registered in Telangana (36)

    // Helper for Amount in Words (Indian Rupees)
    function numberToIndianWords($number) {
        $no = floor($number);
        $point = round($number - $no, 2) * 100;
        $hundred = null;
        $digits_1 = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
        $digits_2 = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
        $digits = ['', 'Hundred', 'Thousand', 'Lakh', 'Crore'];

        $str = [];
        $words = [];
        $i = 0;
        while ($no > 0) {
            $divider = ($i == 2) ? 10 : 100;
            $number = floor($no % $divider);
            $no = floor($no / $divider);
            $i += ($divider == 10) ? 1 : 2;
            if ($number) {
                $plural = (($counter = count($str)) && $number > 9) ? '' : null;
                $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
                $str [] = ($number < 21) ? $digits_1[$number] . ' ' . $digits[$counter] . $plural . ' ' . $hundred
                    : $digits_2[floor($number / 10)] . ' ' . $digits_1[$number % 10] . ' ' . $digits[$counter] . $plural . ' ' . $hundred;
            } else $str[] = null;
        }
        $str = array_reverse($str);
        $result = implode('', $str);
        $points = ($point) ? " and " . ($digits_1[floor($point / 10) * 10] ?? '') . " " . ($digits_1[$point % 10] ?? '') . " Paise" : '';
        return ($result ? trim($result) . " Rupees" : "Zero Rupees") . $points . " Only";
    }

    $amountInWords = numberToIndianWords((float) $order->total_amount);
@endphp

<div class="container">
    <!-- Top Action Toolbar (No-Print) -->
    <div class="d-flex justify-content-between align-items-center mb-3 no-print mx-auto" style="max-width: 860px;">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-dark rounded-pill px-3 py-1.5 fw-semibold small">
                <i class="bi bi-file-earmark-check-fill text-success me-1"></i> Tax Invoice Validated
            </span>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-primary btn-sm rounded-pill px-4 py-2 fw-bold shadow-sm d-flex align-items-center gap-1.5" style="background: #0284c7; border: none;">
                <i class="bi bi-printer-fill"></i> Print / Save as PDF
            </button>
            <button onclick="window.close()" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-2 fw-semibold bg-white shadow-xs">
                Close
            </button>
        </div>
    </div>

    <!-- Main Invoice Sheet -->
    <div class="invoice-sheet position-relative">
        @if($isPaid)
            <div class="watermark-paid">PAID</div>
        @endif

        <!-- Header: Company & Tax Invoice Badge -->
        <div class="d-flex justify-content-between align-items-start border-bottom pb-4 mb-4">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <div class="rounded-3 bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px; font-size: 1.2rem; background: linear-gradient(135deg, #0284c7, #0369a1) !important;">
                        <i class="bi bi-bag-heart-fill"></i>
                    </div>
                    <h3 class="fw-bolder text-dark mb-0" style="letter-spacing: -0.02em;">ShopCalm Retail Pvt. Ltd.</h3>
                </div>
                <div class="text-secondary small mt-1" style="line-height: 1.5; font-size: 0.8rem;">
                    108 Tech Boulevard, Financial District, Gachibowli, Hyderabad - 500032<br>
                    <strong>GSTIN:</strong> 36AABCU9603R1ZM &bull; <strong>CIN:</strong> U52100TG2024PTC184920<br>
                    <strong>State:</strong> Telangana (Code: 36) &bull; <strong>Email:</strong> support@shopcalm.in
                </div>
            </div>
            <div class="text-end">
                <div class="d-inline-block px-3 py-1 bg-light border rounded-pill small fw-bold text-uppercase text-secondary mb-2" style="font-size: 0.72rem; letter-spacing: 0.05em;">
                    Tax Invoice (Original for Recipient)
                </div>
                <h5 class="fw-bolder text-primary mb-1 font-mono">INV-{{ $order->order_number }}</h5>
                <div class="text-secondary small">
                    <strong>Invoice Date:</strong> {{ $order->created_at->format('d M Y') }}<br>
                    <strong>Order Date:</strong> {{ $order->created_at->format('d M Y, h:i A') }}<br>
                    <strong>Place of Supply:</strong> {{ ucfirst($order->shipping_state ?? 'Telangana') }} ({{ $customerStateCode }})
                </div>
            </div>
        </div>

        <!-- 2-Column Info Grid: Billed To & Logistics/Payment Details -->
        <div class="row g-4 mb-4 pb-2">
            <!-- Left: Billed / Shipped To -->
            <div class="col-6">
                <div class="p-3 rounded-3 bg-light border h-100">
                    <div class="small fw-bold text-uppercase text-secondary mb-1.5" style="font-size: 0.68rem; letter-spacing: 0.05em;">
                        <i class="bi bi-geo-alt-fill text-danger me-1"></i> Billed To & Shipped To:
                    </div>
                    <div class="fw-bold text-dark fs-6">{{ $order->shipping_name }}</div>
                    <div class="text-secondary small mt-1" style="line-height: 1.5; font-size: 0.82rem;">
                        {{ $order->shipping_address }}<br>
                        <strong>{{ $order->shipping_city }}, {{ $order->shipping_state }} - {{ $order->shipping_zip }}</strong><br>
                        {{ $order->shipping_country ?? 'India' }}<br>
                        <i class="bi bi-telephone-fill me-1 text-muted small"></i> {{ $order->shipping_phone }} &bull; <i class="bi bi-envelope-fill me-1 text-muted small"></i> {{ $order->shipping_email }}
                    </div>
                </div>
            </div>

            <!-- Right: Payment & Delivery Ledger -->
            <div class="col-6">
                <div class="p-3 rounded-3 bg-light border h-100">
                    <div class="small fw-bold text-uppercase text-secondary mb-1.5 d-flex justify-content-between align-items-center" style="font-size: 0.68rem; letter-spacing: 0.05em;">
                        <span><i class="bi bi-credit-card-2-front-fill text-primary me-1"></i> Payment & Dispatch:</span>
                        @if($isPaid)
                            <span class="invoice-badge invoice-badge-paid"><i class="bi bi-check-circle-fill"></i> PAID</span>
                        @else
                            <span class="invoice-badge invoice-badge-pending"><i class="bi bi-clock-history"></i> PENDING (COD)</span>
                        @endif
                    </div>
                    <div class="text-secondary small" style="line-height: 1.6; font-size: 0.82rem;">
                        <strong>Payment Mode:</strong> 
                        @if($order->payment_method === 'online')
                            ⚡ Cashfree PG ({{ $payment?->method_display ?? 'Online UPI' }})
                        @else
                            💵 Cash on Delivery (COD)
                        @endif
                        <br>
                        @if($payment && $payment->bank_reference)
                            <strong>Bank Ref (UTR / RRN):</strong> <span class="font-mono fw-bold text-dark px-1.5 py-0.5 rounded bg-white border">{{ $payment->bank_reference }}</span><br>
                        @endif
                        @if($payment && $payment->gateway_payment_id)
                            <strong>Gateway Txn ID:</strong> <span class="font-mono text-dark">#{{ $payment->gateway_payment_id }}</span><br>
                        @endif
                        <strong>Dispatch Partner:</strong> {{ $order->courier_partner ?: ($order->rider_name ? 'Bengaluru Local Fleet (' . $order->rider_name . ')' : 'Express Logistics') }}<br>
                        @if($order->tracking_number)
                            <strong>AWB / Tracking #:</strong> <span class="font-mono text-dark">{{ $order->tracking_number }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Line Items Table -->
        <table class="table table-bordered table-invoice align-middle mb-4">
            <thead>
                <tr>
                    <th class="text-center" style="width: 5%;">#</th>
                    <th style="width: 48%;">Item Description & SKU</th>
                    <th class="text-center" style="width: 10%;">HSN/SAC</th>
                    <th class="text-center" style="width: 8%;">Qty</th>
                    <th class="text-end" style="width: 14%;">Unit Price (₹)</th>
                    <th class="text-end" style="width: 15%;">Total Price (₹)</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $productPayable = max(0, (float) $order->subtotal_amount - (float) $order->coupon_discount_amount);
                    $totalTaxable = round($productPayable / 1.18, 2);
                    $totalGst = round($productPayable - $totalTaxable, 2);
                    $cgst = round($totalGst / 2, 2);
                    $sgst = round($totalGst - $cgst, 2);
                    $grandTotal = (float) $order->total_amount;
                @endphp
                @foreach($order->items as $idx => $item)
                    @php
                        $unitPrice = (float) $item->unit_price;
                        $lineTotal = (float) $item->total_price;
                        $itemTaxable = round($lineTotal / 1.18, 2);
                        $itemGst = round($lineTotal - $itemTaxable, 2);
                    @endphp
                    <tr>
                        <td class="text-center font-mono text-secondary">{{ $idx + 1 }}</td>
                        <td>
                            <div class="fw-bold text-dark">{{ $item->product_name }}</div>
                            <div class="text-muted small" style="font-size: 0.72rem;">
                                SKU: <span class="font-mono">{{ $item->product?->sku ?? 'WK-' . str_pad($item->product_id, 5, '0', STR_PAD_LEFT) }}</span>
                                @if($item->offer_discount > 0)
                                    &bull; <span class="text-success fw-semibold">Saved ₹{{ number_format($item->offer_discount * $item->quantity, 2) }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="text-center font-mono text-secondary small">8517</td>
                        <td class="text-center font-bold text-dark font-mono">{{ $item->quantity }}</td>
                        <td class="text-end font-mono">
                            <div>₹{{ number_format($unitPrice, 2) }}</div>
                            <small class="text-muted" style="font-size: 0.65rem;">(Incl. 18% GST)</small>
                        </td>
                        <td class="text-end fw-bold font-mono text-dark">
                            <div>₹{{ number_format($lineTotal, 2) }}</div>
                            <small class="text-muted fw-normal" style="font-size: 0.65rem;">(GST: ₹{{ number_format($itemGst, 2) }})</small>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals & GST Summary Section -->
        <div class="row g-4 mb-4">
            <!-- Left: Amount in Words & Tax Note -->
            <div class="col-7">
                <div class="p-3 rounded-3 bg-light border mb-3">
                    <div class="small fw-bold text-uppercase text-secondary mb-1" style="font-size: 0.68rem; letter-spacing: 0.05em;">
                        Invoice Total in Words:
                    </div>
                    <div class="fw-bold text-dark small" style="line-height: 1.4;">
                        {{ $amountInWords }}
                    </div>
                </div>

                <div class="p-3 rounded-3 border bg-white small text-secondary" style="font-size: 0.72rem; line-height: 1.6;">
                    <div class="fw-bold text-dark mb-1"><i class="bi bi-shield-check text-success me-1"></i>GST Tax Compliance Summary (18% Flat on Products):</div>
                    &bull; <strong>Product Taxable Base (Pre-Tax):</strong> ₹{{ number_format($totalTaxable, 2) }}<br>
                    @if($isInterState)
                        &bull; <strong>IGST @ 18%:</strong> ₹{{ number_format($totalGst, 2) }} (Inter-State Supply)<br>
                    @else
                        &bull; <strong>CGST @ 9%:</strong> ₹{{ number_format($cgst, 2) }} &bull; <strong>SGST @ 9%:</strong> ₹{{ number_format($sgst, 2) }}<br>
                    @endif
                    &bull; <strong>Total Taxes Included:</strong> ₹{{ number_format($totalGst, 2) }}<br>
                    <span class="text-muted fst-italic">&bull; Product prices are inclusive of 18% GST. Delivery & COD fees are flat logistics charges.</span>
                </div>
            </div>

            <!-- Right: Financial Breakdown -->
            <div class="col-5">
                <div class="p-3 rounded-3 bg-light border">
                    <div class="d-flex justify-content-between mb-2 small text-secondary">
                        <span>Items Subtotal:</span>
                        <span class="font-mono fw-semibold text-dark">₹{{ number_format($order->subtotal_amount, 2) }}</span>
                    </div>

                    <div class="d-flex justify-content-between mb-2 small text-secondary">
                        <span>Shipping & Handling:</span>
                        @if($order->shipping_charge > 0)
                            <span class="font-mono text-dark fw-bold">+ ₹{{ number_format($order->shipping_charge, 2) }}</span>
                        @else
                            <span class="badge bg-success bg-opacity-10 text-success fw-bold rounded-pill px-2">FREE</span>
                        @endif
                    </div>

                    @if($order->cod_fee > 0)
                        <div class="d-flex justify-content-between mb-2 small text-secondary">
                            <span>COD Handling Charge:</span>
                            <span class="font-mono text-dark fw-bold">+ ₹{{ number_format($order->cod_fee, 2) }}</span>
                        </div>
                    @endif

                    @if($order->coupon_discount_amount > 0)
                        <div class="d-flex justify-content-between mb-2 small text-success">
                            <span>Coupon Discount ({{ $order->coupon?->code ?? 'PROMO' }}):</span>
                            <span class="font-mono fw-bold">- ₹{{ number_format($order->coupon_discount_amount, 2) }}</span>
                        </div>
                    @endif

                    @if($order->wallet_amount_used > 0)
                        <div class="d-flex justify-content-between mb-2 small text-primary">
                            <span>{{ \App\Models\Setting::get('store_name', 'ShopCalm') }} Wallet Applied:</span>
                            <span class="font-mono fw-bold">- ₹{{ number_format($order->wallet_amount_used, 2) }}</span>
                        </div>
                    @endif

                    <div class="d-flex justify-content-between mb-2 small text-secondary">
                        <span>Total 18% GST (Included):</span>
                        <span class="font-mono text-dark fw-semibold">₹{{ number_format($totalGst, 2) }}</span>
                    </div>

                    <hr class="my-2.5">

                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-dark fs-6">Grand Total:</span>
                        <span class="fs-5 fw-bolder text-primary font-mono">₹{{ number_format($grandTotal, 2) }}</span>
                    </div>
                    <div class="text-muted text-end small" style="font-size: 0.68rem;">(Inclusive of all taxes)</div>
                </div>
            </div>
        </div>

        <!-- Authorized Signature & Footer -->
        <div class="border-top pt-4 mt-2">
            <div class="d-flex justify-content-between align-items-end">
                <div class="text-secondary small" style="font-size: 0.72rem; line-height: 1.5;">
                    <strong>ShopCalm Customer Support:</strong> support@shopcalm.in &bull; +91 99999 99999<br>
                    This is a digitally generated Tax Invoice and does not require a physical signature.
                </div>
                <div class="text-end">
                    <div class="text-secondary small fw-bold text-uppercase mb-2" style="font-size: 0.68rem;">For ShopCalm Retail Pvt. Ltd.</div>
                    <div class="font-mono fw-bold text-primary small d-inline-block px-3 py-1 rounded border bg-light">
                        ✓ Digitally Signed & Authorized
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
