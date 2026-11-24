<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules;

class AuthController extends BaseApiController
{
    protected $whatsAppService;

    public function __construct(WhatsAppService $whatsAppService)
    {
        $this->whatsAppService = $whatsAppService;
    }

    /**
     * Helper to find user by mobile (normalized 10-digit LIKE search) or email.
     */
    private function findUserByIdentifier(string $identifier): ?User
    {
        $identifier = trim($identifier);
        $cleanPhone = preg_replace('/\D/', '', $identifier);
        $last10 = (strlen($cleanPhone) >= 10) ? substr($cleanPhone, -10) : null;

        return User::where(function($query) use ($identifier, $last10) {
            if ($last10) {
                $query->where('mobile_number', 'LIKE', '%' . $last10)
                      ->orWhere('email', strtolower($identifier));
            } else {
                $query->where('email', strtolower($identifier))
                      ->orWhere('mobile_number', $identifier);
            }
        })->first();
    }

    /**
     * Check if user exists by mobile number or email.
     */
    public function checkUser(Request $request): JsonResponse
    {
        $request->validate([
            'identifier' => 'required|string',
        ]);

        $user = $this->findUserByIdentifier($request->identifier);

        if ($user) {
            if (isset($user->status) && in_array(strtolower($user->status), ['blocked', 'deleted'])) {
                return response()->json([
                    'success' => false,
                    'blocked' => true,
                    'message' => 'This account has been closed or blocked. Please contact customer support.'
                ], 200);
            }

            return response()->json([
                'success' => true,
                'exists'  => true,
                'data'    => [
                    'exists'        => true,
                    'name'          => $user->name,
                    'masked_mobile' => $this->maskPhoneNumber($user->mobile_number),
                    'masked_email'  => $this->maskEmailAddress($user->email),
                    'identifier'    => $request->identifier,
                ],
                'message' => 'User account found.'
            ]);
        }

        return response()->json([
            'success' => true,
            'exists'  => false,
            'data'    => [
                'exists' => false,
            ],
            'message' => 'User account not found.'
        ]);
    }

    /**
     * Helper to mask phone numbers (e.g. 81******16) for zero PII leakage.
     */
    public function maskPhoneNumber(?string $phone): string
    {
        if (empty($phone)) return '••••••••••';
        $digits = preg_replace('/\D/', '', $phone);
        if (strlen($digits) >= 10) {
            $ten = substr($digits, -10);
            return substr($ten, 0, 2) . '******' . substr($ten, -2);
        }
        return '••••••••••';
    }

