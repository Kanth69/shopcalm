<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderCancellation;
use App\Models\OtpVerification;
use App\Models\Setting;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class WhatsAppService
{
    /**
     * Core method to send Meta WhatsApp Cloud API Template Messages.
     */
    public function sendTemplateMessage(string $mobileNumber, string $templateKey, array $bodyParameters = [], array $buttonParameters = []): bool
    {
        $cleanPhone = preg_replace('/\D/', '', $mobileNumber);
        if (strlen($cleanPhone) === 10) {
            $cleanPhone = '91' . $cleanPhone;
        }

        $apiToken = Setting::get('whatsapp_api_token') 
            ?: (env('WHATSAPP_CLOUD_API_TOKEN') ?: env('WHATSAPP_API_TOKEN'));

        $phoneNumberId = Setting::get('whatsapp_phone_number_id') 
            ?: (env('WHATSAPP_CLOUD_PHONE_NUMBER_ID') ?: env('WHATSAPP_PHONE_NUMBER_ID'));

        $templateName = Setting::get($templateKey) ?: env(strtoupper($templateKey));

        // Default fallbacks if template setting is not customized yet
        if (empty($templateName)) {
            $defaults = [
                'whatsapp_template_name'                    => 'authentication_otp',
                'whatsapp_template_order_confirmed'         => 'order_confirmed',
                'whatsapp_template_out_for_delivery_local'  => 'out_for_delivery_otp',
                'whatsapp_template_courier_dispatched'      => 'courier_dispatched',
                'whatsapp_template_order_delivered'         => 'order_delivered',
                'whatsapp_template_order_cancelled'         => 'order_cancelled',
                'whatsapp_template_refund_processed'        => 'refund_processed',
            ];
            $templateName = $defaults[$templateKey] ?? 'authentication_otp';
        }

        $langCode = Setting::get('whatsapp_template_language') 
            ?: (env('WHATSAPP_TEMPLATE_LANGUAGE') ?: 'en');

        if (empty($apiToken) || empty($phoneNumberId)) {
            Log::info("LOCAL DEV FALLBACK: WhatsApp message [Template: {$templateName}] for {$cleanPhone} (Params: " . json_encode($bodyParameters) . ")");
            return true;
        }

        try {
            $components = [];

            // Body parameters
            if (!empty($bodyParameters)) {
                $bodyParamsFormatted = [];
                foreach ($bodyParameters as $param) {
                    $bodyParamsFormatted[] = ['type' => 'text', 'text' => (string) $param];
                }
                $components[] = [
                    'type'       => 'body',
                    'parameters' => $bodyParamsFormatted,
                ];
            }

            // Button parameters (URL buttons etc.)
            if (!empty($buttonParameters)) {
                foreach ($buttonParameters as $index => $param) {
                    $components[] = [
                        'type'       => 'button',
                        'sub_type'   => 'url',
                        'index'      => (string) $index,
                        'parameters' => [
                            ['type' => 'text', 'text' => (string) $param]
                        ]
                    ];
                }
            }

            $response = Http::timeout(5)->withToken($apiToken)->post("https://graph.facebook.com/v18.0/{$phoneNumberId}/messages", [
                'messaging_product' => 'whatsapp',
                'to'                => $cleanPhone,
                'type'              => 'template',
                'template'          => [
                    'name'       => $templateName,
                    'language'   => ['code' => $langCode],
                    'components' => $components
                ]
            ]);

            if ($response->failed()) {
                Log::error("Meta WhatsApp Cloud API Error for {$cleanPhone} [Template: {$templateName}]: " . $response->body());
                return false;
            }

            Log::info("Meta WhatsApp API Message dispatched to {$cleanPhone} [Template: {$templateName}]. Message ID: " . ($response->json('messages.0.id') ?? 'N/A'));
            return true;
        } catch (\Exception $e) {
            Log::error("Meta WhatsApp API Exception for {$cleanPhone}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate 6-digit OTP and send via Meta WhatsApp Cloud API.
     */
    public function sendOtp(string $mobileNumber, string $purpose = 'REGISTER'): string
    {
        $otp = (string) random_int(100000, 999999);

        OtpVerification::updateOrCreate(
            ['mobile_number' => $mobileNumber, 'purpose' => $purpose],
            [
                'otp_hash' => Hash::make($otp),
                'expires_at' => Carbon::now()->addMinutes(10),
                'verified_at' => null,
            ]
        );

        $this->sendTemplateMessage($mobileNumber, 'whatsapp_template_name', [$otp], [$otp]);

        return $otp;
    }

    /**
     * Verify OTP for a given mobile number and purpose.
     */
    public function verifyOtp(string $mobileNumber, string $otp, string $purpose = 'REGISTER'): bool
    {
        $record = OtpVerification::where('mobile_number', $mobileNumber)
            ->where('purpose', $purpose)
            ->where('expires_at', '>', Carbon::now())
            ->whereNull('verified_at')
            ->first();

        if ($record && Hash::check($otp, $record->otp_hash)) {
            $record->delete();
            return true;
        }

        return false;
    }

    /**
     * 1. Send Order Confirmation Notification.
     */
    public function sendOrderConfirmation(Order $order): bool
    {
        $phone = $order->shipping_phone ?? $order->user?->mobile_number;
        if (empty($phone)) return false;

        $paymentText = ($order->payment_method === 'wallet' || ($order->total_amount <= 0 && $order->wallet_amount_used > 0)) 
            ? '100% Wallet' 
            : strtoupper($order->payment_method ?? 'COD');

        // Parameters: [Customer Name, Order Number, Total Amount, Payment Method]
        return $this->sendTemplateMessage(
            $phone,
            'whatsapp_template_order_confirmed',
            [
                $order->shipping_name ?? 'Customer',
                $order->order_number,
                '₹' . number_format($order->total_amount, 2),
                $paymentText
            ]
        );
    }

    /**
     * 2A. Send Out For Delivery Notification with Doorstep OTP (Bengaluru Local Fleet).
     */
    public function sendOutForDeliveryLocal(Order $order): bool
    {
        $phone = $order->shipping_phone ?? $order->user?->mobile_number;
        if (empty($phone)) return false;

        $otp = $order->fulfillment?->delivery_otp ?? '1234';
        $riderInfo = ($order->rider_name ?? 'Fleet Rider') . ($order->rider_phone ? " ({$order->rider_phone})" : '');

        // Parameters: [Customer Name, Order Number, Delivery OTP, Rider Info]
        return $this->sendTemplateMessage(
            $phone,
            'whatsapp_template_out_for_delivery_local',
            [
                $order->shipping_name ?? 'Customer',
                $order->order_number,
                $otp,
                $riderInfo
            ]
        );
    }

    /**
     * 2B. Send Courier Dispatched Notification with AWB Tracking Link (Pan-India National Courier).
     */
    public function sendDispatchedCourier(Order $order): bool
    {
        $phone = $order->shipping_phone ?? $order->user?->mobile_number;
        if (empty($phone)) return false;

        $courierName = $order->courier_partner ?? 'Courier';
        $awbNumber = $order->tracking_number ?? 'AWB-PENDING';
        $trackingUrl = $order->tracking_url ?? "https://www.google.com/search?q=" . urlencode("{$courierName} tracking {$awbNumber}");

        // Parameters: [Customer Name, Order Number, Courier Name, AWB Number, Tracking URL]
        return $this->sendTemplateMessage(
            $phone,
            'whatsapp_template_courier_dispatched',
            [
                $order->shipping_name ?? 'Customer',
                $order->order_number,
                $courierName,
                $awbNumber,
                $trackingUrl
            ]
        );
    }

    /**
     * 3. Send Order Delivered Notification.
     */
    public function sendOrderDelivered(Order $order): bool
    {
        $phone = $order->shipping_phone ?? $order->user?->mobile_number;
        if (empty($phone)) return false;

        $deliveredBy = $order->isLocalBengaluruDelivery() 
            ? "Rider " . ($order->rider_name ?? 'Fleet Partner')
            : "Courier " . ($order->courier_partner ?? 'Express');

        // Parameters: [Customer Name, Order Number, Delivered By]
        return $this->sendTemplateMessage(
            $phone,
            'whatsapp_template_order_delivered',
            [
                $order->shipping_name ?? 'Customer',
                $order->order_number,
                $deliveredBy
            ]
        );
    }

    /**
     * 4. Send Order Cancelled & Refund Initiated Notification.
     */
    public function sendOrderCancelled(OrderCancellation $cancellation): bool
    {
        $order = $cancellation->order;
        if (!$order) return false;

        $phone = $order->shipping_phone ?? $order->user?->mobile_number;
        if (empty($phone)) return false;

        $ticketNo = 'CNL-' . $order->order_number;
        $reason = $cancellation->cancellation_reason ?? 'Cancelled';
        
        $walletRefund = (float) ($cancellation->wallet_refund_amount ?? 0);
        $onlineRefund = (float) ($cancellation->online_refund_amount ?? 0);
        $totalRefund = (float) ($cancellation->refund_amount ?? ($walletRefund + $onlineRefund));

        if ($walletRefund > 0 && $onlineRefund > 0) {
            $destText = "₹" . number_format($walletRefund, 2) . " credited to Wallet & ₹" . number_format($onlineRefund, 2) . " to Razorpay PG";
        } elseif ($walletRefund > 0) {
            $destText = "₹" . number_format($walletRefund, 2) . " credited instantly to your ShopCalm Wallet";
        } elseif ($onlineRefund > 0 || in_array($cancellation->refund_method, ['original_source', 'online'])) {
            $destText = "₹" . number_format($onlineRefund > 0 ? $onlineRefund : $totalRefund, 2) . " initiated via Razorpay PG (3-5 business days)";
        } else {
            $destText = "COD Order (₹0 cash collected)";
        }

        // Parameters: [Customer Name, Order Number, Ticket No, Cancellation Reason, Refund Destination Info]
        return $this->sendTemplateMessage(
            $phone,
            'whatsapp_template_order_cancelled',
            [
                $order->shipping_name ?? 'Customer',
                $order->order_number,
                $ticketNo,
                $reason,
                $destText
            ]
        );
    }

    /**
     * 5. Send Refund Processed Notification (UTR Reference).
     */
    public function sendRefundProcessed(OrderCancellation $cancellation): bool
    {
        $order = $cancellation->order;
        if (!$order) return false;

        $phone = $order->shipping_phone ?? $order->user?->mobile_number;
        if (empty($phone)) return false;

        $ticketNo = 'CNL-' . $order->order_number;
        $refundProof = $cancellation->razorpay_refund_id ?: ($cancellation->payment_reference ?: 'PROCESSED');
        $refundAmount = number_format($cancellation->refund_amount > 0 ? $cancellation->refund_amount : ($cancellation->online_refund_amount + $cancellation->wallet_refund_amount), 2);

        // Parameters: [Customer Name, Order Number, Ticket No, Refund Amount, Bank UTR Reference]
        return $this->sendTemplateMessage(
            $phone,
            'whatsapp_template_refund_processed',
            [
                $order->shipping_name ?? 'Customer',
                $order->order_number,
                $ticketNo,
                '₹' . $refundAmount,
                $refundProof
            ]
        );
    }
}
