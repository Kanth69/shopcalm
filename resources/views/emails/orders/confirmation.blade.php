<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation #{{ $order->order_number }}</title>
</head>
<body style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f1f5f9; color: #1e293b; margin: 0; padding: 20px 0;">

<table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #f1f5f9; padding: 20px 0;">
    <tr>
        <td align="center">
            <!-- Main Email Container Table -->
            <table width="600" border="0" cellspacing="0" cellpadding="0" style="background-color: #ffffff; border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                
                <!-- Header Banner -->
                <tr>
                    <td style="background-color: #0f172a; padding: 32px 24px; text-align: center;">
                        <h1 style="color: #ffffff; margin: 0 0 6px 0; font-size: 24px; font-weight: 800; letter-spacing: -0.5px;">Order Confirmed!</h1>
                        <p style="color: #94a3b8; margin: 0 0 12px 0; font-size: 14px;">Thank you for shopping at {{ $storeName }}</p>
                        <span style="background-color: #1e293b; color: #38bdf8; border: 1px solid #38bdf8; padding: 4px 14px; border-radius: 20px; font-size: 12px; font-weight: 700; text-transform: uppercase;">
                            Order #{{ $order->order_number }}
                        </span>
                    </td>
                </tr>

                <!-- Main Content Area -->
                <tr>
                    <td style="padding: 24px;">
                        <p style="font-size: 15px; color: #334155; margin-top: 0; margin-bottom: 16px;">
                            Hi <strong>{{ $order->shipping_name }}</strong>,
                        </p>
                        <p style="font-size: 14px; color: #475569; line-height: 1.6; margin-bottom: 20px;">
                            We’re excited to let you know your order has been received and is being prepared for dispatch. Below are your tax invoice and order summary details:
                        </p>

                        <!-- Order & Delivery Info Box -->
                        <table width="100%" border="0" cellspacing="0" cellpadding="10" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 24px;">
                            <tr>
                                <td width="35%" style="color: #64748b; font-size: 13px; font-weight: 600; border-bottom: 1px solid #e2e8f0;">Order Reference:</td>
                                <td width="65%" style="color: #0f172a; font-size: 13px; font-weight: 700; border-bottom: 1px solid #e2e8f0;">#{{ $order->order_number }}</td>
                            </tr>
                            <tr>
                                <td style="color: #64748b; font-size: 13px; font-weight: 600; border-bottom: 1px solid #e2e8f0;">Date & Time:</td>
                                <td style="color: #0f172a; font-size: 13px; font-weight: 700; border-bottom: 1px solid #e2e8f0;">{{ $order->created_at ? $order->created_at->format('M d, Y - h:i A') : date('M d, Y') }}</td>
                            </tr>
                            <tr>
                                <td style="color: #64748b; font-size: 13px; font-weight: 600; border-bottom: 1px solid #e2e8f0;">Payment Method:</td>
                                <td style="color: #0f172a; font-size: 13px; font-weight: 700; border-bottom: 1px solid #e2e8f0;">{{ strtoupper($order->payment_method) }} ({{ ucfirst($order->payment_status) }})</td>
                            </tr>
                            <tr>
                                <td style="color: #64748b; font-size: 13px; font-weight: 600;">Delivery Address:</td>
                                <td style="color: #0f172a; font-size: 13px; font-weight: 700;">{{ $order->shipping_address }}, {{ $order->shipping_city }}, {{ $order->shipping_state }} - {{ $order->shipping_zip }}</td>
                            </tr>
                        </table>

                        <!-- Tax Invoice View/Print Action Button -->
                        <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-bottom: 24px;">
                            <tr>
                                <td align="center">
                                    <a href="{{ route('orders.public-invoice', $order) }}" target="_blank" style="background-color: #0f172a; color: #ffffff; text-decoration: none; padding: 12px 24px; border-radius: 8px; font-size: 13px; font-weight: 700; display: inline-block; border: 1px solid #1e293b;">
                                        📑 View & Print Official GST Tax Invoice (PDF)
                                    </a>
                                </td>
                            </tr>
                        </table>

                        <!-- Items Table -->
                        <h3 style="font-size: 15px; font-weight: 700; color: #0f172a; margin: 0 0 12px 0;">Items Ordered</h3>
                        <table width="100%" border="0" cellspacing="0" cellpadding="10" style="border-collapse: collapse; margin-bottom: 20px; border: 1px solid #e2e8f0;">
                            <thead>
                                <tr style="background-color: #f1f5f9;">
                                    <th align="left" style="font-size: 12px; text-transform: uppercase; color: #475569; border-bottom: 2px solid #cbd5e1;">Product</th>
                                    <th align="center" style="font-size: 12px; text-transform: uppercase; color: #475569; border-bottom: 2px solid #cbd5e1;">Qty</th>
                                    <th align="right" style="font-size: 12px; text-transform: uppercase; color: #475569; border-bottom: 2px solid #cbd5e1;">Unit Price</th>
                                    <th align="right" style="font-size: 12px; text-transform: uppercase; color: #475569; border-bottom: 2px solid #cbd5e1;">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                <tr>
                                    <td align="left" style="font-size: 13px; color: #334155; border-bottom: 1px solid #e2e8f0;">
                                        <strong>{{ $item->product_name ?? ($item->product->name ?? 'Product Item') }}</strong>
                                        @if($item->selected_option)
                                            <br><small style="color: #64748b;">Option: {{ $item->selected_option }}</small>
                                        @endif
                                    </td>
                                    <td align="center" style="font-size: 13px; color: #334155; border-bottom: 1px solid #e2e8f0;">{{ $item->quantity }}</td>
                                    <td align="right" style="font-size: 13px; color: #334155; border-bottom: 1px solid #e2e8f0;">₹{{ number_format((float)($item->unit_price ?? $item->original_price ?? 0), 2) }}</td>
                                    <td align="right" style="font-size: 13px; font-weight: 700; color: #0f172a; border-bottom: 1px solid #e2e8f0;">₹{{ number_format((float)($item->total_price ?? (($item->unit_price ?? 0) * $item->quantity)), 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <!-- Pricing Breakdown Table -->
                        <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-bottom: 24px;">
                            <tr>
                                <td width="40%">&nbsp;</td>
                                <td width="60%">
                                    <table width="100%" border="0" cellspacing="0" cellpadding="6" style="border-collapse: collapse; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
                                        <tr>
                                            <td align="left" style="font-size: 13px; color: #64748b;">Subtotal:</td>
                                            <td align="right" style="font-size: 13px; color: #0f172a; font-weight: 600;">₹{{ number_format((float)$order->subtotal_amount, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td align="left" style="font-size: 13px; color: #64748b;">Delivery Charge:</td>
                                            <td align="right" style="font-size: 13px; color: #0f172a; font-weight: 600;">
                                                @if((float)$order->shipping_charge > 0)
                                                    ₹{{ number_format((float)$order->shipping_charge, 2) }}
                                                @else
                                                    <span style="color: #10b981; font-weight: 700;">FREE</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @if((float)$order->cod_fee > 0)
                                        <tr>
                                            <td align="left" style="font-size: 13px; color: #64748b;">COD Handling Fee:</td>
                                            <td align="right" style="font-size: 13px; color: #0f172a; font-weight: 600;">₹{{ number_format((float)$order->cod_fee, 2) }}</td>
                                        </tr>
                                        @endif
                                        @if((float)$order->coupon_discount_amount > 0)
                                        <tr>
                                            <td align="left" style="font-size: 13px; color: #10b981;">Coupon Discount:</td>
                                            <td align="right" style="font-size: 13px; color: #10b981; font-weight: 700;">-₹{{ number_format((float)$order->coupon_discount_amount, 2) }}</td>
                                        </tr>
                                        @endif
                                        @if((float)$order->wallet_amount_used > 0)
                                        <tr>
                                            <td align="left" style="font-size: 13px; color: #6366f1;">Wallet Credit Paid:</td>
                                            <td align="right" style="font-size: 13px; color: #6366f1; font-weight: 700;">-₹{{ number_format((float)$order->wallet_amount_used, 2) }}</td>
                                        </tr>
                                        @endif
                                        <tr>
                                            <td align="left" style="font-size: 15px; font-weight: 800; color: #0f172a; border-top: 2px solid #cbd5e1; padding-top: 8px;">Total Amount:</td>
                                            <td align="right" style="font-size: 15px; font-weight: 800; color: #0f172a; border-top: 2px solid #cbd5e1; padding-top: 8px;">₹{{ number_format((float)$order->total_amount, 2) }}</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                        <!-- Policy Notice Box -->
                        <table width="100%" border="0" cellspacing="0" cellpadding="12" style="background-color: #fff5f5; border: 1px solid #fed7d7; border-radius: 8px;">
                            <tr>
                                <td style="font-size: 13px; color: #9b2c2c; line-height: 1.5;">
                                    <strong>Store Policy Notice:</strong> All sales are final once delivered under {{ $storeName }}'s 
                                    <a href="{{ url('/return-refund-policy') }}" target="_blank" style="color: #9b2c2c; font-weight: 700; text-decoration: underline;">No Return & No Replacement Policy</a>. 
                                    Free cancellation is available prior to package dispatch.
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- Footer Banner -->
                <tr>
                    <td style="background-color: #f1f5f9; padding: 20px; text-align: center; border-top: 1px solid #e2e8f0;">
                        <p style="font-size: 12px; color: #64748b; margin: 0 0 6px 0;">© {{ date('Y') }} {{ $storeName }} E-Commerce. All rights reserved.</p>
                        <p style="font-size: 12px; color: #64748b; margin: 0;">Grievance Officer: <a href="mailto:grievance@shopcalm.in" style="color: #4f46e5; text-decoration: none; font-weight: 600;">grievance@shopcalm.in</a></p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

</body>
</html>
