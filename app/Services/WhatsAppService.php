<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OtpVerification;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * Send Meta WhatsApp Cloud API Template Message (Authentication OTP or Order Confirmation / Tracking).
     */
    public function sendTemplateMessage(
        string $mobileNumber, 
        string $templateKey = 'whatsapp_template_name', 
        array $bodyParameters = [], 
        array $buttonParameters = [],
        string $channel = 'auth'
    ): bool {
        $cleanPhone = preg_replace('/\D/', '', $mobileNumber);
        if (strlen($cleanPhone) === 11 && str_starts_with($cleanPhone, '0')) {
            $cleanPhone = '91' . substr($cleanPhone, 1);
        } elseif (strlen($cleanPhone) === 10) {
            $cleanPhone = '91' . $cleanPhone;
        }

        $apiToken = Setting::get('whatsapp_api_token') 
            ?: (env('WHATSAPP_CLOUD_API_TOKEN') ?: env('WHATSAPP_API_TOKEN'));

        // Auto-detect channel from template key if not explicitly set
        if ($channel === 'auth' && in_array($templateKey, ['whatsapp_template_order_confirmed', 'whatsapp_template_out_for_delivery'])) {
            $channel = 'updates';
        }

        // Determine Phone Number ID based on channel with seamless fallback
        $authPhoneId = Setting::get('whatsapp_phone_number_id') 
            ?: (env('WHATSAPP_CLOUD_PHONE_NUMBER_ID') ?: env('WHATSAPP_PHONE_NUMBER_ID'));

        $updatesPhoneId = Setting::get('whatsapp_updates_phone_number_id')
            ?: (env('WHATSAPP_UPDATES_PHONE_NUMBER_ID') ?: env('WHATSAPP_CLOUD_UPDATES_PHONE_NUMBER_ID'));

        // If updates channel is targeted and updates phone ID is configured, use it. Otherwise fallback gracefully to primary ID.
        if ($channel === 'updates' && !empty($updatesPhoneId)) {
            $phoneNumberId = $updatesPhoneId;
        } else {
            $phoneNumberId = $authPhoneId;
        }

        $defaultTemplate = match ($templateKey) {
            'whatsapp_template_order_confirmed'  => 'shopcalm_order_confirmed',
            'whatsapp_template_out_for_delivery' => 'shopcalm_out_for_delivery',
            default                              => 'shopcalm_otp',
        };

        $templateName = Setting::get($templateKey) 
            ?: (env(strtoupper($templateKey)) ?: $defaultTemplate);

        $langCode = Setting::get('whatsapp_template_language') 
            ?: (env('WHATSAPP_TEMPLATE_LANGUAGE') ?: 'en_US');

        if (empty($apiToken) || empty($phoneNumberId)) {
            Log::info("LOCAL DEV FALLBACK: WhatsApp Message [Channel: {$channel}, PhoneID: {$phoneNumberId}, Template: {$templateName}] for {$cleanPhone} (Params: " . json_encode($bodyParameters) . ")");
            return true;
        }

        try {
            $components = [];

            // Body parameters (e.g. OTP code or [Customer Name, Order Number, Amount, Payment Method])
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

            // Copy-code Button parameter for Meta Authentication Templates
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

            $payload = [
                'messaging_product' => 'whatsapp',
                'to'                => $cleanPhone,
                'type'              => 'template',
                'template'          => [
                    'name'       => $templateName,
                    'language'   => ['code' => $langCode],
                    'components' => $components,
                ],
            ];

            $response = Http::timeout(3)->withToken($apiToken)->post("https://graph.facebook.com/v18.0/{$phoneNumberId}/messages", $payload);

            // 1. Language mismatch retry (if template was registered under 'en' instead of 'en_US' or vice-versa)
            if ($response->failed() && (str_contains($response->body(), '132001') || str_contains($response->body(), 'translated language'))) {
                $altLang = ($langCode === 'en_US') ? 'en' : 'en_US';
                $payload['template']['language']['code'] = $altLang;
                $response = Http::timeout(3)->withToken($apiToken)->post("https://graph.facebook.com/v18.0/{$phoneNumberId}/messages", $payload);
            }

            // 2. If template with button fails, retry once with body-only components (fallback for templates without buttons)
            if ($response->failed() && !empty($buttonParameters)) {
                $bodyOnlyComponents = array_filter($components, fn($c) => ($c['type'] ?? '') === 'body');
                $payload['template']['components'] = array_values($bodyOnlyComponents);
                $retryRes = Http::timeout(3)->withToken($apiToken)->post("https://graph.facebook.com/v18.0/{$phoneNumberId}/messages", $payload);
                if ($retryRes->successful()) {
                    Log::info("Meta WhatsApp API Message dispatched to {$cleanPhone} [Template: {$templateName}] (body-only). Message ID: " . ($retryRes->json('messages.0.id') ?? 'N/A'));
                    return true;
                }
            }

            // 3. Parameter count auto-adaptation (supports 4, 2, 1, or 0 variable template designs)
            if ($response->failed() && (str_contains($response->body(), '132000') || str_contains($response->body(), 'does not match the expected number'))) {
                // 3a. Try 2 parameters [CustomerName, OrderNumber]
                if (count($bodyParameters) > 2) {
                    $reducedParams = array_slice($bodyParameters, 0, 2);
                    $reducedFormatted = array_map(fn($p) => ['type' => 'text', 'text' => (string)$p], $reducedParams);
                    $payload['template']['components'] = [
                        ['type' => 'body', 'parameters' => $reducedFormatted]
                    ];
                    $retryRes = Http::timeout(3)->withToken($apiToken)->post("https://graph.facebook.com/v18.0/{$phoneNumberId}/messages", $payload);
                    if ($retryRes->successful()) {
                        Log::info("Meta WhatsApp API Message dispatched to {$cleanPhone} [Template: {$templateName}] (2-variable adaptation). Message ID: " . ($retryRes->json('messages.0.id') ?? 'N/A'));
                        return true;
                    }
                    $response = $retryRes;
                }

                // 3b. Try 1 parameter (OrderNumber)
                if (str_contains($response->body(), '132000') || str_contains($response->body(), 'does not match the expected number')) {
                    $singleParam = [['type' => 'text', 'text' => (string) ($bodyParameters[1] ?? ($bodyParameters[0] ?? ''))]];
                    $payload['template']['components'] = [
                        ['type' => 'body', 'parameters' => $singleParam]
                    ];
                    $retryRes = Http::timeout(3)->withToken($apiToken)->post("https://graph.facebook.com/v18.0/{$phoneNumberId}/messages", $payload);
                    if ($retryRes->successful()) {
                        Log::info("Meta WhatsApp API Message dispatched to {$cleanPhone} [Template: {$templateName}] (1-variable adaptation). Message ID: " . ($retryRes->json('messages.0.id') ?? 'N/A'));
                        return true;
                    }
                    $response = $retryRes;
                }

                // 3c. Try 0 parameters (static body)
                if (str_contains($response->body(), '132000') || str_contains($response->body(), 'does not match the expected number')) {
                    $payload['template']['components'] = [];
                    $retryRes = Http::timeout(3)->withToken($apiToken)->post("https://graph.facebook.com/v18.0/{$phoneNumberId}/messages", $payload);
                    if ($retryRes->successful()) {
                        Log::info("Meta WhatsApp API Message dispatched to {$cleanPhone} [Template: {$templateName}] (0-variable adaptation). Message ID: " . ($retryRes->json('messages.0.id') ?? 'N/A'));
                        return true;
                    }
                    $response = $retryRes;
                }
            }

            // 4. Fallback template name retry (if default template name variation exists)
            if ($response->failed() && str_contains($response->body(), '132001') && $templateKey === 'whatsapp_template_order_confirmed') {
                $altTemplate = ($templateName === 'shopcalm_order_confirmed') ? 'shopcalm_order_confirmation' : 'shopcalm_order_confirmed';
                if ($altTemplate !== $templateName) {
                    $payload['template']['name'] = $altTemplate;
                    $retryRes = Http::timeout(3)->withToken($apiToken)->post("https://graph.facebook.com/v18.0/{$phoneNumberId}/messages", $payload);
                    if ($retryRes->successful()) {
                        Log::info("Meta WhatsApp API Message dispatched to {$cleanPhone} [Template: {$altTemplate}] (alternate name fallback). Message ID: " . ($retryRes->json('messages.0.id') ?? 'N/A'));
                        return true;
                    }
                    $response = $retryRes;
                }
            }

            if ($response->failed()) {
                Log::warning("Meta WhatsApp Cloud API could not send message for {$cleanPhone} [Template: {$templateName}]: " . $response->body());
                return false;
            }

            Log::info("Meta WhatsApp API Message dispatched to {$cleanPhone} [Template: {$templateName}]. Message ID: " . ($response->json('messages.0.id') ?? 'N/A'));
            return true;
        } catch (\Exception $e) {
            Log::warning("Meta WhatsApp API Exception for {$cleanPhone}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate 6-digit OTP and send via Meta WhatsApp Cloud API (Registration / Password Reset / Login).
     */
    public function sendOtp(string $mobileNumber, string $purpose = 'REGISTER'): string
    {
        $otp = (string) random_int(100000, 999999);

        OtpVerification::updateOrCreate(
            ['mobile_number' => $mobileNumber, 'purpose' => $purpose],
            [
                'otp_hash'    => Hash::make($otp),
                'expires_at'  => Carbon::now()->addMinutes(10),
                'verified_at' => null,
            ]
        );

        $this->sendTemplateMessage($mobileNumber, 'whatsapp_template_name', [$otp], [$otp]);

        return $otp;
    }

    /**
     * Verify OTP for a given mobile number and purpose (Registration / Password Reset / Login).
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
     * Resend authentication OTP.
     */
    public function resendOtp(string $mobileNumber, string $purpose = 'REGISTER'): string
    {
        return $this->sendOtp($mobileNumber, $purpose);
    }

    /**
     * Send Order Confirmation Notification.
     * Handled via In-App FCM Push Notification, Email & Website Checkout Screen.
     * WhatsApp notification is disabled to avoid customer inbox fatigue and save Meta conversation costs.
     */
    public function sendOrderConfirmation(Order $order): bool
    {
        $enabled = Setting::get('whatsapp_notify_order_confirmed', '0') === '1';
        if (!$enabled) {
            Log::info("[WhatsAppService] WhatsApp Order Confirmation is disabled (handled via App Push Notification & Email). Order #{$order->order_number}");
            return true;
        }

        // Prioritize the user's main registration mobile number (given & verified via OTP during registration)
        $phone = $order->user?->mobile_number 
            ?: ($order->user?->phone 
            ?: ($order->shipping_phone ?: null));

        if (empty($phone)) {
            Log::info("[WhatsAppService] Order #{$order->order_number} has no mobile phone. Skipping WhatsApp.");
            return false;
        }

        $customerName = trim($order->user?->name ?: ($order->shipping_name ?: 'Customer'));
        $orderNumber  = (string) $order->order_number;

        return $this->sendTemplateMessage(
            $phone,
            'whatsapp_template_order_confirmed',
            [
                $customerName,
                $orderNumber
            ]
        );
    }

    /**
     * Send Out for Delivery Notification via Meta WhatsApp Cloud API (Utility Category).
     * Compulsory notification dispatched automatically when the delivery partner scans the tote bag at hub morning dispatch.
     */
    public function sendOutForDeliveryLocal(Order $order): bool
    {
        $phone = $order->user?->mobile_number 
            ?: ($order->user?->phone 
            ?: ($order->shipping_phone ?: null));

        if (empty($phone)) {
            Log::info("[WhatsAppService] Order #{$order->order_number} has no mobile phone. Skipping Out for Delivery WhatsApp.");
            return false;
        }

        $customerName = trim($order->user?->name ?: ($order->shipping_name ?: 'Customer'));
        $orderNumber  = (string) $order->order_number;
        $riderName    = trim($order->fulfillment?->rider_name ?: 'Delivery Partner');
        $trackingUrl  = url("/orders/{$order->id}");

        // Parameters matching approved Meta Utility Out for Delivery template:
        // {{1}} = Customer Name
        // {{2}} = Order Number
        // {{3}} = Delivery Partner Name
        // {{4}} = Live Tracking Link
        return $this->sendTemplateMessage(
            $phone,
            'whatsapp_template_out_for_delivery',
            [
                $customerName,
                $orderNumber,
                $riderName,
                $trackingUrl,
            ],
            [],
            'updates'
        );
    }

    /**
     * Order Delivery Notification.
     * Handled via In-App FCM Push Notification & physical delivery slip handover by delivery partner.
     * WhatsApp delivery receipt is disabled to keep customer messaging lean and save Meta fees.
     */
    public function sendOrderDelivered(Order $order): bool
    {
        Log::info("[WhatsAppService] Order #{$order->order_number} delivered. Skipping WhatsApp message (handled by In-App FCM Push & physical delivery slip).");
        return true;
    }

    // =========================================================================
    // LEGACY STUBS: Kept as safe no-ops to prevent MethodNotFoundException
    // =========================================================================
    public function sendDispatchedCourier($order): bool { return false; }
    public function sendOrderCancelled($cancellation): bool { return false; }
    public function sendRefundProcessed($cancellation): bool { return false; }
}
