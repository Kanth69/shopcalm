<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    protected $whatsAppService;

    public function __construct(WhatsAppService $whatsAppService)
    {
        $this->whatsAppService = $whatsAppService;
    }

    public function create()
    {
        return view('auth.index');
    }

    private function findExistingUserByPhone(string $mobileNumber): ?User
    {
        $cleanPhone = preg_replace('/\D/', '', $mobileNumber);
        if (empty($cleanPhone)) return null;

        $last10 = (strlen($cleanPhone) >= 10) ? substr($cleanPhone, -10) : $cleanPhone;

        return User::where('mobile_number', 'LIKE', '%' . $last10)->first();
    }

    private function findExistingUserByEmail(string $email): ?User
    {
        $cleanEmail = strtolower(trim($email));
        if (empty($cleanEmail)) return null;

        return User::whereNotNull('email')
            ->where('email', '!=', '')
            ->where('email', $cleanEmail)
            ->first();
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'mobile_number' => ['required', 'string', 'digits:10'],
        ], [
            'mobile_number.required' => 'Please enter your mobile number.',
            'mobile_number.digits'   => 'Please enter a valid 10-digit mobile number.',
        ]);

        $existingUser = $this->findExistingUserByPhone($request->mobile_number);

        if ($existingUser) {
            return response()->json([
                'success' => false,
                'message' => 'This mobile number is already registered. Please sign in instead.',
                'errors'  => ['mobile_number' => ['This mobile number is already registered. Please sign in instead.']]
            ], 422);
        }

        $otp = $this->whatsAppService->sendOtp($request->mobile_number, 'REGISTER');

        return response()->json([
            'success' => true,
            'message' => 'WhatsApp OTP sent successfully.',
            'dev_otp' => app()->environment('local', 'testing') ? $otp : null,
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'mobile_number' => ['required', 'string', 'digits:10'],
            'otp'           => ['required', 'string', 'max:6'],
        ]);

        $isValid = $this->whatsAppService->verifyOtp($request->mobile_number, $request->otp, 'REGISTER');

        if ($isValid) {
            $request->session()->put('verified_registration_mobile', $request->mobile_number);
            return response()->json([
                'success' => true,
                'message' => 'WhatsApp OTP verified successfully.',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid or expired WhatsApp OTP code. Please try again.',
        ], 422);
    }

    public function store(Request $request)
    {
        // Normalize empty string email to null
        if (!$request->filled('email')) {
            $request->merge(['email' => null]);
        } else {
            $request->merge(['email' => strtolower(trim($request->email))]);
        }

        $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'mobile_number' => ['required', 'string', 'digits:10'],
            'email'         => ['nullable', 'string', 'email', 'max:255'],
            'otp'           => ['required', 'string', 'max:6'],
            'password'      => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'name.required'          => 'Please enter your full name.',
            'mobile_number.required' => 'Please enter your mobile number.',
            'mobile_number.digits'   => 'Please enter a valid 10-digit mobile number.',
            'email.email'            => 'Please enter a valid email address.',
            'otp.required'           => 'Please enter the 6-digit WhatsApp OTP code.',
            'password.required'      => 'Please create a password.',
            'password.confirmed'     => 'The password confirmation does not match.',
        ]);

        // 1. Strict Duplicate Mobile Check
        $existingPhoneUser = $this->findExistingUserByPhone($request->mobile_number);

        if ($existingPhoneUser) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This mobile number is already registered. Please sign in instead.',
                    'errors'  => ['mobile_number' => ['This mobile number is already registered. Please sign in instead.']]
                ], 422);
            }
            return back()->withInput()->withErrors(['mobile_number' => 'This mobile number is already registered. Please sign in instead.']);
        }

        // 2. Strict Duplicate Email Check (if email provided)
        if ($request->filled('email')) {
            $existingEmailUser = $this->findExistingUserByEmail($request->email);

            if ($existingEmailUser) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This email address is already registered to another account.',
                        'errors'  => ['email' => ['This email address is already registered to another account.']]
                    ], 422);
                }
                return back()->withInput()->withErrors(['email' => 'This email address is already registered to another account.']);
            }
        }

        // 3. Verify OTP
        $isSessionVerified = ($request->session()->get('verified_registration_mobile') === $request->mobile_number);
        $isOtpValid = $isSessionVerified || $this->whatsAppService->verifyOtp($request->mobile_number, $request->otp, 'REGISTER');

        if (!$isOtpValid) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired WhatsApp OTP code. Please try again.',
                    'errors'  => ['otp' => ['Invalid or expired WhatsApp OTP code.']]
                ], 422);
            }
            return back()->withInput()->withErrors(['otp' => 'Invalid or expired WhatsApp OTP code.']);
        }

        $request->session()->forget('verified_registration_mobile');

        $user = User::create([
            'name'              => $request->name,
            'email'             => $request->email ?: null,
            'mobile_number'     => $request->mobile_number,
            'password'          => Hash::make($request->password),
            'role_id'           => User::ROLE_CUSTOMER,
            'email_verified_at' => now(),
        ]);

        // Initialize wallet & apply referral rewards if referral code supplied
        $referralCode = $request->referral_code ?? $request->session()->get('referral_code');
        app(\App\Services\WalletService::class)->applySignupReferral($user, $referralCode);

        event(new Registered($user));

        $guestSessionId = $request->session()->getId();

        Auth::login($user);

        // Merge guest session cart into newly registered customer's cart
        app(\App\Services\CartService::class)->mergeSessionCart($guestSessionId);

        if ($request->wantsJson()) {
            return response()->json([
                'success'  => true,
                'redirect' => route('home')
            ]);
        }

        return redirect()->route('home');
    }
}
