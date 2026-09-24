<?php

namespace App\Services;

use App\Models\OtpVerification;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class OtpService
{
    public function generateAndSend(string $email, string $purpose)
    {
        $otp = random_int(100000, 999999);

        OtpVerification::updateOrCreate(
            ['email' => $email, 'purpose' => $purpose],
            [
                'otp_hash' => Hash::make($otp),
                'expires_at' => Carbon::now()->addMinutes(10),
                'verified_at' => null,
            ]
        );

        $storeName = \App\Models\Setting::get('store_name', 'ShopCalm');
        $subject = "{$storeName} Verification OTP: {$otp}";
        $html = "<div style='font-family:sans-serif; padding:20px; text-align:center;'>
            <h2>{$storeName} Verification Code</h2>
            <p>Your 6-digit OTP code is:</p>
            <h1 style='color:#6366f1; letter-spacing:5px;'>{$otp}</h1>
            <p>Valid for 10 minutes. Do not share this code with anyone.</p>
        </div>";

        app(EmailService::class)->sendEmail($email, $subject, $html);
    }

    public function verify(string $email, string $otp, string $purpose): bool
    {
        $record = OtpVerification::where('email', $email)
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
}
