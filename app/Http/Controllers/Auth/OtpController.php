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
        $identifier = trim($request->input('identifier', ''));

        if (empty($identifier)) {
            return response()->json([
                'success' => false,
                'message' => 'Please enter an email address or mobile number.'
            ], 422);
        }

        // Strict format validation
        $hasLetters = preg_match('/[a-zA-Z]/', $identifier);
        $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL) !== false;

        // Phone: must NOT contain letters and must be valid digits
        $cleanPhone = preg_replace('/[\s\-\(\)]/', '', $identifier);
        $isPhone = !$hasLetters && (
            preg_match('/^(\+?[0-9]{1,3})?[6-9][0-9]{9}$/', $cleanPhone) ||
            preg_match('/^\+?[1-9][0-9]{9,14}$/', $cleanPhone)
        );

        if (!$isEmail && !$isPhone) {
            return response()->json([
                'success' => false,
                'message' => 'Please enter a valid Email address (e.g. name@example.com) or 10-digit Mobile number.'
            ], 422);
        }

        $user = User::where('email', $identifier)
            ->orWhere('mobile_number', $identifier)
            ->orWhere('mobile_number', $cleanPhone)
            ->first();

        if ($user && in_array($user->status, ['Blocked', 'Deleted'])) {
            return response()->json([
                'success' => false,
                'exists'  => true,
                'blocked' => true,
                'message' => 'This account has been closed or blocked.'
            ]);
        }

        return response()->json([
            'success' => true,
            'exists'  => (bool)$user,
            'type'    => $isEmail ? 'email' : 'mobile',
        ]);
    }

    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'mobile_number' => 'nullable|string|digits:10|unique:users,mobile_number',
            'email' => 'required|email|unique:users,email',
        ], [
            'name.required' => 'Please enter your full name.',
            'mobile_number.required' => 'Please enter your mobile number.',
            'mobile_number.digits' => 'Please enter a valid 10-digit mobile number.',
            'mobile_number.unique' => 'This mobile number is already registered. Please sign in instead.',
            'email.required' => 'Please enter your email address.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email is already registered. Please sign in instead.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
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
            return response()->json(['success' => true, 'message' => 'Verification code sent to your email.']);
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
        ], [
            'otp.required' => 'Please enter the 6-digit verification code.',
            'otp.min' => 'Please enter the full 6-digit code.',
            'otp.max' => 'Please enter the full 6-digit code.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        $isValid = $this->otpService->verify($request->email, $request->otp, 'REGISTER');

        if (!$isValid) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired OTP. Please enter the correct code.',
                'errors' => ['otp' => ['Invalid or expired OTP. Please enter the correct code.']]
            ], 422);
        }

        // Store verified email in session for registration step 3 completion
        $request->session()->put('verified_registration_email', $request->email);
        $request->session()->put('verified_registration_otp', $request->otp);

        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully!'
        ]);
    }
}
