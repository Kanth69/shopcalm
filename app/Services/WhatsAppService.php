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

        $apiToken = env('WHATSAPP_CLOUD_API_TOKEN');
        $phoneNumberId = env('WHATSAPP_CLOUD_PHONE_NUMBER_ID');

        if (!empty($apiToken) && !empty($phoneNumberId)) {
            try {
                $response = Http::withToken($apiToken)->post("https://graph.facebook.com/v18.0/{$phoneNumberId}/messages", [
                    'messaging_product' => 'whatsapp',
                    'to' => $cleanPhone,
                    'type' => 'template',
                    'template' => [
                        'name' => 'authentication_otp',
                        'language' => ['code' => 'en_US'],
                        'components' => [
                            [
                                'type' => 'body',
                                'parameters' => [
                                    ['type' => 'text', 'text' => $otp]
                                ]
                            ],
                            [
                                'type' => 'button',
                                'sub_type' => 'url',
                                'index' => '0',
                                'parameters' => [
                                    ['type' => 'text', 'text' => $otp]
                                ]
                            ]
                        ]
                    ]
                ]);

                if ($response->failed()) {
                    Log::warning("Meta WhatsApp Cloud API HTTP Error: " . $response->body());
                    // Fallback log
                    Log::info("FALLBACK: WhatsApp OTP for {$mobileNumber} [{$purpose}]: {$otp}");
                }
            } catch (\Exception $e) {
                Log::error("Meta WhatsApp API Exception: " . $e->getMessage());
                Log::info("FALLBACK: WhatsApp OTP for {$mobileNumber} [{$purpose}]: {$otp}");
            }
        } else {
            // Local / Sandbox Dev Fallback Mode
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
