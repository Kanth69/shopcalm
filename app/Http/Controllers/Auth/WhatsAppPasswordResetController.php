<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class WhatsAppPasswordResetController extends Controller
{
    protected $whatsAppService;

    public function __construct(WhatsAppService $whatsAppService)
    {
        $this->whatsAppService = $whatsAppService;
    }

    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetOtp(Request $request)
    {
        $identifier = $request->input('identifier') ?? $request->input('mobile_number');
        if (empty($identifier)) {
            return response()->json(['success' => false, 'message' => 'Please provide an email or registered mobile number.'], 422);
        }

        $cleanPhone = preg_replace('/[^\d]/', '', $identifier);
        $user = User::where('email', $identifier)
            ->orWhere('mobile_number', $identifier)
            ->orWhere('mobile_number', $cleanPhone)
            ->first();

        if (!$user || empty($user->mobile_number)) {
            return response()->json([
                'success' => false,
                'message' => 'We could not find an account registered with this credential.',
            ], 404);
        }

        $otp = $this->whatsAppService->sendOtp($user->mobile_number, 'RESET_PASSWORD');

        $clean = preg_replace('/\D/', '', $user->mobile_number);
        $masked = (strlen($clean) >= 10) ? substr($clean, 0, 2) . '******' . substr($clean, -2) : '••••••••••';

        return response()->json([
            'success'       => true,
            'masked_mobile' => $masked,
            'message'       => 'WhatsApp OTP code sent to your registered mobile number.',
            'dev_otp'       => app()->environment('local', 'testing') ? $otp : null,
        ]);
    }

    public function resetPassword(Request $request)
    {
        $identifier = $request->input('identifier') ?? $request->input('mobile_number');
        if (empty($identifier)) {
            return response()->json(['success' => false, 'message' => 'Please provide your account credential.'], 422);
        }

        $cleanPhone = preg_replace('/[^\d]/', '', $identifier);
        $user = User::where('email', $identifier)
            ->orWhere('mobile_number', $identifier)
            ->orWhere('mobile_number', $cleanPhone)
            ->first();

        if (!$user || empty($user->mobile_number)) {
            return response()->json([
                'success' => false,
                'message' => 'We could not find an account registered with this credential.',
            ], 404);
        }

        $request->validate([
            'otp'      => ['required', 'string', 'max:6'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'otp.required'       => 'Please enter the 6-digit WhatsApp OTP code.',
            'password.required'  => 'Please enter your new password.',
            'password.confirmed' => 'The password confirmation does not match.',
        ]);

        $isValidOtp = $this->whatsAppService->verifyOtp($user->mobile_number, $request->otp, 'RESET_PASSWORD');

        if (!$isValidOtp) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired WhatsApp OTP code. Please request a new OTP.',
                    'errors'  => ['otp' => ['Invalid or expired WhatsApp OTP code.']]
                ], 422);
            }
            return back()->withInput()->withErrors(['otp' => 'Invalid or expired WhatsApp OTP code.']);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        Auth::login($user);

        if ($request->wantsJson()) {
            return response()->json([
                'success'  => true,
                'message'  => 'Password updated successfully! You are now logged in.',
                'redirect' => route('home')
            ]);
        }

        return redirect()->route('home')->with('status', 'Password reset successfully!');
    }
}
