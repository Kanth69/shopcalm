<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        $user->load(['profile', 'interests']);
        $categories = Category::where('status', 'Active')->orderBy('name')->get();

        $isSubscribed = !empty($user->email)
            ? \App\Models\Subscriber::where('email', strtolower(trim($user->email)))
                ->where('status', 'Subscribed')
                ->exists()
            : false;

        return view('customer.account.profile', [
            'user' => $user,
            'categories' => $categories,
            'isSubscribed' => $isSubscribed,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        
        $newEmail = $request->filled('email') ? strtolower(trim($request->email)) : null;

        $user->name = $request->input('name');
        $user->mobile_number = $request->input('mobile_number');

        if ($user->email !== $newEmail) {
            $user->email = $newEmail;
        }

        $user->save();

        // Update Customer Profile metadata
        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            $request->only(['gender', 'date_of_birth', 'preferred_language'])
        );

        // Update Interests
        $user->interests()->sync($request->input('interests', []));

        return Redirect::route('profile.edit')->with('toast', ['type' => 'success', 'title' => 'Success', 'message' => 'Profile updated successfully.']);
    }

    /**
     * Download customer personal data as JSON (DPDP Act 2023 Section 11 Right to Access).
     */
    public function exportData(Request $request)
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

        $fileName = 'shopcalm_my_personal_data_' . $user->id . '_' . date('Ymd_His') . '.json';

        return response()->streamDownload(function () use ($exportData) {
            echo json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }, $fileName, [
            'Content-Type' => 'application/json',
        ]);
    }

    /**
     * Delete customer account and erase personal data (DPDP Act 2023 compliance).
     */
    public function destroy(Request $request)
    {
        $user = $request->user();

        // 1. Block deletion if user has active/pending orders in transit
        $hasActiveOrders = \App\Models\Order::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'processing', 'confirmed', 'packed', 'shipped', 'out for delivery', 'Pending', 'Processing', 'Confirmed', 'Dispatched', 'Out for Delivery'])
            ->exists();

        if ($hasActiveOrders) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete account while you have active/pending orders in progress. Please wait until your active order is delivered or cancelled.'
            ], 422);
        }

        // 2. Anonymize user profile & remove personal data under DPDP Act 2023
        $deletedId = $user->id;
        
        // Remove saved shipping addresses
        \App\Models\Address::where('user_id', $deletedId)->delete();
        
        // Remove customer profile metadata
        if ($user->profile) {
            $user->profile()->delete();
        }

        // Clear cart & wishlist
        \App\Models\CartItem::whereHas('cart', function ($q) use ($deletedId) {
            $q->where('user_id', $deletedId);
        })->delete();
        \App\Models\Cart::where('user_id', $deletedId)->delete();
        \App\Models\WishlistItem::whereHas('wishlist', function ($q) use ($deletedId) {
            $q->where('user_id', $deletedId);
        })->delete();
        \App\Models\Wishlist::where('user_id', $deletedId)->delete();

        // Remove newsletter subscription
        if (!empty($user->email)) {
            \App\Models\Subscriber::where('email', strtolower(trim($user->email)))->delete();
        }

        // Anonymize main user record (Preserves user_id link on past order tax invoices for GST compliance)
        $user->name = 'Deleted Customer #' . $deletedId;
        $user->email = null;
        $user->mobile_number = null;
        $user->password = bcrypt(\Illuminate\Support\Str::random(32));
        $user->status = 'Deleted';
        $user->save();

        // Revoke Sanctum API tokens if mobile app was logged in
        if (method_exists($user, 'tokens')) {
            $user->tokens()->delete();
        }

        // Logout customer web session
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'success' => true,
            'message' => 'Your account and personal data have been erased in compliance with DPDP Act 2023.',
            'redirect' => route('home')
        ]);
    }
}