    /**
     * Helper to mask email addresses for zero PII leakage.
     */
    public function maskEmailAddress(?string $email): string
    {
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) return '••••@••••';
        [$user, $domain] = explode('@', $email);
        $len = strlen($user);
        if ($len <= 2) {
            $maskedUser = substr($user, 0, 1) . '*';
        } else {
            $maskedUser = substr($user, 0, 1) . str_repeat('*', max(1, $len - 2)) . substr($user, -1);
        }
        return $maskedUser . '@' . $domain;
    }

    /**
     * Send WhatsApp OTP for Login, Registration, or Password Reset.
     */
    public function sendOtp(Request $request): JsonResponse
    {
        $request->validate([
            'mobile_number' => 'nullable|string',
            'identifier'    => 'nullable|string',
            'type'          => 'nullable|string|in:LOGIN,REGISTER,RESET_PASSWORD',
        ]);

        $type = strtoupper($request->input('type', 'LOGIN'));
        
        $targetMobile = $request->input('mobile_number');
        if (empty($targetMobile) && $request->filled('identifier')) {
            $user = $this->findUserByIdentifier($request->identifier);
            if ($user && !empty($user->mobile_number)) {
                $targetMobile = $user->mobile_number;
            }
        }

        // Prevent OTP dispatch to blocked or deleted accounts
        if (in_array($type, ['LOGIN', 'RESET_PASSWORD'])) {
            $chkUser = null;
            if ($request->filled('identifier')) {
                $chkUser = $this->findUserByIdentifier($request->identifier);
            } elseif (!empty($targetMobile)) {
                $chkUser = $this->findUserByIdentifier($targetMobile);
            }
            if ($chkUser && isset($chkUser->status) && in_array(strtolower($chkUser->status), ['blocked', 'deleted'])) {
                return $this->sendError('This account has been closed or blocked. Please contact customer support.', [], 403);
            }
        }

        if ($type === 'RESET_PASSWORD') {
            $user = null;
            if ($request->filled('identifier')) {
                $user = $this->findUserByIdentifier($request->identifier);
            } elseif (!empty($targetMobile)) {
                $user = $this->findUserByIdentifier($targetMobile);
            }

            if (!$user || empty($user->mobile_number)) {
                return $this->sendError('No registered account found with this credential.', [], 404);
            }
            $targetMobile = $user->mobile_number;
        }

        if (empty($targetMobile)) {
            return $this->sendError('A valid mobile number is required to send OTP.', [], 422);
        }

        if ($type === 'REGISTER') {
            $user = $this->findUserByIdentifier($targetMobile);
            if ($user) {
                return $this->sendError('This mobile number is already registered. Please sign in instead.', [], 422);
            }
        }

        $otp = $this->whatsAppService->sendOtp($targetMobile, $type);

        return $this->sendResponse([
            'otp_sent'      => true,
            'masked_mobile' => $this->maskPhoneNumber($targetMobile),
            'dev_otp'       => app()->environment('local', 'testing') ? $otp : null,
        ], 'WhatsApp OTP sent successfully.');
    }

    /**
     * Verify WhatsApp OTP & Issue Sanctum Bearer Token.
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'mobile_number' => 'required|string',
            'otp'           => 'required|string|size:6',
            'type'          => 'nullable|string',
        ]);

        $type = strtoupper((string) $request->input('type', ''));
        $cleanPhone = preg_replace('/\D/', '', $request->mobile_number);
        $last10 = (strlen($cleanPhone) >= 10) ? substr($cleanPhone, -10) : $cleanPhone;

        $isValid = false;
        if (!empty($type)) {
            $isValid = $this->whatsAppService->verifyOtp($request->mobile_number, $request->otp, $type)
                    || $this->whatsAppService->verifyOtp($last10, $request->otp, $type);
        } else {
            $isValid = $this->whatsAppService->verifyOtp($request->mobile_number, $request->otp, 'LOGIN')
                    || $this->whatsAppService->verifyOtp($last10, $request->otp, 'LOGIN')
                    || $this->whatsAppService->verifyOtp($request->mobile_number, $request->otp, 'REGISTER')
                    || $this->whatsAppService->verifyOtp($last10, $request->otp, 'REGISTER');
        }

        if (!$isValid) {
            return $this->sendError('Invalid or expired WhatsApp OTP code.', [], 422);
        }

        $user = $this->findUserByIdentifier($request->mobile_number);

        if ($user) {
            // Revoke old tokens & issue new token
            $user->tokens()->delete();
            $token = $user->createToken('mobile_app_auth_token')->plainTextToken;

            // Clear any previous password login lockout on verified WhatsApp OTP login
            RateLimiter::clear('account_lockout:' . $user->id);

            return $this->sendResponse([
                'token' => $token,
                'user'  => [
                    'id'            => $user->id,
                    'name'          => $user->name,
                    'email'         => $user->email,
                    'mobile_number' => $user->mobile_number,
                    'avatar'        => $user->avatar ? asset('storage/' . $user->avatar) : null,
                ],
            ], 'WhatsApp OTP verified successfully.');
        }

        return $this->sendResponse([
            'verified' => true,
        ], 'WhatsApp OTP verified. Please complete registration.');
    }

    /**
     * Password Reset via WhatsApp OTP Endpoint (Zero Email Links).
     */
    public function resetPassword(Request $request): JsonResponse
    {
        if (!$request->has('password_confirmation') && $request->has('password')) {
            $request->merge(['password_confirmation' => $request->password]);
        }

        $request->validate([
            'mobile_number' => 'nullable|string',
            'identifier'    => 'nullable|string',
            'otp'           => 'required|string|size:6',
            'password'      => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'otp.required'       => 'Please enter the 6-digit WhatsApp OTP.',
            'password.required'  => 'Please enter your new password.',
            'password.confirmed' => 'Password confirmation does not match.',
        ]);

        $user = null;
        if ($request->filled('identifier')) {
            $user = $this->findUserByIdentifier($request->identifier);
        } elseif ($request->filled('mobile_number')) {
            $user = $this->findUserByIdentifier($request->mobile_number);
        }

        if (!$user || empty($user->mobile_number)) {
            return $this->sendError('No registered account found with this credential.', [], 404);
        }

        $isValid = $this->whatsAppService->verifyOtp($user->mobile_number, $request->otp, 'RESET_PASSWORD');

        if (!$isValid) {
            return $this->sendError('Invalid or expired WhatsApp OTP code.', [], 422);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        // Clear any password login lockout after verified OTP reset
        RateLimiter::clear('account_lockout:' . $user->id);

        // Generate new Sanctum token for instant auto-login after password reset
        $user->tokens()->delete();
        $token = $user->createToken('mobile_app_auth_token')->plainTextToken;

        return $this->sendResponse([
            'token' => $token,
            'user'  => [
                'id'            => $user->id,
                'name'          => $user->name,
                'email'         => $user->email,
                'mobile_number' => $user->mobile_number,
            ],
        ], 'Password reset successfully.');
    }

    /**
     * Password Login Endpoint.
     */
    public function login(Request $request): JsonResponse
    {
        // Support all variations: login_identifier, identifier, mobile_number, or email
        $identifier = $request->input('login_identifier')
            ?? ($request->input('identifier')
            ?? ($request->input('mobile_number')
            ?? $request->input('email')));

        if ($identifier) {
            $request->merge(['login_identifier' => trim((string)$identifier)]);
        }

        $request->validate([
            'login_identifier' => 'required|string',
            'password'         => 'required|string',
        ], [
            'login_identifier.required' => 'Please enter your mobile number or email address.',
            'password.required'         => 'Please enter your password.',
        ]);

        $user = $this->findUserByIdentifier($request->login_identifier);
        $throttleKey = 'account_lockout:' . ($user ? $user->id : md5(strtolower(trim($request->login_identifier))));

        // 1. Enforce 2-hour account lockout after 5 consecutive failed attempts
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $hours = floor($seconds / 3600);
            $minutes = ceil(($seconds % 3600) / 60);

            $timeRemainingText = $hours > 0
                ? "{$hours} hr " . ($minutes > 0 ? "{$minutes} min" : "")
                : "{$minutes} minutes";

            return $this->sendError(
                "Account temporarily locked for 2 hours due to 5 consecutive failed login attempts. Please try again in {$timeRemainingText}, or reset your password via WhatsApp OTP to unlock immediately.",
                [
                    'locked'              => true,
                    'retry_after_seconds' => $seconds,
                    'time_remaining_text' => $timeRemainingText,
                ],
                429
            );
        }

        if (!$user || !Hash::check($request->password, $user->password)) {
            RateLimiter::hit($throttleKey, 7200); // 2 hours = 7200 seconds
            $remaining = RateLimiter::remaining($throttleKey, 5);

            if ($remaining === 0) {
                return $this->sendError(
                    'Account temporarily locked for 2 hours due to 5 consecutive failed login attempts. You can reset your password via WhatsApp OTP to unlock immediately.',
                    [
                        'locked'              => true,
                        'retry_after_seconds' => 7200,
                    ],
                    429
                );
            }

            return $this->sendError(
                "Invalid login credentials. {$remaining} " . ($remaining === 1 ? 'attempt' : 'attempts') . ' remaining before account is locked for 2 hours.',
                ['attempts_remaining' => $remaining],
                401
            );
        }

        if (isset($user->status) && in_array(strtolower($user->status), ['blocked', 'deleted'])) {
            return $this->sendError('This account has been closed or blocked. Please contact customer support.', [], 403);
        }

        // On successful password login, clear failed attempt counter
        RateLimiter::clear($throttleKey);

        // Create Sanctum Bearer Token for Mobile App
        $token = $user->createToken('mobile_app_auth_token')->plainTextToken;

        return $this->sendResponse([
            'token' => $token,
            'user'  => [
                'id'            => $user->id,
                'name'          => $user->name,
                'email'         => $user->email,
                'mobile_number' => $user->mobile_number,
                'avatar'        => $user->avatar ? asset('storage/' . $user->avatar) : null,
            ],
        ], 'Login successful.');
    }

    /**
     * Register Customer Account Endpoint (Passwordless OTP or Password).
     */
    public function register(Request $request): JsonResponse
    {
        if ($request->filled('password') && !$request->has('password_confirmation')) {
            $request->merge(['password_confirmation' => $request->password]);
        }

        $rules = [
            'name'          => 'required|string|max:255',
            'mobile_number' => 'required|string|max:15',
            'email'         => 'nullable|string|email|max:255',
            'referral_code' => 'nullable|string|max:50',
        ];

        if ($request->filled('password')) {
            $rules['password'] = ['required', 'confirmed', Rules\Password::defaults()];
        } else {
            $rules['otp'] = 'required|string|max:6';
        }

        $request->validate($rules, [
            'name.required'          => 'Please enter your full name.',
            'mobile_number.required' => 'Please enter your mobile number.',
            'email.email'            => 'Please enter a valid email address.',
            'otp.required'           => 'Please enter the 6-digit WhatsApp OTP.',
            'password.required'      => 'Please set a secure password.',
            'password.confirmed'     => 'Password confirmation does not match.',
        ]);

        // Duplicate mobile check
        $existingPhone = $this->findUserByIdentifier($request->mobile_number);
        if ($existingPhone) {
            return $this->sendError('This mobile number is already registered. Please sign in instead.', [], 422);
        }

        // Duplicate email check (if provided)
        if ($request->filled('email')) {
            $existingEmail = User::whereNotNull('email')
                ->where('email', '!=', '')
                ->where('email', strtolower(trim($request->email)))
                ->first();
            if ($existingEmail) {
                return $this->sendError('This email address is already registered to another account.', [], 422);
            }
        }

        // Verify OTP if passwordless flow
        if (!$request->filled('password')) {
            $isValidOtp = $this->whatsAppService->verifyOtp($request->mobile_number, $request->otp, 'REGISTER');
            if (!$isValidOtp) {
                return $this->sendError('Invalid or expired WhatsApp OTP code.', [], 422);
            }
            $rawPassword = \Illuminate\Support\Str::random(32);
        } else {
            $rawPassword = $request->password;
        }

        $user = User::create([
            'name'               => $request->name,
            'mobile_number'      => $request->mobile_number,
            'email'              => $request->filled('email') ? strtolower(trim($request->email)) : null,
            'password'           => Hash::make($rawPassword),
            'role_id'            => User::ROLE_CUSTOMER,
            'status'             => 'Active',
            'mobile_verified_at' => now(),
        ]);

        if ($request->filled('referral_code')) {
            try {
                app(\App\Services\WalletService::class)->applySignupReferral($user, $request->input('referral_code'));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Signup referral error for user #{$user->id}: " . $e->getMessage());
            }
        }

        $token = $user->createToken('mobile_app_auth_token')->plainTextToken;

        return $this->sendResponse([
            'token' => $token,
            'user'  => [
                'id'            => $user->id,
                'name'          => $user->name,
                'email'         => $user->email,
                'mobile_number' => $user->mobile_number,
                'avatar'        => null,
            ],
        ], 'Account created successfully.', 201);
    }

    /**
     * Get Authenticated User Profile (For Auto-Login Validation).
     */
    public function user(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load('profile');
        $profile = $user->profile;
        $wallet = app(\App\Services\WalletService::class)->getOrCreateWallet($user);

        return $this->sendResponse([
            'id'                 => $user->id,
            'name'               => $user->name,
            'email'              => $user->email,
            'mobile_number'      => $user->mobile_number,
            'gender'             => $profile?->gender,
            'date_of_birth'      => $profile?->date_of_birth ? $profile->date_of_birth->format('Y-m-d') : null,
            'preferred_language' => $profile?->preferred_language,
            'avatar'             => $user->avatar ? asset('storage/' . $user->avatar) : null,
            'wallet_balance'     => (float) $wallet->balance,
            'referral_code'      => $wallet->referral_code,
            'is_referral_active' => \App\Services\WalletService::isReferralProgramActive(),
            'created_at'         => $user->created_at ? $user->created_at->format('Y-m-d H:i:s') : null,
        ], 'Authenticated user profile retrieved.');
    }

    /**
     * Register Firebase Device FCM Push Token.
     */
    public function registerDeviceToken(Request $request): JsonResponse
    {
        $request->validate([
            'fcm_token'   => 'required|string',
            'device_type' => 'nullable|string|in:android,ios,web',
            'device_id'   => 'nullable|string',
            'app_version' => 'nullable|string',
        ]);

        $user = $request->user();
        $deviceId = $request->input('device_id') ?: $request->header('X-Device-Id');
        $deviceType = $request->input('device_type', 'android');

        if ($user) {
            $user->update([
                'fcm_token' => $request->fcm_token,
            ]);
        }

        \App\Models\UserDevice::updateOrCreate(
            ['fcm_token' => $request->fcm_token],
            [
                'user_id'        => $user?->id,
                'device_id'      => $deviceId,
                'device_type'    => $deviceType,
                'app_version'    => $request->input('app_version'),
                'is_active'      => true,
                'last_active_at' => now(),
            ]
        );

        return $this->sendResponse([
            'registered' => true,
        ], 'Device FCM token registered successfully.');
    }

    /**
     * Logout Revoke Current Token Endpoint.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->sendResponse([], 'Logged out successfully.');
    }

    /**
     * Export Customer Personal Data JSON (DPDP Act 2023 Section 11 API).
     */
    public function exportData(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load(['profile', 'addresses', 'orders', 'wallet']);

        $exportData = [
            'export_date' => now()->toIso8601String(),
            'platform' => \App\Models\Setting::get('store_name', 'ShopCalm'),
            'legal_compliance' => 'Digital Personal Data Protection Act, 2023 (Section 11 - Right to Access)',
            'account_information' => [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email ?? 'Not provided',
                'mobile_number' => $user->mobile_number,
                'role' => 'Customer',
                'account_status' => $user->status,
                'registered_at' => $user->created_at ? $user->created_at->toIso8601String() : null,
            ],
            'profile_metadata' => $user->profile ? [
                'gender' => $user->profile->gender,
                'date_of_birth' => $user->profile->date_of_birth,
                'preferred_language' => $user->profile->preferred_language,
            ] : null,
            'saved_addresses' => $user->addresses->map(function ($addr) {
                return [
                    'recipient_name' => $addr->name ?? $addr->recipient_name,
                    'phone' => $addr->phone_number ?? $addr->phone,
                    'address_line_1' => $addr->address_line_1 ?? $addr->house_no_building,
                    'street' => $addr->address_line_2 ?? $addr->street_address,
                    'city' => $addr->city,
                    'state' => $addr->state,
                    'pincode' => $addr->pincode,
                    'address_type' => $addr->address_type ?? 'Home',
                ];
            }),
            'order_history' => $user->orders->map(function ($order) {
                return [
                    'order_number' => $order->order_number,
                    'order_date' => $order->created_at ? $order->created_at->toIso8601String() : null,
                    'total_amount' => $order->total_amount,
                    'status' => $order->status,
                    'payment_method' => $order->payment_method,
                    'payment_status' => $order->payment_status,
                ];
            }),
            'wallet_summary' => [
                'balance' => $user->wallet ? (float)$user->wallet->balance : 0.0,
                'referral_code' => $user->wallet ? $user->wallet->referral_code : 'WKREF100',
            ],
        ];

        return $this->sendResponse($exportData, 'Personal data retrieved successfully under DPDP Act 2023.');
    }

    /**
     * Delete Customer Account & Erase Personal Data (DPDP Act 2023 API).
     */
    public function deleteAccount(Request $request): JsonResponse
    {
        $user = $request->user();

        // 1. Block deletion if user has active/pending orders in transit
        $hasActiveOrders = \App\Models\Order::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'processing', 'confirmed', 'packed', 'shipped', 'out for delivery', 'Pending', 'Processing', 'Confirmed', 'Dispatched', 'Out for Delivery'])
            ->exists();

        if ($hasActiveOrders) {
            return $this->sendError('Cannot delete account while you have active/pending orders in progress. Please wait until your active order is delivered or cancelled.', [], 422);
        }

        // 2. Anonymize user profile & remove personal data under DPDP Act 2023
        $deletedId = $user->id;
        
        \App\Models\Address::where('user_id', $deletedId)->delete();
        
        if ($user->profile) {
            $user->profile()->delete();
        }

        \App\Models\CartItem::whereHas('cart', function ($q) use ($deletedId) {
            $q->where('user_id', $deletedId);
        })->delete();
        \App\Models\Cart::where('user_id', $deletedId)->delete();

        \App\Models\WishlistItem::whereHas('wishlist', function ($q) use ($deletedId) {
            $q->where('user_id', $deletedId);
        })->delete();
        \App\Models\Wishlist::where('user_id', $deletedId)->delete();

        if (!empty($user->email)) {
            \App\Models\Subscriber::where('email', strtolower(trim($user->email)))->delete();
        }

        // Anonymize user record
        $user->name = 'Deleted Customer #' . $deletedId;
        $user->email = null;
        $user->mobile_number = null;
        $user->password = bcrypt(\Illuminate\Support\Str::random(32));
        $user->status = 'Deleted';
        $user->save();

        // Revoke Sanctum API tokens
        if (method_exists($user, 'tokens')) {
            $user->tokens()->delete();
        }

        return $this->sendResponse([], 'Your account and personal data have been erased in compliance with DPDP Act 2023.');
    }
}
