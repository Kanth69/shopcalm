<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk Shipping Labels ({{ $orders->count() }} Orders) - ShopCalm Logistics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            background-color: #f1f5f9;
            color: #000000;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
        }

        /* Screen Control Toolbar */
        .toolbar {
            width: 100mm;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            position: sticky;
            top: 10px;
            z-index: 100;
            background: #ffffff;
            padding: 10px 14px;
            border-radius: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.12);
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 16px;
            font-size: 13px;
            font-weight: 700;
            border-radius: 20px;
            cursor: pointer;
            border: 1px solid #cbd5e1;
            text-decoration: none;
        }
        .btn-print { background: #0284c7; color: #ffffff; border-color: #0284c7; }
        .btn-close { background: #ffffff; color: #475569; }

        /* Standard 4" x 6" (100mm x 150mm) Label Container */
        .thermal-label {
            width: 100mm;
            height: 148mm;
            background: #ffffff;
            border: 2px solid #000000;
            padding: 4mm 5mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
            margin-bottom: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            page-break-after: always;
            page-break-inside: avoid;
        }

        .header-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #000000;
            padding-bottom: 2.5mm;
            margin-bottom: 2mm;
        }
        .brand-title {
            font-size: 14pt;
            font-weight: 900;
            letter-spacing: -0.5px;
            text-transform: uppercase;
        }
        .hub-badge {
            border: 1.5px solid #000000;
            padding: 1px 6px;
            font-size: 8pt;
            font-weight: 800;
            text-transform: uppercase;
        }

        .barcode-section {
            text-align: center;
            border-bottom: 2px solid #000000;
            padding-bottom: 2mm;
            margin-bottom: 2mm;
        }
        .barcode-svg {
            max-width: 100%;
            height: 40px;
        }
        .barcode-text {
            font-family: monospace;
            font-size: 9pt;
            font-weight: 800;
            letter-spacing: 1px;
            margin-top: 1px;
        }

        .payment-banner {
            border: 2px solid #000000;
            padding: 2.5mm;
            text-align: center;
            margin-bottom: 2.5mm;
        }
        .payment-cod {
            background: #000000;
            color: #ffffff;
        }
        .payment-prepaid {
            background: #ffffff;
            color: #000000;
        }
        .payment-title {
            font-size: 12pt;
            font-weight: 900;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .payment-sub {
            font-size: 8pt;
            font-weight: 700;
        }

        .routing-row {
            display: flex;
            border-bottom: 1.5px solid #000000;
            padding-bottom: 2mm;
            margin-bottom: 2mm;
            font-size: 7.5pt;
        }
        .routing-col { flex: 1; }
        .routing-col:last-child {
            text-align: right;
            border-left: 1.5px solid #000000;
            padding-left: 3mm;
        }

        .ship-to-box {
            border: 2px solid #000000;
            padding: 3.5mm;
            margin-bottom: 2.5mm;
            background: #ffffff;
        }
        .section-label {
            font-size: 7pt;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 1.5mm;
            border-bottom: 1px solid #000000;
            padding-bottom: 1mm;
        }
        .customer-name {
            font-size: 12pt;
            font-weight: 900;
            line-height: 1.2;
            margin-bottom: 1.5mm;
        }
        .customer-address {
            font-size: 9pt;
            line-height: 1.35;
            font-weight: 600;
        }
        .customer-city-zip {
            font-size: 10.5pt;
            font-weight: 900;
            margin-top: 1.5mm;
            text-transform: uppercase;
        }
        .customer-phone {
            font-size: 11pt;
            font-weight: 900;
            margin-top: 2mm;
            background: #000000;
            color: #ffffff;
            padding: 1.5mm 3mm;
            display: inline-block;
            border-radius: 2px;
        }

        .items-section {
            border-bottom: 1.5px solid #000000;
            padding-bottom: 2mm;
            margin-bottom: 2mm;
            flex-grow: 1;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5pt;
        }
        .items-table th {
            text-align: left;
            border-bottom: 1px solid #000000;
            font-weight: 800;
            padding-bottom: 1mm;
            text-transform: uppercase;
        }
        .items-table td {
            padding-top: 1mm;
            vertical-align: top;
        }
        .text-right { text-align: right; }

        .footer-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 7pt;
            padding-top: 1.5mm;
            border-top: 1.5px dashed #000000;
        }
        .return-info {
            width: 100%;
            line-height: 1.3;
        }

        @media print {
            body {
                background: none;
                padding: 0;
                margin: 0;
            }
            .toolbar { display: none !important; }
            .thermal-label {
                border: 1.5px solid #000000;
                box-shadow: none;
                width: 100mm;
                height: 148mm;
                margin: 0;
                page-break-after: always;
                page-break-inside: avoid;
            }
            @page {
                size: 100mm 150mm;
                margin: 0mm;
            }
        }
    </style>
</head>
<body>

    <!-- Master Screen Toolbar -->
    <div class="toolbar">
        <button onclick="window.print()" class="btn btn-print">
            <i class="bi bi-printer-fill"></i> Print All {{ $orders->count() }} Labels (4x6)
        </button>
        <button onclick="window.close()" class="btn btn-close">
            Close
        </button>
    </div>

    <!-- Loop Through Selected Orders -->
    @foreach($orders as $order)
        <div class="thermal-label">
            <div>
                <!-- 1. Header Row -->
                <div class="header-row">
                    <div>
                        <div class="brand-title">ShopCalm</div>
                        <div style="font-size: 6.5pt; font-weight: 700; text-transform: uppercase;">Direct Fulfillment Logistics</div>
                    </div>
                    <div class="text-right">
                        @if($order->fulfillment?->type === 'local_fleet' || str_contains(strtolower($order->shipping_city ?? ''), 'bengaluru') || str_contains(strtolower($order->shipping_city ?? ''), 'bangalore'))
                            <div class="hub-badge">BLR FLEET HUB #01</div>
                            <div style="font-size: 6pt; font-weight: 800; margin-top: 1px;">HYPERLOCAL EXPRESS</div>
                        @else
                            <div class="hub-badge">{{ strtoupper($order->courier_partner ?? 'NATIONAL COURIER') }}</div>
                            <div style="font-size: 6pt; font-weight: 800; margin-top: 1px;">PAN-INDIA AIR</div>
                        @endif
                    </div>
                </div>

                <!-- 2. Barcode Section -->
                <div class="barcode-section">
                    <svg id="barcode-{{ $order->id }}" class="barcode-svg"></svg>
                    <div class="barcode-text">REF: {{ $order->order_number }}</div>
                </div>

                <!-- 3. Payment Mode Badge -->
                @if(strtolower($order->payment_method) === 'cod')
                    <div class="payment-banner payment-cod">
                        <div class="payment-title">💵 COD: COLLECT ₹{{ number_format($order->total_amount, 2) }}</div>
                        <div class="payment-sub">CASH ON DELIVERY &bull; VERIFY EXACT AMOUNT</div>
                    </div>
                @else
                    <div class="payment-banner payment-prepaid">
                        <div class="payment-title">✅ PREPAID ORDER: ₹0.00</div>
                        <div class="payment-sub">PAID ONLINE VIA {{ strtoupper($order->payment_method) }} &bull; DO NOT COLLECT CASH</div>
                    </div>
                @endif

                <!-- 4. Routing & Courier Details -->
                <div class="routing-row">
                    <div class="routing-col">
                        <strong>Order Date:</strong> {{ $order->created_at->format('d M Y, h:i A') }}<br>
                        <strong>Zone:</strong> {{ str_contains(strtolower($order->shipping_city ?? ''), 'bengaluru') ? 'Bengaluru Metro' : 'Outstation Hub' }}
                    </div>
                    <div class="routing-col">
                        <strong>AWB / Tracking:</strong> {{ $order->tracking_number ?? $order->order_number }}<br>
                        <strong>Assigned Fleet:</strong> {{ $order->rider?->name ?? 'Hub Counter Dispatch' }}
                    </div>
                </div>

                <!-- 5. Destination Customer Address -->
                <div class="ship-to-box">
                    <div class="section-label">📍 DELIVER TO CUSTOMER:</div>
                    <div class="customer-name">{{ $order->shipping_name }}</div>
                    <div class="customer-address">
                        {{ $order->shipping_address }}
                    </div>
                    <div class="customer-city-zip">
                        {{ $order->shipping_city }}, {{ $order->shipping_state }} - <strong>{{ $order->shipping_zip }}</strong>
                    </div>
                    <div class="customer-phone">
                        📱 PHONE: {{ $order->shipping_phone }}
                    </div>
                </div>

                <!-- 6. Package Item Checklist -->
                <div class="items-section">
                    <table class="items-table">
                        <thead>
                            <tr>
                                <th style="width: 25%;">SKU</th>
                                <th style="width: 60%;">Product Name</th>
                                <th style="width: 15%;" class="text-right">Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                                <tr>
                                    <td style="font-family: monospace; font-weight: 700;">{{ $item->product?->sku ?? 'SKU-'.$item->id }}</td>
                                    <td>{{ \Illuminate\Support\Str::limit($item->product_name, 35) }}</td>
                                    <td class="text-right" style="font-weight: 800;">x{{ $item->quantity }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 7. Return Info Footer -->
            <div class="footer-row">
                <div class="return-info">
                    <strong>Return If Undelivered To:</strong>
                    ShopCalm Central Fulfillment Hub #01, Outer Ring Road, Bellandur, Bengaluru - 560103
                </div>
            </div>
        </div>
    @endforeach

    <!-- Barcode Generation Script For All Stickers -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @foreach($orders as $order)
                JsBarcode("#barcode-{{ $order->id }}", "{{ $order->order_number }}", {
                    format: "CODE128",
                    lineColor: "#000000",
                    width: 2,
                    height: 40,
                    displayValue: false,
                    margin: 0
                });
            @endforeach
        });
    </script>
</body>
</html>
