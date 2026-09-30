<?php

namespace App\Services;

use App\Models\OtpVerification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class WhatsAppService
{
    /**
     * Generate 6-digit OTP and send via Meta WhatsApp Cloud API (or Log fallback).
     */
    public function sendOtp(string $mobileNumber, string $purpose = 'REGISTER'): string
    {
        // Sanitize phone number (strip non-digits)
        $cleanPhone = preg_replace('/\D/', '', $mobileNumber);
        if (strlen($cleanPhone) === 10) {
            $cleanPhone = '91' . $cleanPhone; // Default to India country code if 10 digits
        }

        $otp = (string) random_int(100000, 999999);

        OtpVerification::updateOrCreate(
            ['mobile_number' => $mobileNumber, 'purpose' => $purpose],
            [
                'otp_hash' => Hash::make($otp),
                'expires_at' => Carbon::now()->addMinutes(10),
                'verified_at' => null,
            ]
        );

        $apiToken = \App\Models\Setting::get('whatsapp_api_token') 
            ?: (env('WHATSAPP_CLOUD_API_TOKEN') ?: env('WHATSAPP_API_TOKEN'));

        $phoneNumberId = \App\Models\Setting::get('whatsapp_phone_number_id') 
            ?: (env('WHATSAPP_CLOUD_PHONE_NUMBER_ID') ?: env('WHATSAPP_PHONE_NUMBER_ID'));

        $templateName = \App\Models\Setting::get('whatsapp_template_name') 
            ?: (env('WHATSAPP_TEMPLATE_NAME') ?: 'authentication_otp');

        $langCode = \App\Models\Setting::get('whatsapp_template_language') 
            ?: (env('WHATSAPP_TEMPLATE_LANGUAGE') ?: 'en');

        if (!empty($apiToken) && !empty($phoneNumberId)) {
            try {
                $sendMetaRequest = function ($lang, $includeButton = true) use ($apiToken, $phoneNumberId, $cleanPhone, $templateName, $otp) {
                    $components = [
                        [
                            'type'       => 'body',
                            'parameters' => [
                                ['type' => 'text', 'text' => $otp]
                            ]
                        ]
                    ];

                    if ($includeButton) {
                        $components[] = [
                            'type'       => 'button',
                            'sub_type'   => 'url',
                            'index'      => '0',
                            'parameters' => [
                                ['type' => 'text', 'text' => $otp]
                            ]
                        ];
                    }

                    return Http::timeout(5)->withToken($apiToken)->post("https://graph.facebook.com/v18.0/{$phoneNumberId}/messages", [
                        'messaging_product' => 'whatsapp',
                        'to'                => $cleanPhone,
                        'type'              => 'template',
                        'template'          => [
                            'name'       => $templateName,
                            'language'   => ['code' => $lang],
                            'components' => $components
                        ]
                    ]);
                };

                // 1st Attempt: Primary language code + button
                $response = $sendMetaRequest($langCode, true);

                // If button mismatch error, retry primary language code without button
                if ($response->failed() && str_contains($response->body(), 'button')) {
                    Log::info("Retrying Meta WhatsApp Cloud API request without button component for template: {$templateName}");
                    $response = $sendMetaRequest($langCode, false);
                }

                // If language code mismatch error (#132001), try 'en' and 'en_IND'
                if ($response->failed() && (str_contains($response->body(), '132001') || str_contains($response->body(), 'does not exist in'))) {
                    $altLangs = ($langCode === 'en') ? ['en_US', 'en_IND'] : ['en', 'en_IND'];
                    foreach ($altLangs as $altLang) {
                        Log::info("Retrying Meta WhatsApp Cloud API with language code: {$altLang}");
                        $response = $sendMetaRequest($altLang, true);
                        if ($response->failed() && str_contains($response->body(), 'button')) {
                            $response = $sendMetaRequest($altLang, false);
                        }
                        if ($response->successful()) {
                            break;
                        }
                    }
                }

                if ($response->failed()) {
                    Log::error("Meta WhatsApp Cloud API Dispatch Error for {$cleanPhone}: " . $response->body());
                    // Fallback log for server monitoring
                    Log::info("FALLBACK OTP LOG for {$mobileNumber} [{$purpose}]: {$otp}");
                } else {
                    Log::info("Meta WhatsApp Cloud API OTP dispatched successfully to {$cleanPhone}. Message ID: " . ($response->json('messages.0.id') ?? 'N/A'));
                }
            } catch (\Exception $e) {
                Log::error("Meta WhatsApp API Exception: " . $e->getMessage());
                Log::info("FALLBACK OTP LOG for {$mobileNumber} [{$purpose}]: {$otp}");
            }
        } else {
            // Local / Sandbox Dev Fallback Mode when no API keys are provided
            Log::info("LOCAL DEV FALLBACK: WhatsApp OTP for {$mobileNumber} [{$purpose}]: {$otp}");
        }

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
            $record->delete(); // Delete after single successful verification
            return true;
        }

        return false;
    }
}
