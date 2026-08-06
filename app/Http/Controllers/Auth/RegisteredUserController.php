<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    protected $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    public function create()
    {
        return view('auth.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'mobile_number' => ['required', 'string', 'max:10', 'unique:users,mobile_number'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'otp' => ['required', 'string'],
        ]);

        $isOtpValid = $this->otpService->verify($request->email, $request->otp, 'REGISTER');

        if (!$isOtpValid) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired OTP. Please enter the correct code.',
                    'errors' => ['otp' => ['Invalid or expired OTP. Please enter the correct code.']]
                ], 422);
            }
            return back()->withInput()->withErrors(['otp' => 'Invalid or expired OTP. Please enter the correct code.']);
        }

        $user = User::create([
            'name'              => $request->name,
            'email'             => $request->email,
            'mobile_number'     => $request->mobile_number,
            'password'          => Hash::make($request->password),
            'role_id'           => User::ROLE_CUSTOMER,
            'email_verified_at' => now(), // OTP already confirmed email ownership
        ]);

        event(new Registered($user));

        $guestSessionId = $request->session()->getId();

        Auth::login($user);

        // Merge guest session cart into newly registered customer's cart
        app(\App\Services\CartService::class)->mergeSessionCart($guestSessionId);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'redirect' => route('home')
            ]);
        }

        return redirect()->route('home');
    }
}
