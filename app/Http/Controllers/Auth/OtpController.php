<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class OtpController extends Controller
{
    protected $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    public function checkUser(Request $request)
    {
        $identifier = $request->input('identifier');
        $user = User::where('email', $identifier)->orWhere('mobile_number', $identifier)->first();

        if ($user && $user->status === 'Blocked') {
            return response()->json([
                'exists'  => true,
                'blocked' => true,
                'message' => 'Your account has been blocked. Please contact support.'
            ]);
        }

        return response()->json(['exists' => (bool)$user]);
    }

    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $key = 'otp-resend:' . Str::slug($request->email);
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            $minutes = ceil($seconds / 60);
            return response()->json([
                'success' => false,
                'message' => "Maximum OTP limit (3/hour) reached. Try again in {$minutes} minutes."
            ], 429);
        }

        try {
            RateLimiter::hit($key, 3600); // 1 hour window
            $this->otpService->generateAndSend($request->email, 'REGISTER');
            return response()->json(['success' => true, 'message' => 'OTP sent successfully.']);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('OTP send failed: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['success' => false, 'message' => 'Failed to send OTP. Please try again.'], 500);
        }
    }

    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp' => 'required|string|min:6|max:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $isValid = $this->otpService->verify($request->email, $request->otp, 'REGISTER');

        if ($isValid) {
            return response()->json(['success' => true, 'verified' => true, 'message' => 'OTP verified.']);
        }

        return response()->json(['success' => false, 'verified' => false, 'message' => 'Invalid or expired OTP.']);
    }
}
