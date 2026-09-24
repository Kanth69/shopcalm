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
        $request->validate([
            'mobile_number' => ['required', 'string', 'digits:10', 'exists:users,mobile_number'],
        ], [
            'mobile_number.required' => 'Please enter your registered mobile number.',
            'mobile_number.digits'   => 'Please enter a valid 10-digit mobile number.',
            'mobile_number.exists'   => 'We could not find an account registered with this mobile number.',
        ]);

        $otp = $this->whatsAppService->sendOtp($request->mobile_number, 'RESET_PASSWORD');

        return response()->json([
            'success' => true,
            'message' => 'WhatsApp OTP code sent to your mobile number.',
            'dev_otp' => app()->environment('local', 'testing') ? $otp : null,
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'mobile_number' => ['required', 'string', 'digits:10', 'exists:users,mobile_number'],
            'otp'           => ['required', 'string', 'max:6'],
            'password'      => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'mobile_number.required' => 'Please enter your mobile number.',
            'mobile_number.exists'   => 'We could not find an account registered with this mobile number.',
            'otp.required'           => 'Please enter the 6-digit WhatsApp OTP code.',
            'password.required'      => 'Please enter your new password.',
            'password.confirmed'     => 'The password confirmation does not match.',
        ]);

        $isValidOtp = $this->whatsAppService->verifyOtp($request->mobile_number, $request->otp, 'RESET_PASSWORD');

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

        $user = User::where('mobile_number', $request->mobile_number)->first();
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
