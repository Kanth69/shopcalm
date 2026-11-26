<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Address;
use App\Models\Order;
use App\Models\OrderFeedback;
use App\Models\OrderItem;
use App\Models\OrderReplacement;
use App\Models\Setting;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AccountController extends BaseApiController
{
    protected $orderService;
    protected $walletService;

    public function __construct(OrderService $orderService, WalletService $walletService)
    {
        $this->orderService = $orderService;
        $this->walletService = $walletService;
    }

    /**
     * Customer Orders History List.
     */
    public function orders(Request $request): JsonResponse
    {
        $user = $request->user();
        $status = $request->query('status');

        $query = $user->orders()->with(['items.product.brand', 'items.product.category', 'fulfillment', 'cancellation'])->latest();

        if ($status) {
            $query->where('status', $status);
        }

        $orders = $query->paginate(20);

        $allowCancellationSetting = Setting::get('allow_customer_cancellation', '1') == '1';

        $formattedOrders = collect($orders->items())->map(function (Order $order) use ($allowCancellationSetting) {
            $isPendingUnpaid = in_array($order->status, ['pending', 'failed', '']) || $order->payment_status === 'failed';
            $canCustomerCancel = !in_array($order->status, ['cancelled', 'delivered']) &&
                ($isPendingUnpaid || ($allowCancellationSetting && in_array($order->status, ['confirmed', 'processing'])));

            return [
                'id'                          => $order->id,
                'order_number'                => $order->order_number,
                'status'                      => $order->status,
                'payment_method'              => $order->payment_method,
                'payment_status'              => $order->payment_status,
                'item_count'                  => $order->items->count(),
                'subtotal_amount'             => (float) $order->subtotal_amount,
                'shipping_charge'             => (float) ($order->shipping_charge ?? 0),
                'wallet_amount_used'          => (float) ($order->wallet_amount_used ?? 0),
                'total_amount'                => (float) $order->total_amount,
                'delivery_otp'                => null,
                'is_local_bengaluru'          => $order->isLocalBengaluruDelivery(),
                'can_customer_cancel'         => $canCustomerCancel,
                'allow_customer_cancellation' => $allowCancellationSetting,
                'created_at'                  => $order->created_at ? $order->created_at->format('d M, Y') : '',
                'created_at_full'             => $order->created_at ? $order->created_at->format('d M, Y h:i A') : '',
                'cancellation'                => $order->cancellation ? [
                    'refund_status'        => $order->cancellation->refund_status,
                    'online_refund_status' => $order->cancellation->online_refund_status,
                    'refund_amount'        => (float) $order->cancellation->refund_amount,
                    'wallet_refund_amount' => (float) ($order->cancellation->wallet_refund_amount ?? 0),
                    'online_refund_amount' => (float) ($order->cancellation->online_refund_amount ?? 0),
                    'refund_method'        => $order->cancellation->refund_method,
                    'razorpay_refund_id'   => $order->cancellation->razorpay_refund_id,
                ] : null,
                'items'                       => $order->items->map(function ($item) {
                    $imgPath = $item->product ? ($item->product->main_image ?? $item->product->featured_image ?? null) : null;
                    $unitPrice = (float) ($item->unit_price ?? $item->price ?? 0);
                    $qty = (int) ($item->quantity ?? 1);
                    $totPrice = (float) ($item->total_price ?? $item->subtotal ?? ($unitPrice * $qty));
                    $mrp = (float) ($item->product?->mrp && (float) $item->product?->mrp > 0
                        ? $item->product->mrp
                        : ($item->original_price && (float) $item->original_price > $unitPrice
                            ? $item->original_price
                            : ($unitPrice > 0 ? round($unitPrice * 1.25, 2) : $unitPrice)));
                    if ($mrp < $unitPrice) {
                        $mrp = $unitPrice;
                    }
                    $isReplaceable = (bool) ($item->product?->is_replaceable ?? false);
                    $replacementDays = (int) ($item->product?->replacement_window_days ?? 0);

                    return [
                        'id'                       => $item->id,
                        'product_id'               => $item->product_id,
                        'product_slug'             => $item->product?->slug,
                        'product_name'             => $item->product_name ?? $item->product?->name ?? 'Product',
                        'brand_name'               => $item->product?->brand?->name,
                        'category_name'            => $item->product?->category?->name,
                        'product_image'            => $imgPath ? (str_starts_with($imgPath, 'http') ? $imgPath : asset('storage/' . $imgPath)) : null,
                        'selected_option'          => $item->selected_option,
                        'quantity'                 => $qty,
                        'original_price'           => $mrp,
                        'mrp'                      => $mrp,
                        'unit_price'               => $unitPrice,
                        'price'                    => $unitPrice,
                        'total_price'              => $totPrice,
                        'subtotal'                 => $totPrice,
                        'is_replaceable'           => $isReplaceable,
                        'replacement_window_days'  => $replacementDays,
                        'replacement_policy_badge' => $isReplaceable ? "{$replacementDays}D Replacement" : "Non-Returnable",
                    ];
                }),
            ];
        });

        return $this->sendResponse([
            'orders'       => $formattedOrders,
            'current_page' => $orders->currentPage(),
            'last_page'    => $orders->lastPage(),
            'total'        => $orders->total(),
        ], 'Customer orders retrieved.');
    }

    /**
     * Single Order Details with Stepper & Logistics Info.
     */
    public function orderDetails(Request $request, Order $order): JsonResponse
    {
        $user = $request->user();

        if ((int)$order->user_id !== (int)$user->id) {
            return $this->sendError('Unauthorized access to this order.', [], 403);
        }

        $order->load(['items.product.brand', 'items.product.category', 'fulfillment', 'cancellation.cancelledBy', 'coupon', 'primaryPayment']);

        $cancellation = $order->cancellation;
        $cancellationData = null;
        if ($cancellation) {
            $cancellationData = [
                'cancelled_by_type'    => $cancellation->cancelled_by_type,
                'cancellation_reason'  => $cancellation->cancellation_reason,
                'admin_notes'          => $cancellation->admin_notes,
                'cancellation_fee'     => (float) $cancellation->cancellation_fee,
                'refund_amount'        => (float) $cancellation->refund_amount,
                'wallet_refund_amount' => (float) ($cancellation->wallet_refund_amount ?? 0),
                'online_refund_amount' => (float) ($cancellation->online_refund_amount ?? 0),
                'refund_status'        => $cancellation->refund_status,
                'online_refund_status' => $cancellation->online_refund_status,
                'refund_method'        => $cancellation->refund_method,
                'payment_reference'    => $cancellation->razorpay_refund_id ?: $cancellation->payment_reference,
                'razorpay_refund_id'   => $cancellation->razorpay_refund_id,
                'cancelled_at'         => $cancellation->created_at ? $cancellation->created_at->format('d M, Y h:i A') : null,
            ];
        }

        $isPendingUnpaid = in_array($order->status, ['pending', 'failed', '']) || $order->payment_status === 'failed';
        $allowCancellationSetting = Setting::get('allow_customer_cancellation', '1') == '1';
        $canCustomerCancel = !in_array($order->status, ['cancelled', 'delivered']) &&
            ($isPendingUnpaid || ($allowCancellationSetting && in_array($order->status, ['confirmed', 'processing'])));

        $rawNotes = $order->notes ?? '';
        $prefTimeSlot = '';
        $cleanInstruction = $rawNotes;
        if (preg_match('/\[Time:\s*([^\]]+)\]/i', $rawNotes, $noteMatches)) {
            $prefTimeSlot = trim($noteMatches[1]);
            $cleanInstruction = trim(str_replace($noteMatches[0], '', $rawNotes));
        }

        $fulfillment = $order->fulfillment;
        $podPhoto = null;
        $dropType = null;
        if ($fulfillment && preg_match('/\[POD_PHOTO:(.*?)\]/', $fulfillment->notes ?? '', $mPhoto)) {
            $podPhoto = asset('storage/' . $mPhoto[1]);
        }
        if ($fulfillment && preg_match('/\[DROP_TYPE:(.*?)\]/', $fulfillment->notes ?? '', $mDrop)) {
            $dropType = $mDrop[1];
        }

        $order->loadMissing('feedback');

        return $this->sendResponse([
            'id'                          => $order->id,
            'order_number'                => $order->order_number,
            'invoice_number'              => 'INV-' . $order->order_number,
            'invoice_url'                 => url('/orders/' . $order->id . '/tax-invoice'),
            'status'                      => $order->status,
            'is_out_for_delivery'         => $order->status === 'out for delivery' || $order->status === 'out_for_delivery',
            'is_delivered'                => $order->status === 'delivered',
            'is_cancelled'                => $order->status === 'cancelled',
            'payment_method'              => $order->payment_method,
            'payment_status'              => $order->payment_status,
            'bank_reference'              => $order->primaryPayment?->bank_reference,
            'gateway_payment_id'          => $order->primaryPayment?->gateway_payment_id,
            'subtotal'                    => (float) $order->subtotal_amount,
            'shipping_cost'               => (float) ($order->shipping_charge ?? $order->shipping_cost ?? 0),
            'cod_fee'                     => (float) $order->cod_fee,
            'coupon_code'                 => $order->coupon?->code,
            'coupon_discount'             => (float) $order->coupon_discount_amount,
            'wallet_amount_used'          => (float) $order->wallet_amount_used,
            'total_amount'                => (float) $order->total_amount,
            'notes'                       => $rawNotes,
            'delivery_instructions'       => $cleanInstruction,
            'preferred_time_slot'         => $prefTimeSlot,
            'pod_photo_url'               => $podPhoto,
            'drop_type'                   => $dropType,
            'customer_delivery_decision'  => $fulfillment ? [
                'has_issue'    => (bool) $fulfillment->hasActiveDeliveryIssue(),
                'issue_reason' => $fulfillment->delivery_issue,
                'resolution'   => $fulfillment->delivery_issue_resolution,
                'resolved_at'  => $fulfillment->delivery_issue_resolved_at?->format('d M, Y h:i A'),
                'delivery_slot'=> $fulfillment->delivery_slot ?: ($order->delivery_slot ?: 'Fast Doorstep Delivery'),
            ] : null,
            'delivery_feedback'           => $order->feedback ? [
                'delivery_rating' => (int) $order->feedback->delivery_rating,
                'product_rating'  => (int) $order->feedback->product_rating,
                'tags'            => $order->feedback->tags ?? [],
                'comment'         => $order->feedback->comment,
            ] : null,
            'shipping_name'               => $order->shipping_name,
            'shipping_email'              => $order->shipping_email,
            'shipping_phone'              => $order->shipping_phone,
            'shipping_address'            => $order->shipping_address,
            'shipping_city'               => $order->shipping_city,
            'shipping_state'              => $order->shipping_state,
            'shipping_pincode'            => $order->shipping_zip,
            'shipping_country'            => $order->shipping_country ?? 'India',
            'is_local_bengaluru'          => $order->isLocalBengaluruDelivery(),
            'courier_partner'             => $order->courier_partner,
            'tracking_number'             => $order->tracking_number,
            'tracking_url'                => $order->tracking_url,
            'rider_name'                  => $order->rider_name,
            'rider_phone'                 => $order->rider_phone,
            'delivery_slot'               => $order->delivery_slot,
            'delivery_otp'                => null,
            'delivered_at'                => $order->delivered_at ? $order->delivered_at->format('d M, Y h:i A') : null,
            'can_customer_cancel'         => $canCustomerCancel,
            'allow_customer_cancellation' => $allowCancellationSetting,
            'cancellation'                => $cancellationData,
            'seller_info'                 => [
                'company_name' => Setting::get('invoice_company_name', Setting::get('company_name', 'ShopCalm Retail Pvt. Ltd.')),
                'address'      => Setting::get('address', 'Bengaluru, Karnataka - 560001'),
                'gstin'        => Setting::get('invoice_gstin', Setting::get('gstin', '29AABCU9603R1ZM')),
                'cin'          => Setting::get('invoice_cin', Setting::get('cin', 'U52100KA2024PTC184920')),
                'state'        => 'Karnataka',
                'state_code'   => '29',
                'email'        => Setting::get('contact_email', 'support@shopcalm.in'),
                'phone'        => Setting::get('contact_phone', '+91 99999 99999'),
            ],
            'created_at'                  => $order->created_at ? $order->created_at->format('d M, Y h:i A') : '',
            'created_at_date'             => $order->created_at ? $order->created_at->format('d M, Y') : '',
            'items'                       => $order->items->map(function ($item) {
                $imgPath = $item->product ? ($item->product->main_image ?? $item->product->featured_image ?? null) : null;
                $unitPrice = (float) ($item->unit_price ?? $item->price ?? 0);
                $qty = (int) ($item->quantity ?? 1);
                $totPrice = (float) ($item->total_price ?? $item->subtotal ?? ($unitPrice * $qty));
                $sku = $item->product?->sku ?: ('SC-' . str_pad((string) ($item->product_id ?? $item->id), 5, '0', STR_PAD_LEFT));
                $mrp = (float) ($item->product?->mrp && (float) $item->product?->mrp > 0
                    ? $item->product->mrp
                    : ($item->original_price && (float) $item->original_price > $unitPrice
                        ? $item->original_price
                        : ($unitPrice > 0 ? round($unitPrice * 1.25, 2) : $unitPrice)));
                if ($mrp < $unitPrice) {
                    $mrp = $unitPrice;
                }
                $isReplaceable = (bool) ($item->product?->is_replaceable ?? false);
                $replacementDays = (int) ($item->product?->replacement_window_days ?? 0);

                return [
                    'id'                       => $item->id,
                    'product_id'               => $item->product_id,
                    'product_slug'             => $item->product?->slug,
                    'product_name'             => $item->product_name ?? $item->product?->name ?? 'Product',
                    'sku'                      => $sku,
                    'brand_name'               => $item->product?->brand?->name,
                    'category_name'            => $item->product?->category?->name,
                    'product_image'            => $imgPath ? (str_starts_with($imgPath, 'http') ? $imgPath : asset('storage/' . $imgPath)) : null,
                    'selected_option'          => $item->selected_option,
                    'quantity'                 => $qty,
                    'original_price'           => $mrp,
                    'mrp'                      => $mrp,
                    'offer_discount'           => (float) ($item->offer_discount ?? max(0, $mrp - $unitPrice)),
                    'unit_price'               => $unitPrice,
                    'price'                    => $unitPrice,
                    'total_price'              => $totPrice,
                    'subtotal'                 => $totPrice,
                    'is_replaceable'           => $isReplaceable,
                    'replacement_window_days'  => $replacementDays,
                    'replacement_policy_badge' => $isReplaceable ? "{$replacementDays}D Replacement" : "Non-Returnable",
                    'policy_description'       => $isReplaceable
                        ? "Standard replacement within {$replacementDays} days for verified transit damage or defect."
                        : "Zero return policy. Standard non-returnable non-refundable sale.",
                ];
            }),
        ], 'Order details retrieved.');
    }

    /**
     * Cancellation Breakdown Summary for Customer Modal.
     */
    public function cancellationSummary(Request $request, Order $order): JsonResponse
    {
        $user = $request->user();
        $isPendingUnpaid = in_array($order->status, ['pending', 'failed']) || $order->payment_status === 'failed';

        if (!$isPendingUnpaid && Setting::get('allow_customer_cancellation', '1') != '1') {
            return $this->sendError('Customer order cancellation is currently disabled by store management.', [], 403);
        }

        if ((int)$order->user_id !== (int)$user->id) {
            return $this->sendError('Unauthorized access to this order.', [], 403);
        }

        $summary = $this->orderService->calculateCancellationSummary($order);
        return $this->sendResponse([
            'summary' => $summary,
            'is_cod'  => $order->payment_method === 'cod',
            'is_paid' => $order->payment_status === 'paid',
        ], 'Cancellation summary calculated.');
    }

    /**
     * Customer Cancel Order Endpoint.
     */
    public function cancelOrder(Request $request, Order $order): JsonResponse
    {
        $user = $request->user();
        $isPendingUnpaid = in_array($order->status, ['pending', 'failed']) || $order->payment_status === 'failed';

        if (!$isPendingUnpaid && Setting::get('allow_customer_cancellation', '1') != '1') {
            return $this->sendError('Customer order cancellation is currently disabled by store management.', [], 403);
        }

        if ((int)$order->user_id !== (int)$user->id) {
            return $this->sendError('Unauthorized access to this order.', [], 403);
        }

        $request->validate([
            'cancellation_reason' => 'required|string|max:255',
            'refund_method'       => 'nullable|string|in:original_source,wallet,none',
        ]);

        try {
            $reason = $request->cancellation_reason;
            $refundMethod = $request->input('refund_method', 'original_source');
            $upiId = null;

            if ($order->payment_method === 'cod' && $order->payment_status !== 'paid') {
                $cancellation = $this->orderService->cancelCodOrderFree($order, $reason);
            } else {
                $cancellation = $this->orderService->cancelPrepaidOrder($order, $reason, $refundMethod, $upiId);
            }

            return $this->sendResponse([
                'cancellation_id' => $cancellation->id,
                'order_number'    => $order->order_number,
                'refund_amount'   => (float) $cancellation->refund_amount,
                'refund_status'   => $cancellation->refund_status,
            ], 'Order cancelled successfully.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 422);
        }
    }

    /**
     * Get Store Wallet Balance & Transaction History.
     */
    public function wallet(Request $request): JsonResponse
    {
        $user = $request->user();
        $wallet = $this->walletService->getOrCreateWallet($user);

        $transactions = $wallet->transactions()->latest()->take(20)->get()->map(function ($tx) {
            return [
                'id'          => $tx->id,
                'type'        => $tx->type, // CREDIT or DEBIT
                'amount'      => (float) $tx->amount,
                'action'      => $tx->action,
                'description' => $tx->description,
                'created_at'  => $tx->created_at ? $tx->created_at->format('Y-m-d H:i:s') : null,
            ];
        });

        return $this->sendResponse([
            'balance'            => (float) $wallet->balance,
            'is_active'          => (bool) $wallet->is_active,
            'referral_code'      => $wallet->referral_code,
            'is_referral_active' => \App\Services\WalletService::isReferralProgramActive(),
            'signup_bonus'       => \App\Services\WalletService::getSignupBonus(),
            'first_order_reward' => \App\Services\WalletService::getFirstOrderReward(),
            'transactions'       => $transactions,
        ], 'Store wallet data retrieved.');
    }

    /**
     * Get Saved Customer Shipping Addresses (Cached for high performance).
     */
    public function addresses(Request $request): JsonResponse
    {
        $user = $request->user();
        $cacheKey = "user_{$user->id}_addresses";

        $addresses = Cache::remember($cacheKey, 600, function () use ($user) {
            return Address::where('user_id', $user->id)
                ->latest('id')
                ->get();
        });

        return $this->sendResponse([
            'addresses' => $addresses,
            'limit'     => 3,
            'count'     => $addresses->count(),
        ], 'Saved addresses retrieved.');
    }

    /**
     * Store New Customer Shipping Address.
     */
    public function storeAddress(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$request->has('zip') && $request->filled('pincode')) {
            $request->merge(['zip' => $request->input('pincode')]);
        }

        if (!$request->filled('address')) {
            $parts = array_filter([$request->input('house_no'), $request->input('street')]);
            if (!empty($parts)) {
                $request->merge(['address' => implode(', ', $parts)]);
            }
        }

        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'phone'     => 'required|string|max:20',
            'house_no'  => 'nullable|string|max:255',
            'street'    => 'nullable|string|max:255',
            'address'   => 'required|string|max:500',
            'city'      => 'required|string|max:100',
            'state'     => 'required|string|max:100',
            'zip'       => 'required|string|max:10',
            'country'   => 'nullable|string|max:100',
            'latitude'  => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'landmark'  => 'nullable|string|max:255',
            'type'      => 'nullable|string|max:50',
        ]);

        $validated['country'] = $validated['country'] ?? 'India';

        // 1. Smart Deduplication: Check if identical address already exists (same ZIP and house_no / address)
        $houseNo = trim($validated['house_no'] ?? '');
        $zip = trim($validated['zip'] ?? '');
        $fullAddress = trim($validated['address'] ?? '');

        $duplicateQuery = $user->addresses()->where('zip', $zip);
        if (!empty($houseNo)) {
            $duplicateQuery->where(function ($q) use ($houseNo, $fullAddress) {
                $q->where('house_no', $houseNo)
                  ->orWhere('address', $fullAddress);
            });
        } else {
            $duplicateQuery->where('address', $fullAddress);
        }

        $existingAddress = $duplicateQuery->first();
        if ($existingAddress) {
            $existingAddress->update($validated);
            Cache::forget("user_{$user->id}_addresses");

            return $this->sendResponse([
                'address' => $existingAddress->fresh(),
                'limit'   => 3,
                'count'   => $user->addresses()->count(),
                'is_duplicate_updated' => true,
            ], 'Address already exists. Updated with latest details.', 200);
        }

        // 2. Strict Limit: Max 3 Saved Addresses per customer (Home, Work, Other)
        $currentCount = $user->addresses()->count();
        if ($currentCount >= 3) {
            return $this->sendError(
                'You have reached the maximum limit of 3 saved addresses (Home, Work, Other). Please edit or delete an existing address.',
                [
                    'limit'   => 3,
                    'current' => $currentCount,
                ],
                422
            );
        }

        $address = $user->addresses()->create($validated);
        Cache::forget("user_{$user->id}_addresses");

        return $this->sendResponse([
            'address' => $address,
            'limit'   => 3,
            'count'   => $currentCount + 1,
        ], 'Delivery address saved successfully.', 201);
    }

    /**
     * Update Existing Shipping Address.
     */
    public function updateAddress(Request $request, Address $address): JsonResponse
    {
        $user = $request->user();

        if ((int) $address->user_id !== (int) $user->id) {
            return $this->sendError('Unauthorized access to this address.', [], 403);
        }

        if (!$request->has('zip') && $request->filled('pincode')) {
            $request->merge(['zip' => $request->input('pincode')]);
        }

        if (!$request->filled('address') && ($request->filled('house_no') || $request->filled('street'))) {
            $h = $request->input('house_no', $address->house_no);
            $s = $request->input('street', $address->street);
            $parts = array_filter([$h, $s]);
            if (!empty($parts)) {
                $request->merge(['address' => implode(', ', $parts)]);
            }
        }

        $validated = $request->validate([
            'name'      => 'sometimes|required|string|max:255',
            'phone'     => 'sometimes|required|string|max:20',
            'house_no'  => 'nullable|string|max:255',
            'street'    => 'nullable|string|max:255',
            'address'   => 'sometimes|required|string|max:500',
            'city'      => 'sometimes|required|string|max:100',
            'state'     => 'sometimes|required|string|max:100',
            'zip'       => 'sometimes|required|string|max:10',
            'country'   => 'nullable|string|max:100',
            'latitude'  => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'landmark'  => 'nullable|string|max:255',
            'type'      => 'nullable|string|max:50',
        ]);

        $address->update($validated);
        Cache::forget("user_{$user->id}_addresses");

        return $this->sendResponse([
            'address' => $address,
        ], 'Delivery address updated successfully.');
    }

    /**
     * Delete a Shipping Address.
     */
    public function deleteAddress(Request $request, Address $address): JsonResponse
    {
        $user = $request->user();

        if ((int) $address->user_id !== (int) $user->id) {
            return $this->sendError('Unauthorized access to this address.', [], 403);
        }

        $address->delete();
        Cache::forget("user_{$user->id}_addresses");

        return $this->sendResponse([], 'Delivery address deleted successfully.');
    }

    /**
     * Update Customer Profile (Name, Email, Avatar, or Password).
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($request->filled('current_password') && $request->filled('password')) {
            return $this->changePassword($request);
        }

        $validated = $request->validate([
            'name'               => 'sometimes|required|string|max:255',
            'email'              => 'nullable|email|max:255|unique:users,email,' . $user->id,
            'avatar'             => 'nullable|image|max:2048',
            'gender'             => 'nullable|string|in:Male,Female',
            'date_of_birth'      => 'nullable|date|before:today',
            'preferred_language' => 'nullable|string|max:50',
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->avatar);
            }
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $userFields = collect($validated)->only(['name', 'email', 'avatar'])->filter(fn($v) => !is_null($v))->toArray();
        if (!empty($userFields)) {
            $user->update($userFields);
        }

        $profileFields = collect($validated)->only(['gender', 'date_of_birth', 'preferred_language'])->toArray();
        if (!empty($profileFields)) {
            $user->profile()->updateOrCreate(
                ['user_id' => $user->id],
                $profileFields
            );
        }

        $user->load('profile');
        $profile = $user->profile;

        return $this->sendResponse([
            'user' => [
                'id'                 => $user->id,
                'name'               => $user->name,
                'email'              => $user->email,
                'mobile_number'      => $user->mobile_number,
                'gender'             => $profile?->gender,
                'date_of_birth'      => $profile?->date_of_birth ? $profile->date_of_birth->format('Y-m-d') : null,
                'preferred_language' => $profile?->preferred_language,
                'avatar'             => $user->avatar ? asset('storage/' . $user->avatar) : null,
            ],
        ], 'Profile updated successfully.');
    }

    /**
     * Change Customer Account Password.
     */
    public function changePassword(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'current_password' => 'required|string',
            'password'         => 'required|string|min:8|confirmed',
        ]);

        if (!\Illuminate\Support\Facades\Hash::check($request->current_password, $user->password)) {
            return $this->sendError('The current password you entered is incorrect.', [
                'current_password' => ['The current password is incorrect.'],
            ], 422);
        }

        $user->update([
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
        ]);

        return $this->sendResponse([], 'Password updated successfully.');
    }

    /**
     * Customer Raised Support Tickets / Enquiries
     */
    public function tickets(Request $request): JsonResponse
    {
        $user = $request->user();
        $email = $user?->email ?? $request->query('email');
        $mobile = $user?->phone ?? $user?->mobile_number ?? $request->query('mobile');

        if (empty($email) && empty($mobile)) {
            return $this->sendResponse([], 'No tickets found');
        }

        // Clean up mobile (remove country code or formatting for robust matching)
        $cleanMobile = $mobile ? preg_replace('/\D/', '', $mobile) : null;
        if ($cleanMobile && strlen($cleanMobile) > 10) {
            $cleanMobile = substr($cleanMobile, -10);
        }

        $tickets = \App\Models\ContactEnquiry::query()
            ->where(function ($q) use ($email, $mobile, $cleanMobile) {
                if (!empty($email)) {
                    $q->where('email', $email);
                }
                if (!empty($mobile)) {
                    $q->orWhere('mobile', $mobile);
                }
                if (!empty($cleanMobile)) {
                    $q->orWhere('mobile', 'LIKE', '%' . $cleanMobile . '%');
                }
            })
            ->latest()
            ->get()
            ->map(function ($ticket) {
                $statusKey = $ticket->status ?? 'unread';
                if ($ticket->is_read && $statusKey === 'unread') {
                    $statusKey = 'in_progress';
                }

                $statusLabel = match ($statusKey) {
                    'in_progress' => 'In Progress',
                    'resolved', 'closed' => 'Resolved',
                    default => 'Open / Under Review',
                };

                return [
                    'id'            => $ticket->id,
                    'ticket_number' => '#TKT-' . str_pad($ticket->id, 5, '0', STR_PAD_LEFT),
                    'name'          => $ticket->name,
                    'email'         => $ticket->email,
                    'mobile'        => $ticket->mobile,
                    'subject'       => $ticket->subject,
                    'message'       => $ticket->message,
                    'status'        => $statusKey,
                    'status_label'  => $statusLabel,
                    'reply_notes'   => $ticket->reply_notes,
                    'resolved_at'   => $ticket->resolved_at?->format('d M Y, h:i A'),
                    'created_at'    => $ticket->created_at?->format('d M Y, h:i A'),
                    'created_ago'   => $ticket->created_at?->diffForHumans(),
                ];
            });

        return $this->sendResponse($tickets, 'Tickets retrieved successfully.');
    }

    /**
     * Mobile REST: Customer Delivery Decision / Exception Resolution (2-Way Sync).
     */
    public function deliveryDecision(Request $request, Order $order): JsonResponse
    {
        $user = $request->user();
        if (!$user || (int)$order->user_id !== (int)$user->id) {
            return $this->sendError('Unauthorized access to this order.', [], 403);
        }

        $validated = $request->validate([
            'decision'      => 'required|string|in:reschedule_tomorrow,leave_security_neighbor,reattempt_today',
            'delivery_slot' => 'nullable|string|max:100',
            'instructions'  => 'nullable|string|max:300',
        ]);

        $decision = $validated['decision'];
        $fulfillment = $order->fulfillment;
        $resolutionNote = '';

        if ($decision === 'reschedule_tomorrow') {
            $slot = $validated['delivery_slot'] ?? 'Tomorrow Morning (8:00 AM - 12:00 PM)';
            $resolutionNote = "Rescheduled by Customer to {$slot}";

            if ($fulfillment) {
                $fulfillment->update([
                    'delivery_slot'              => $slot,
                    'delivery_issue_resolved_at' => now(),
                    'delivery_issue_resolution'  => $resolutionNote,
                    'notes'                      => trim(($fulfillment->notes ?? '') . " | [Customer Rescheduled: {$slot}]"),
                ]);
            }
            $order->update(['delivery_slot' => $slot]);
        } elseif ($decision === 'leave_security_neighbor') {
            $customNote = $validated['instructions'] ?: 'Please leave package with building security or next-door neighbor';
            $resolutionNote = "Customer Handover Instruction: {$customNote}";

            if ($fulfillment) {
                $fulfillment->update([
                    'delivery_issue_resolved_at' => now(),
                    'delivery_issue_resolution'  => $resolutionNote,
                    'notes'                      => trim(($fulfillment->notes ?? '') . " | [Customer: {$customNote}]"),
                ]);
            }
        } elseif ($decision === 'reattempt_today') {
            $resolutionNote = 'Customer is at home now — ready for doorstep handover';

            if ($fulfillment) {
                $fulfillment->update([
                    'delivery_issue_resolved_at' => now(),
                    'delivery_issue_resolution'  => $resolutionNote,
                    'notes'                      => trim(($fulfillment->notes ?? '') . ' | [Customer: Home now, ready for re-attempt]'),
                ]);
            }
        }

        $order->statusHistories()->create([
            'previous_status' => $order->status,
            'current_status'  => $order->status,
            'notes'           => "Customer Delivery Action: {$resolutionNote}",
        ]);

        return $this->sendResponse([
            'resolution'    => $resolutionNote,
            'delivery_slot' => $order->fresh()->delivery_slot,
        ], 'Your delivery decision has been synchronized with the dispatch hub and assigned delivery partner.');
    }

    /**
     * Mobile REST: 5-Star Delivery Experience Feedback.
     */
    public function orderFeedback(Request $request, Order $order): JsonResponse
    {
        $user = $request->user();
        if (!$user || (int)$order->user_id !== (int)$user->id) {
            return $this->sendError('Unauthorized access.', [], 403);
        }

        if ($order->status !== 'delivered') {
            return $this->sendError('Feedback can only be provided for delivered orders.', [], 422);
        }

        $validated = $request->validate([
            'delivery_rating' => ['required', 'integer', 'min:1', 'max:5'],
            'product_rating'  => ['nullable', 'integer', 'min:1', 'max:5'],
            'tags'            => ['nullable', 'array'],
            'tags.*'          => ['string', 'max:50'],
            'comment'         => ['nullable', 'string', 'max:1000'],
        ]);

        $riderId = $order->fulfillment?->rider_id;

        $feedback = OrderFeedback::updateOrCreate(
            ['order_id' => $order->id],
            [
                'user_id'         => $user->id,
                'rider_id'        => $riderId,
                'delivery_rating' => $validated['delivery_rating'],
                'product_rating'  => $validated['product_rating'] ?? $validated['delivery_rating'],
                'tags'            => $validated['tags'] ?? [],
                'comment'         => $validated['comment'] ?? null,
            ]
        );

        return $this->sendResponse([
            'feedback' => $feedback,
        ], 'Thank you for rating your delivery partner!');
    }

    /**
     * Mobile REST: 1-Tap "Buy Again" / Re-Order All Items.
     */
    public function reorder(Request $request, Order $order): JsonResponse
    {
        $user = $request->user();
        if (!$user || (int)$order->user_id !== (int)$user->id) {
            return $this->sendError('Unauthorized access.', [], 403);
        }

        $cartService = app(CartService::class);
        $order->loadMissing('items.product');

        $addedCount = 0;
        $failedCount = 0;

        foreach ($order->items as $item) {
            if ($item->product && $item->product->status === 'Active') {
                $qty = max(1, (int)$item->quantity);
                $opt = $item->selected_option;
                $res = $cartService->addProduct((int)$item->product_id, $qty, $opt);
                if (is_array($res) && ($res['success'] ?? false)) {
                    $addedCount++;
                } else {
                    $failedCount++;
                }
            } else {
                $failedCount++;
            }
        }

        if ($addedCount === 0) {
            return $this->sendError('Could not re-order items. Products may currently be out of stock or inactive.', [], 422);
        }

        return $this->sendResponse([
            'added_count'  => $addedCount,
            'failed_count' => $failedCount,
        ], "{$addedCount} " . ($addedCount === 1 ? 'item' : 'items') . " added to your cart!");
    }

    /**
     * Mobile REST: 15-Second Background Status Poller.
     */
    public function statusPoll(Request $request, Order $order): JsonResponse
    {
        $user = $request->user();
        if (!$user || (int)$order->user_id !== (int)$user->id) {
            return $this->sendError('Unauthorized.', [], 403);
        }

        $order->loadMissing(['fulfillment', 'feedback']);
        $fulfillment = $order->fulfillment;

        $podPhoto = null;
        if ($fulfillment && preg_match('/\[POD_PHOTO:(.*?)\]/', $fulfillment->notes ?? '', $mPhoto)) {
            $podPhoto = asset('storage/' . $mPhoto[1]);
        }

        return $this->sendResponse([
            'order_id'            => $order->id,
            'order_number'        => $order->order_number,
            'status'              => $order->status,
            'payment_status'      => $order->payment_status,
            'is_out_for_delivery' => $order->status === 'out for delivery' || $order->status === 'out_for_delivery',
            'is_delivered'        => $order->status === 'delivered',
            'is_cancelled'        => $order->status === 'cancelled',
            'has_active_issue'    => (bool)($fulfillment?->hasActiveDeliveryIssue()),
            'issue_reason'        => $fulfillment?->delivery_issue,
            'delivery_slot'       => $fulfillment?->delivery_slot ?: ($order->delivery_slot ?: 'Fast Doorstep Delivery'),
            'rider_name'          => $order->rider_name,
            'rider_phone'         => $order->rider_phone,
            'pod_photo_url'       => $podPhoto,
            'feedback_submitted'  => (bool)$order->feedback,
        ], 'Status retrieved.');
    }

    /**
     * Get Standard Valid Replacement Reasons & Eligibility for an item.
     */
    public function getReplacementConfig(Request $request, Order $order, OrderItem $orderItem): JsonResponse
    {
        $user = $request->user();
        if ((int) $order->user_id !== (int) $user->id || (int) $orderItem->order_id !== (int) $order->id) {
            return $this->sendError('Unauthorized.', [], 403);
        }

        $product = $orderItem->product;
        $isReplaceable = (bool) ($product?->is_replaceable ?? false);
        $windowDays = $isReplaceable ? (int) ($product->replacement_window_days ?: 7) : 0;

        $deliveredAt = $order->fulfillment?->delivered_at ?? $order->updated_at;
        $expiryDate = $deliveredAt ? $deliveredAt->copy()->addDays($windowDays) : null;
        $isWithinWindow = $expiryDate ? now()->lte($expiryDate) : false;

        $existingReplacement = OrderReplacement::where('order_item_id', $orderItem->id)
            ->whereNotIn('status', ['cancelled', 'rejected'])
            ->first();

        $isEligible = ($order->status === 'delivered') && $isReplaceable && $isWithinWindow && !$existingReplacement;

        $reasonsFormatted = collect(OrderReplacement::VALID_REASONS)->map(function ($config, $key) {
            return [
                'key'             => $key,
                'label'           => $config['label'],
                'photo_mandatory' => (bool) $config['photo_mandatory'],
                'description'     => $config['description'],
            ];
        })->values();

        return $this->sendResponse([
            'is_eligible'          => $isEligible,
            'is_delivered'         => $order->status === 'delivered',
            'is_replaceable'       => $isReplaceable,
            'window_days'          => $windowDays,
            'window_expires_at'    => $expiryDate ? $expiryDate->format('Y-m-d H:i:s') : null,
            'is_within_window'     => $isWithinWindow,
            'has_active_request'   => (bool) $existingReplacement,
            'active_replacement'   => $existingReplacement ? [
                'replacement_number' => $existingReplacement->replacement_number,
                'status'             => $existingReplacement->status,
                'pickup_date'        => $existingReplacement->pickup_date?->format('Y-m-d'),
                'rider_name'         => $existingReplacement->rider_name,
            ] : null,
            'reasons'              => $reasonsFormatted,
        ], 'Replacement configuration loaded.');
    }

    /**
     * Submit Item Replacement Request.
     */
    public function requestReplacement(Request $request, Order $order, OrderItem $orderItem): JsonResponse
    {
        $user = $request->user();
        if ((int) $order->user_id !== (int) $user->id || (int) $orderItem->order_id !== (int) $order->id) {
            return $this->sendError('Unauthorized access to this order item.', [], 403);
        }

        if ($order->status !== 'delivered') {
            return $this->sendError('Replacement can only be requested for delivered orders.', [], 422);
        }

        $product = $orderItem->product;
        if (!$product || !$product->is_replaceable) {
            return $this->sendError('This product is non-returnable and non-replaceable as per store policy.', [], 422);
        }

        $windowDays = (int) ($product->replacement_window_days ?: 7);
        $deliveredAt = $order->fulfillment?->delivered_at ?? $order->updated_at;
        $expiryDate = $deliveredAt ? $deliveredAt->copy()->addDays($windowDays) : null;
        if ($expiryDate && now()->gt($expiryDate)) {
            return $this->sendError("The {$windowDays}-day replacement window for this item has expired on " . $expiryDate->format('d M Y') . '.', [], 422);
        }

        $alreadySubmitted = OrderReplacement::where('order_item_id', $orderItem->id)
            ->whereNotIn('status', ['cancelled', 'rejected'])
            ->exists();
        if ($alreadySubmitted) {
            return $this->sendError('A replacement request is already in progress for this item.', [], 422);
        }

        $validKeys = array_keys(OrderReplacement::VALID_REASONS);
        $request->validate([
            'reason'          => ['required', 'string', 'in:' . implode(',', $validKeys)],
            'customer_notes'  => ['nullable', 'string', 'max:1000'],
            'proof_images'    => ['nullable', 'array', 'max:4'],
            'proof_images.*'  => ['nullable', 'image', 'max:5120'], // Max 5MB per image
        ]);

        $reasonKey = $request->reason;
        $reasonConfig = OrderReplacement::VALID_REASONS[$reasonKey];

        // Enforce: Photo proof is mandatory ONLY if physical_damage
        $uploadedImages = [];
        if ($request->hasFile('proof_images')) {
            foreach ($request->file('proof_images') as $file) {
                if ($file->isValid()) {
                    $uploadedImages[] = $file->store('replacements', 'public');
                }
            }
        }

        if ($reasonConfig['photo_mandatory'] && empty($uploadedImages)) {
            return $this->sendError('Clear photo proof is required for physical damage or broken seal claims.', [], 422);
        }

        $replacement = OrderReplacement::create([
            'replacement_number'  => OrderReplacement::generateReplacementNumber(),
            'order_id'            => $order->id,
            'order_item_id'       => $orderItem->id,
            'product_id'          => $product->id,
            'user_id'             => $user->id,
            'reason'              => $reasonKey,
            'reason_label'        => $reasonConfig['label'],
            'customer_notes'      => $request->customer_notes,
            'proof_images'        => !empty($uploadedImages) ? $uploadedImages : null,
            'status'              => 'requested',
        ]);

        return $this->sendResponse([
            'replacement_id'     => $replacement->id,
            'replacement_number' => $replacement->replacement_number,
            'status'             => $replacement->status,
            'status_label'       => 'Review Pending by Order Manager',
            'created_at'         => $replacement->created_at->format('Y-m-d H:i:s'),
        ], 'Your replacement request has been submitted successfully. Order Manager will review and assign a delivery partner for doorstep pickup.');
    }

    /**
     * Customer Replacements History List.
     */
    public function replacements(Request $request): JsonResponse
    {
        $user = $request->user();

        $replacements = OrderReplacement::with(['order', 'orderItem', 'product', 'rider'])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(15);

        $formatted = $replacements->map(function ($rep) {
            $proofUrls = [];
            if (!empty($rep->proof_images)) {
                foreach ($rep->proof_images as $path) {
                    $proofUrls[] = asset('storage/' . $path);
                }
            }

            return [
                'id'                 => $rep->id,
                'replacement_number' => $rep->replacement_number,
                'order_number'       => $rep->order?->order_number,
                'item_name'          => $rep->orderItem?->product_name ?? ($rep->product?->name ?? 'Product Item'),
                'item_image'         => $rep->product?->main_image ? asset('storage/' . $rep->product->main_image) : null,
                'reason_label'       => $rep->reason_label,
                'status'             => $rep->status,
                'status_badge'       => match ($rep->status) {
                    'requested'       => 'Pending Review',
                    'approved'        => 'Approved',
                    'pickup_assigned' => 'Pickup Assigned',
                    'picked_up'       => 'Item Picked Up',
                    'completed'       => 'Exchange Completed',
                    'rejected'        => 'Rejected',
                    default           => ucfirst($rep->status),
                },
                'pickup_date'        => $rep->pickup_date ? $rep->pickup_date->format('d M Y') : null,
                'rider_name'         => $rep->rider_name,
                'rider_phone'        => $rep->rider_phone,
                'rejection_reason'   => $rep->rejection_reason,
                'proof_photos'       => $proofUrls,
                'created_at'         => $rep->created_at->format('d M Y, h:i A'),
            ];
        });

        return $this->sendResponse([
            'replacements' => $formatted,
            'current_page' => $replacements->currentPage(),
            'total'        => $replacements->total(),
        ], 'Replacements retrieved.');
    }

    /**
     * Single Replacement Detail.
     */
    public function replacementDetails(Request $request, OrderReplacement $replacement): JsonResponse
    {
        $user = $request->user();
        if ((int) $replacement->user_id !== (int) $user->id) {
            return $this->sendError('Unauthorized.', [], 403);
        }

        $replacement->loadMissing(['order', 'orderItem', 'product', 'rider']);

        $proofUrls = [];
        if (!empty($replacement->proof_images)) {
            foreach ($replacement->proof_images as $path) {
                $proofUrls[] = asset('storage/' . $path);
            }
        }

        return $this->sendResponse([
            'id'                  => $replacement->id,
            'replacement_number'  => $replacement->replacement_number,
            'order_number'        => $replacement->order?->order_number,
            'order_id'            => $replacement->order_id,
            'product_name'        => $replacement->orderItem?->product_name ?? ($replacement->product?->name ?? 'Item'),
            'selected_option'     => $replacement->orderItem?->selected_option,
            'product_image'       => $replacement->product?->main_image ? asset('storage/' . $replacement->product->main_image) : null,
            'reason'              => $replacement->reason,
            'reason_label'        => $replacement->reason_label,
            'customer_notes'      => $replacement->customer_notes,
            'status'              => $replacement->status,
            'pickup_date'         => $replacement->pickup_date ? $replacement->pickup_date->format('d M Y') : null,
            'rider_name'          => $replacement->rider_name,
            'rider_phone'         => $replacement->rider_phone,
            'order_manager_notes' => $replacement->order_manager_notes,
            'rejection_reason'    => $replacement->rejection_reason,
            'proof_photos'        => $proofUrls,
            'created_at'          => $replacement->created_at->format('d M Y, h:i A'),
            'timeline'            => [
                ['stage' => 'Requested', 'time' => $replacement->created_at->format('d M Y, h:i A'), 'completed' => true],
                ['stage' => 'Approved', 'time' => $replacement->reviewed_at?->format('d M Y, h:i A'), 'completed' => in_array($replacement->status, ['approved', 'pickup_assigned', 'picked_up', 'completed'])],
                ['stage' => 'Pickup Assigned', 'time' => $replacement->pickup_date ? ('Scheduled: ' . $replacement->pickup_date->format('d M Y')) : null, 'completed' => in_array($replacement->status, ['pickup_assigned', 'picked_up', 'completed'])],
                ['stage' => 'Picked Up', 'time' => $replacement->picked_up_at?->format('d M Y, h:i A'), 'completed' => in_array($replacement->status, ['picked_up', 'completed'])],
                ['stage' => 'Exchange Completed', 'time' => $replacement->completed_at?->format('d M Y, h:i A'), 'completed' => $replacement->status === 'completed'],
            ],
        ], 'Replacement details retrieved.');
    }
}


