<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
            return response()->json([
                'success' => true,
                'exists'  => true,
                'data'    => [
                    'exists'        => true,
                    'name'          => $user->name,
                    'mobile_number' => $user->mobile_number,
                    'email'         => $user->email,
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
     * Send WhatsApp OTP for Login, Registration, or Password Reset.
     */
    public function sendOtp(Request $request): JsonResponse
    {
        $request->validate([
            'mobile_number' => 'required|string',
            'type'          => 'nullable|string|in:LOGIN,REGISTER,RESET_PASSWORD',
        ]);

        $type = strtoupper($request->input('type', 'LOGIN'));
        
        if ($type === 'REGISTER') {
            $user = $this->findUserByIdentifier($request->mobile_number);
            if ($user) {
                return $this->sendError('This mobile number is already registered. Please sign in instead.', [], 422);
            }
        }

        $otp = $this->whatsAppService->sendOtp($request->mobile_number, $type);

        return $this->sendResponse([
            'otp_sent' => true,
            'dev_otp'  => app()->environment('local', 'testing') ? $otp : null,
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

        $type = strtoupper($request->input('type', 'LOGIN'));
        $isValid = $this->whatsAppService->verifyOtp($request->mobile_number, $request->otp, $type);

        if (!$isValid) {
            return $this->sendError('Invalid or expired WhatsApp OTP code.', [], 422);
        }

        $user = $this->findUserByIdentifier($request->mobile_number);

        if ($user) {
            // Revoke old tokens & issue new token
            $user->tokens()->delete();
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
        $request->validate([
            'mobile_number' => 'required|string|digits:10',
            'otp'           => 'required|string|size:6',
            'password'      => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'mobile_number.required' => 'Please enter your mobile number.',
            'otp.required'           => 'Please enter the 6-digit WhatsApp OTP.',
            'password.required'      => 'Please enter your new password.',
            'password.confirmed'     => 'Password confirmation does not match.',
        ]);

        $user = $this->findUserByIdentifier($request->mobile_number);
        if (!$user) {
            return $this->sendError('No registered account found with this mobile number.', [], 404);
        }

        $isValid = $this->whatsAppService->verifyOtp($request->mobile_number, $request->otp, 'RESET_PASSWORD');

        if (!$isValid) {
            return $this->sendError('Invalid or expired WhatsApp OTP code.', [], 422);
        }

        $user->password = Hash::make($request->password);
        $user->save();

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
        $request->validate([
            'login_identifier' => 'required|string',
            'password'         => 'required|string',
        ]);

        $user = $this->findUserByIdentifier($request->login_identifier);

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->sendError('Invalid login credentials.', [], 401);
        }

        if (isset($user->status) && in_array(strtolower($user->status), ['blocked', 'deleted'])) {
            return $this->sendError('This account has been closed or blocked. Please contact customer support.', [], 403);
        }

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
     * Register Customer Account Endpoint (Email Optional).
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'mobile_number' => 'required|string|max:15',
            'email'         => 'nullable|string|email|max:255',
            'password'      => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'name.required'          => 'Please enter your full name.',
            'mobile_number.required' => 'Please enter your mobile number.',
            'email.email'            => 'Please enter a valid email address.',
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

        $user = User::create([
            'name'          => $request->name,
            'mobile_number' => $request->mobile_number,
            'email'         => $request->filled('email') ? strtolower(trim($request->email)) : null,
            'password'      => Hash::make($request->password),
            'role_id'       => 2, // Customer role
            'status'        => 'active',
        ]);

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
        $wallet = app(\App\Services\WalletService::class)->getOrCreateWallet($user);

        return $this->sendResponse([
            'id'             => $user->id,
            'name'           => $user->name,
            'email'          => $user->email,
            'mobile_number'  => $user->mobile_number,
            'avatar'         => $user->avatar ? asset('storage/' . $user->avatar) : null,
            'wallet_balance' => (float) $wallet->balance,
            'created_at'     => $user->created_at ? $user->created_at->format('Y-m-d H:i:s') : null,
        ], 'Authenticated user profile retrieved.');
    }

    /**
     * Register Firebase Device FCM Push Token.
     */
    public function registerDeviceToken(Request $request): JsonResponse
    {
        $request->validate([
            'fcm_token' => 'required|string',
            'device_type' => 'nullable|string|in:android,ios',
        ]);

        $user = $request->user();
        $user->update([
            'fcm_token' => $request->fcm_token,
        ]);

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
