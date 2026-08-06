<?php

namespace App\Services;

use App\Models\OtpVerification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendOtpMail;
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

        Mail::to($email)->send(new SendOtpMail($otp));
    }

    public function verify(string $email, string $otp, string $purpose): bool
    {
        $record = OtpVerification::where('email', $email)
            ->where('purpose', $purpose)
            ->where('expires_at', '>', Carbon::now())
            ->whereNull('verified_at')
            ->first();

        if ($record && Hash::check($otp, $record->otp_hash)) {
            $record->update(['verified_at' => Carbon::now()]);
            return true;
        }

        return false;
    }
}
