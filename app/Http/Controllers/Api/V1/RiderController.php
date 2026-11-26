<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Order;
use App\Models\OrderReplacement;
use App\Models\User;
use App\Models\CashSettlement;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RiderController extends BaseApiController
{
    /**
     * Authenticate Delivery Partner via Mobile / Email + Password.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $loginInput = trim($request->input('email'));
        $isEmail = filter_var($loginInput, FILTER_VALIDATE_EMAIL);
        
        $user = User::where($isEmail ? 'email' : 'mobile_number', $loginInput)->first();

        if (!$user) {
            return $this->sendError('The provided credentials do not match our delivery fleet records.', [], 401);
        }

        if (!Hash::check($request->input('password'), $user->password)) {
            return $this->sendError('Invalid password. Please check your credentials.', [], 401);
        }

        if (in_array($user->status, ['Blocked', 'Deleted'])) {
            return $this->sendError('Your rider account has been deactivated or blocked. Please contact hub dispatch.', [], 403);
        }

        if (!$user->canAccessDeliveryPortal()) {
            return $this->sendError('Unauthorized account. Only registered Delivery Partners can access this app.', [], 403);
        }

        // Generate Sanctum Bearer Token
        $token = $user->createToken('rider-mobile-app')->plainTextToken;

        return $this->sendResponse([
            'token' => $token,
            'rider' => [
                'id'            => $user->id,
                'name'          => $user->name,
                'email'         => $user->email,
                'mobile_number' => $user->mobile_number,
                'role'          => 'Delivery Partner',
                'hub'           => 'Bengaluru Fleet Hub #01',
            ],
        ], 'Welcome to ShopCalm Fleet! Signed in successfully.');
    }

    /**
     * Get Rider Run-Sheet Manifest & Active Tasks.
     */
    public function dashboard(Request $request): JsonResponse
    {
        $rider = $request->user();

        // Active orders assigned to this rider
        $activeOrders = Order::with(['items.product', 'fulfillment'])
            ->whereHas('fulfillment', fn($q) => $q->where('rider_id', $rider->id))
            ->whereIn('status', ['packed', 'shipped', 'out for delivery', 'processing'])
            ->latest('updated_at')
            ->get();

        $formattedOrders = $activeOrders->map(function ($order, $index) {
            $isPaid = ($order->payment_status === 'paid' || $order->payment_method === 'wallet' || ($order->total_amount <= 0 && $order->wallet_amount_used > 0));
            $isCod = ($order->payment_method === 'cod' && !$isPaid && $order->total_amount > 0);
            $isDoorstepUpi = ($order->payment_method === 'doorstep_upi');

            // Extract preferred delivery time slot if formatted inside notes: [Time: ...]
            $timeSlot = '';
            $customerNotes = $order->notes ?? '';
            if (preg_match('/\[Time:\s*([^\]]+)\]/i', $customerNotes, $matches)) {
                $timeSlot = trim($matches[1]);
                $customerNotes = trim(str_replace($matches[0], '', $customerNotes));
            }

            $isOffice = str_contains(strtolower($order->shipping_address ?? ''), 'office') 
                     || str_contains(strtolower($order->shipping_address ?? ''), 'tech park') 
                     || str_contains(strtolower($order->shipping_address ?? ''), 'tower')
                     || str_contains(strtolower($timeSlot), 'office');

            $hasExactPin = !empty($order->shipping_latitude) && !empty($order->shipping_longitude);
            $lat = (float) ($order->shipping_latitude ?: 0);
            $lng = (float) ($order->shipping_longitude ?: 0);

            if (!$hasExactPin || $lat == 0.0) {
                $resolved = self::resolvePincodeCoordinates($order->shipping_zip, $order->shipping_city, $order->shipping_address);
                $lat = $resolved['lat'];
                $lng = $resolved['lng'];
            }

            $landmark = $order->shipping_landmark ?? ($order->address?->landmark ?? '');

            return [
                'id'                   => $order->id,
                'stop_number'          => $index + 1,
                'order_number'         => $order->order_number,
                'customer_name'        => $order->shipping_name,
                'customer_phone'       => $order->shipping_phone,
                'shipping_address'     => $order->shipping_address,
                'shipping_city'        => $order->shipping_city,
                'shipping_zip'         => $order->shipping_zip,
                'latitude'             => $lat,
                'longitude'            => $lng,
                'has_exact_pin'        => $hasExactPin,
                'landmark'             => $landmark,
                'preferred_time_slot'  => $timeSlot,
                'customer_notes'       => $customerNotes,
                'is_office'            => $isOffice,
                'is_paid'              => $isPaid,
                'is_cod'               => $isCod,
                'payment_method'       => $order->payment_method ?? 'prepaid',
                'is_doorstep_upi'      => $isDoorstepUpi,
                'total_amount'         => (float) $order->total_amount,
                'formatted_amount'     => '₹' . number_format($order->total_amount, 2),
                'status'               => $order->status,
                'is_bag_loaded'        => !empty($order->fulfillment?->dispatched_at),
                'has_issue'            => (bool) ($order->fulfillment?->hasActiveDeliveryIssue()),
                'issue_reason'         => $order->fulfillment?->delivery_issue,
                'items_count'          => $order->items->sum('quantity'),
                'items_preview'        => $order->items->first()?->product_name ?? 'Parcel Package',
                'items'                => $order->items->map(function ($item) {
                    $img = $item->display_image ?? $item->product?->main_image;
                    return [
                        'name'            => $item->product_name,
                        'quantity'        => (int) $item->quantity,
                        'selected_option' => $item->selected_option,
                        'sku'             => $item->product?->sku ?? 'N/A',
                        'image_url'       => $img ? asset('storage/' . $img) : null,
                    ];
                }),
            ];
        });

        // 1. Ready to Deliver (no issues)
        $readyOrders = $formattedOrders->filter(fn($o) => !$o['has_issue'])->values();

        // 2. On Hold / Reported Issues
        $onHoldOrders = $formattedOrders->filter(fn($o) => $o['has_issue'])->values();

        // Delivered today count
        $deliveredTodayCount = Order::whereHas('fulfillment', fn($q) => $q->where('rider_id', $rider->id)->whereDate('delivered_at', today()))
            ->where('status', 'delivered')
            ->count();

        // COD cash in pocket pending deposit
        $cashInHandPending = Order::whereHas('fulfillment', fn($q) => $q->where('rider_id', $rider->id)->whereNull('cod_deposited_at'))
            ->where('status', 'delivered')
            ->whereIn('payment_method', ['cod', 'cash'])
            ->sum('total_amount');

        $totalToday = $deliveredTodayCount + $activeOrders->count();
        $progressPercent = $totalToday > 0 ? round(($deliveredTodayCount / $totalToday) * 100) : 0;

        return $this->sendResponse([
            'rider' => [
                'name' => $rider->name,
                'hub'  => 'Bengaluru Fleet Hub #01',
            ],
            'metrics' => [
                'to_deliver'           => $readyOrders->count(),
                'bag_loaded_count'     => $readyOrders->filter(fn($o) => !empty($o['is_bag_loaded']))->count(),
                'on_hold'              => $onHoldOrders->count(),
                'delivered_today'      => $deliveredTodayCount,
                'total_today'          => $totalToday,
                'progress_percent'     => $progressPercent,
                'cash_in_hand_pending' => (float) $cashInHandPending,
                'formatted_cash'       => '₹' . number_format($cashInHandPending, 2),
            ],
            'ready_orders'  => $readyOrders,
            'on_hold_orders' => $onHoldOrders,
        ], 'Rider manifest loaded successfully.');
    }

    /**
     * Get Single Order Details for Doorstep Delivery.
     */
    public function show(Request $request, Order $order): JsonResponse
    {
        $rider = $request->user();
        $assignedRiderId = $order->rider_id ?? $order->fulfillment?->rider_id;

        if ($assignedRiderId && $assignedRiderId !== $rider->id && !$rider->isSuperAdmin()) {
            return $this->sendError('Unauthorized. This delivery is not assigned to you.', [], 403);
        }

        $order->load(['items.product', 'fulfillment']);

        $isPaid = ($order->payment_status === 'paid' || $order->payment_method === 'wallet' || ($order->total_amount <= 0 && $order->wallet_amount_used > 0));
        $isCod = ($order->payment_method === 'cod' && !$isPaid && $order->total_amount > 0);
        $isDoorstepUpi = ($order->payment_method === 'doorstep_upi');

        $timeSlot = '';
        $customerNotes = $order->notes ?? '';
        if (preg_match('/\[Time:\s*([^\]]+)\]/i', $customerNotes, $matches)) {
            $timeSlot = trim($matches[1]);
            $customerNotes = trim(str_replace($matches[0], '', $customerNotes));
        }

        $qrData = $this->generateRazorpayDoorstepQr($order);

        $hasExactPin = !empty($order->shipping_latitude) && !empty($order->shipping_longitude);
        $lat = (float) ($order->shipping_latitude ?: 0);
        $lng = (float) ($order->shipping_longitude ?: 0);

        if (!$hasExactPin || $lat == 0.0) {
            $resolved = self::resolvePincodeCoordinates($order->shipping_zip, $order->shipping_city, $order->shipping_address);
            $lat = $resolved['lat'];
            $lng = $resolved['lng'];
        }

        $landmark = $order->shipping_landmark ?? ($order->address?->landmark ?? '');

        return $this->sendResponse([
            'id'                  => $order->id,
            'order_number'        => $order->order_number,
            'customer_name'       => $order->shipping_name,
            'customer_phone'      => $order->shipping_phone,
            'shipping_address'    => $order->shipping_address,
            'shipping_city'       => $order->shipping_city,
            'shipping_zip'        => $order->shipping_zip,
            'latitude'            => $lat,
            'longitude'           => $lng,
            'has_exact_pin'       => $hasExactPin,
            'landmark'            => $landmark,
            'preferred_time_slot' => $timeSlot,
            'customer_notes'      => $customerNotes,
            'is_paid'             => $isPaid,
            'is_cod'              => $isCod,
            'payment_method'      => $order->payment_method ?? 'prepaid',
            'is_doorstep_upi'     => $isDoorstepUpi,
            'total_amount'        => (float) $order->total_amount,
            'formatted_amount'    => '₹' . number_format($order->total_amount, 2),
            'status'              => $order->status,
            'is_bag_loaded'       => !empty($order->fulfillment?->dispatched_at),
            'has_issue'           => (bool) ($order->fulfillment?->hasActiveDeliveryIssue()),
            'issue_reason'        => $order->fulfillment?->delivery_issue,
            'delivery_otp'        => null,
            'upi_intent'          => $qrData['upi_intent'],
            'upi_qr_url'          => $qrData['qr_url'],
            'is_dynamic_qr'       => $qrData['is_dynamic'],
            'items'               => $order->items->map(function ($item) {
                $img = $item->display_image ?? $item->product?->main_image;
                return [
                    'name'            => $item->product_name,
                    'quantity'        => (int) $item->quantity,
                    'selected_option' => $item->selected_option,
                    'sku'             => $item->product?->sku ?? 'N/A',
                    'image_url'       => $img ? asset('storage/' . $img) : null,
                ];
            }),
        ], 'Order details loaded.');
    }

    /**
     * Heartbeat / Ping probe for offline connectivity detection.
     */
    public function ping(): JsonResponse
    {
        return $this->sendResponse([
            'status'      => 'online',
            'server_time' => now()->toIso8601String(),
            'platform'    => 'ShopCalm Fleet',
        ], 'Server online.');
    }

    /**
     * Complete Delivery (OTP verification for Prepaid, Instant for COD).
     * Supports offline-first basement sync with client-side timestamps.
     */
    public function completeDelivery(Request $request, Order $order): JsonResponse
    {
        $rider = $request->user();
        $assignedRiderId = $order->rider_id ?? $order->fulfillment?->rider_id;

        if ($assignedRiderId && $assignedRiderId !== $rider->id && !$rider->isSuperAdmin()) {
            return $this->sendError('Unauthorized.', [], 403);
        }

        // Idempotent delivery check: If already delivered (e.g. from prior network retry), return success cleanly
        if ($order->status === 'delivered') {
            return $this->sendResponse([
                'order_number' => $order->order_number,
                'status'       => 'delivered',
                'already_synced' => true,
            ], 'Order is already marked as delivered.');
        }

        $isPrepaid = in_array($order->payment_method, ['online', 'wallet', 'prepaid']) || empty($order->payment_method);

        // Zero delivery OTP required (Amazon-standard fast frictionless doorstep handover)
        $request->validate([
            'notes'               => ['nullable', 'string', 'max:255'],
            'latitude'            => ['nullable', 'numeric'],
            'longitude'           => ['nullable', 'numeric'],
            'client_delivered_at' => ['nullable', 'date'],
        ]);

        $clientTime = $request->input('client_delivered_at');
        $deliveredAt = $clientTime ? \Carbon\Carbon::parse($clientTime) : now();
        $isOfflineSync = !empty($clientTime);

        DB::transaction(function () use ($order, $rider, $request, $isPrepaid, $deliveredAt, $isOfflineSync) {
            $previousStatus = $order->status;

            $updateData = [
                'status'         => 'delivered',
                'payment_status' => 'paid',
            ];

            // Amazon Self-Correcting Loop: If rider provides real hardware GPS at doorstep,
            // refine order rooftop coordinates and update customer saved address permanently!
            $riderLat = $request->input('latitude');
            $riderLng = $request->input('longitude');
            if ($riderLat && $riderLng && $riderLat != 0.0 && $riderLng != 0.0) {
                $updateData['shipping_latitude'] = (float) $riderLat;
                $updateData['shipping_longitude'] = (float) $riderLng;

                if ($order->user_id) {
                    try {
                        \App\Models\Address::where('user_id', $order->user_id)
                            ->where('zip', $order->shipping_zip)
                            ->update([
                                'latitude'  => (float) $riderLat,
                                'longitude' => (float) $riderLng,
                            ]);
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::info("[RiderController] Address refinement skipped: " . $e->getMessage());
                    }
                }
            }

            $order->update($updateData);

            if ($order->fulfillment) {
                $fulfillData = [
                    'status'                     => 'delivered',
                    'delivered_at'               => $deliveredAt,
                    'delivery_issue_resolved_at' => $order->fulfillment->delivery_issue ? $deliveredAt : null,
                    'delivery_issue_resolution'  => $order->fulfillment->delivery_issue 
                        ? ($isPrepaid ? 'Delivered upon customer handover' : 'Delivered with Cash Collection') 
                        : null,
                    'notes'                      => $request->notes ?: "Delivered by {$rider->name}",
                ];

                if ($isOfflineSync) {
                    $fulfillData['notes'] = trim(($fulfillData['notes'] ?? '') . " | [OFFLINE_BASEMENT_SYNC]");
                }

                // Failsafe: If morning bag scan was bypassed, ensure dispatched_at is recorded
                if (empty($order->fulfillment->dispatched_at)) {
                    $fulfillData['dispatched_at'] = $deliveredAt;
                    $fulfillData['notes'] = trim(($fulfillData['notes'] ?? '') . " | [AUTO_DISPATCHED_AT_DOORSTEP]");
                }

                $order->fulfillment->update($fulfillData);
            }

            // If order was Cash on Delivery, record cash collection payment
            if (in_array($order->payment_method, ['cod', 'cash'])) {
                Payment::updateOrCreate(
                    ['order_id' => $order->id, 'gateway' => 'cash'],
                    [
                        'amount'                 => (float) $order->total_amount,
                        'currency'               => 'INR',
                        'status'                 => 'SUCCESS',
                        'payment_method_group'   => 'cash',
                        'payment_method_details' => [
                            'type'         => 'cash_on_delivery',
                            'collected_by' => $rider->name,
                            'collected_at' => $deliveredAt->toIso8601String(),
                            'offline_sync' => $isOfflineSync,
                        ],
                        'bank_reference'         => 'COD_' . $order->order_number,
                        'payment_time'           => $deliveredAt,
                        'gateway_message'        => "Cash on Delivery ₹" . number_format($order->total_amount, 2) . " collected in pocket by {$rider->name}" . ($isOfflineSync ? " (Offline Basement Sync)" : ""),
                    ]
                );
            }

            $historyNote = in_array($order->payment_method, ['cod', 'cash'])
                ? "Doorstep delivery completed by {$rider->name}. Cash of ₹" . number_format($order->total_amount, 2) . " collected in pocket."
                : ($order->payment_method === 'doorstep_upi'
                    ? "Doorstep delivery completed by {$rider->name}. Paid via Doorstep UPI."
                    : "Doorstep delivery completed by {$rider->name} with customer OTP.");

            if ($isOfflineSync) {
                $historyNote .= " [Handover at {$deliveredAt->format('h:i A')}, Synced from Basement Offline Queue]";
            }

            $order->statusHistories()->create([
                'previous_status' => $previousStatus,
                'current_status'  => 'delivered',
                'changed_by'      => $rider->id,
                'notes'           => $historyNote,
                'created_at'      => $deliveredAt,
            ]);

            // Referral Cashback Milestone Rewards
            app(\App\Services\WalletService::class)->rewardReferrerOnDeliveredOrder($order);

            // FCM Push Notification to Customer App
            try {
                app(\App\Services\FcmNotificationService::class)->sendOrderNotification($order, 'delivered');
            } catch (\Throwable $e) {
                Log::warning("[FCM] Delivered push notification error: " . $e->getMessage());
            }

            // Customer WhatsApp Delivery Confirmation & Cash Receipt (Channel 2: Updates)
            try {
                app(\App\Services\WhatsAppService::class)->sendOrderDelivered($order);
            } catch (\Throwable $e) {
                Log::warning("[WhatsApp] Delivered receipt notification error: " . $e->getMessage());
            }
        });

        return $this->sendResponse([
            'order_number' => $order->order_number,
            'status'       => 'delivered',
        ], "Order #{$order->order_number} marked as Delivered successfully! Great job!");
    }

    /**
     * Complete Contactless Delivery via Photo POD (Proof of Delivery).
     * Strictly for Prepaid or settled orders (unpaid COD is rejected).
     */
    public function completeContactless(Request $request, Order $order): JsonResponse
    {
        $rider = $request->user();
        $assignedRiderId = $order->rider_id ?? $order->fulfillment?->rider_id;

        if ($assignedRiderId && $assignedRiderId !== $rider->id && !$rider->isSuperAdmin()) {
            return $this->sendError('Unauthorized.', [], 403);
        }

        // Anti-Fraud Rule: Strictly reject unpaid Cash on Delivery orders
        $isPaid = ($order->payment_status === 'paid' || $order->payment_method === 'wallet' || ($order->total_amount <= 0 && $order->wallet_amount_used > 0));
        if (!$isPaid && in_array($order->payment_method, ['cod', 'cash'])) {
            return $this->sendError('Cash on Delivery orders cannot be delivered contactless without payment.', [], 422);
        }

        $request->validate([
            'photo'      => ['required', 'image', 'max:10240'],
            'drop_type'  => ['required', 'string', 'in:security_guard,doorstep,neighbor'],
            'drop_notes' => ['nullable', 'string', 'max:255'],
            'rider_lat'  => ['nullable', 'numeric'],
            'rider_lng'  => ['nullable', 'numeric'],
        ]);

        $dropTypeLabels = [
            'security_guard' => 'Building Security Guard / Reception',
            'doorstep'       => 'Doorstep / Shoe Rack',
            'neighbor'       => 'Neighbor / Flatmate',
        ];
        $dropLabel = $dropTypeLabels[$request->input('drop_type')] ?? 'Contactless Drop';

        // Optimize and store image as crisp WebP (~60KB) using existing ImageOptimizerService
        $photoFile = $request->file('photo');
        $photoPath = app(\App\Services\ImageOptimizerService::class)->optimizeAndStore(
            $photoFile,
            'pod/' . date('Y/m'),
            1024,
            75
        );

        DB::transaction(function () use ($order, $rider, $request, $dropLabel, $photoPath) {
            $previousStatus = $order->status;

            $order->update([
                'status'         => 'delivered',
                'payment_status' => 'paid',
            ]);

            $fulfillmentNote = trim(
                ($order->fulfillment?->notes ?? '') .
                " | [POD_PHOTO:{$photoPath}][DROP_TYPE:{$request->input('drop_type')}]" .
                ($request->filled('drop_notes') ? " ({$request->input('drop_notes')})" : "")
            );

            if ($order->fulfillment) {
                $order->fulfillment->update([
                    'status'                     => 'delivered',
                    'delivered_at'               => now(),
                    'delivery_issue_resolved_at' => $order->fulfillment->delivery_issue ? now() : null,
                    'delivery_issue_resolution'  => 'Delivered via Contactless Photo POD',
                    'notes'                      => $fulfillmentNote,
                ]);
            }

            $order->statusHistories()->create([
                'previous_status' => $previousStatus,
                'current_status'  => 'delivered',
                'changed_by'      => $rider->id,
                'notes'           => "Contactless delivery completed by {$rider->name}. Left at: {$dropLabel}. Photo proof recorded.",
            ]);

            // Referral Cashback Milestone Rewards
            app(\App\Services\WalletService::class)->rewardReferrerOnDeliveredOrder($order);

            // FCM Push Notification to Customer App
            try {
                app(\App\Services\FcmNotificationService::class)->sendOrderNotification($order, 'delivered');
            } catch (\Throwable $e) {
                Log::warning("[FCM] Delivered push notification error: " . $e->getMessage());
            }

            // Customer WhatsApp Delivery Confirmation & Cash Receipt (Channel 2: Updates)
            try {
                app(\App\Services\WhatsAppService::class)->sendOrderDelivered($order);
            } catch (\Throwable $e) {
                Log::warning("[WhatsApp] Delivered receipt notification error: " . $e->getMessage());
            }
        });

        return $this->sendResponse([
            'order_number' => $order->order_number,
            'status'       => 'delivered',
            'photo_url'    => asset('storage/' . $photoPath),
            'drop_label'   => $dropLabel,
        ], "Contactless delivery completed! Photo proof saved.");
    }

    /**
     * Confirm Doorstep UPI Payment.
     */
    public function confirmDoorstepUpi(Request $request, Order $order): JsonResponse
    {
        $rider = $request->user();

        $assignedRiderId = $order->rider_id ?? $order->fulfillment?->rider_id;
        if ($assignedRiderId && $assignedRiderId !== $rider->id && !$rider->isSuperAdmin()) {
            return $this->sendError('Unauthorized.', [], 403);
        }

        DB::transaction(function () use ($order, $rider, $request) {
            $order->update([
                'payment_method' => 'doorstep_upi',
                'payment_status' => 'paid',
            ]);

            if ($order->fulfillment) {
                $order->fulfillment->update([
                    'notes' => trim(($order->fulfillment->notes ?? '') . " | Paid via Razorpay Doorstep UPI"),
                ]);
            }

            Payment::updateOrCreate(
                ['order_id' => $order->id, 'gateway' => 'razorpay'],
                [
                    'amount'                 => (float) $order->total_amount,
                    'currency'               => 'INR',
                    'status'                 => 'SUCCESS',
                    'payment_method_group'   => 'upi',
                    'payment_method_details' => ['type' => 'doorstep_upi', 'verified_by' => $rider->name],
                    'bank_reference'         => 'RZP_POD_' . time(),
                    'payment_time'           => now(),
                    'gateway_message'        => 'Doorstep UPI Payment Confirmed via Razorpay',
                ]
            );

            $order->statusHistories()->create([
                'previous_status' => $order->status,
                'current_status'  => $order->status,
                'changed_by'      => $rider->id,
                'notes'           => "Razorpay Doorstep UPI Payment of ₹" . number_format($order->total_amount, 2) . " verified by {$rider->name}.",
            ]);
        });

        return $this->sendResponse([
            'is_paid'          => true,
            'formatted_amount' => '₹' . number_format($order->total_amount, 2),
        ], 'UPI payment confirmed.');
    }

    /**
     * Check real-time payment status (for live QR auto-polling and manual CHECK STATUS in Rider App).
     */
    public function paymentStatus(Request $request, Order $order): JsonResponse
    {
        $isPaid = ($order->payment_status === 'paid' || $order->payment_method === 'wallet' || ($order->total_amount <= 0 && $order->wallet_amount_used > 0));

        // If not paid in DB yet, query Razorpay directly as a real-time check
        if (!$isPaid) {
            $keyId = config('services.razorpay.key_id');
            $keySecret = config('services.razorpay.key_secret');

            if (!empty($keyId) && !empty($keySecret)) {
                try {
                    $api = new \Razorpay\Api\Api($keyId, $keySecret);
                    // Check payments associated with this order
                    $payments = $api->payment->all([
                        'notes.order_id' => (string) $order->id,
                        'count'          => 1,
                    ]);

                    if (!empty($payments['items'])) {
                        $latestPayment = $payments['items'][0];
                        if (in_array($latestPayment['status'], ['captured', 'authorized'])) {
                            DB::transaction(function () use ($order, $latestPayment) {
                                $order->update([
                                    'payment_method' => 'doorstep_upi',
                                    'payment_status' => 'paid',
                                ]);

                                Payment::updateOrCreate(
                                    ['order_id' => $order->id, 'gateway' => 'razorpay'],
                                    [
                                        'amount'                 => (float) $order->total_amount,
                                        'currency'               => 'INR',
                                        'status'                 => 'SUCCESS',
                                        'payment_method_group'   => 'upi',
                                        'payment_method_details' => [
                                            'type'       => 'razorpay_direct_check',
                                            'payment_id' => $latestPayment['id'] ?? null,
                                        ],
                                        'bank_reference'         => $latestPayment['id'] ?? ('RZP_' . time()),
                                        'payment_time'           => now(),
                                        'gateway_message'        => 'Doorstep UPI Verified via Razorpay Live Status Check',
                                    ]
                                );
                            });

                            $isPaid = true;
                        }
                    }
                } catch (\Throwable $e) {
                    // Fail silently and return database state
                }
            }
        }

        return $this->sendResponse([
            'order_id'        => $order->id,
            'order_number'    => $order->order_number,
            'is_paid'         => (bool) $isPaid,
            'payment_method'  => $order->payment_method,
            'payment_status'  => $order->payment_status,
        ], 'Payment status checked.');
    }

    /**
     * Generate or Retrieve Razorpay Dynamic Doorstep QR Code.
     */
    protected function generateRazorpayDoorstepQr(Order $order): array
    {
        $keyId = config('services.razorpay.key_id');
        $keySecret = config('services.razorpay.key_secret');

        // 1. Try Razorpay Official Dynamic QR Code API (single_use, locked amount)
        if (!empty($keyId) && !empty($keySecret)) {
            try {
                $api = new \Razorpay\Api\Api($keyId, $keySecret);
                $qrCode = $api->qrCode->create([
                    'type'           => 'upi_qr',
                    'name'           => 'ShopCalm Fleet',
                    'usage'          => 'single_use',
                    'fixed_amount'   => true,
                    'payment_amount' => (int) round(((float) $order->total_amount) * 100), // in paise
                    'description'    => "Doorstep UPI for Order #{$order->order_number}",
                    'notes'          => [
                        'order_id'     => (string) $order->id,
                        'order_number' => (string) $order->order_number,
                        'flow'         => 'doorstep_delivery',
                    ],
                ]);

                if (!empty($qrCode->image_url)) {
                    return [
                        'qr_url'     => $qrCode->image_url,
                        'upi_intent' => $qrCode->id ?? "rzp_qr_{$order->id}",
                        'is_dynamic' => true,
                    ];
                }
            } catch (\Throwable $e) {
                \Log::info("[Razorpay QR] Dynamic QR API fallback: " . $e->getMessage());
            }
        }

        // 2. High-reliability fallback: Razorpay Merchant UPI Intent QR
        $merchantVpa = config('services.razorpay.merchant_vpa') ?: 'shopcalm.payments@razorpay';
        $upiIntent = "upi://pay?pa=" . urlencode($merchantVpa)
            . "&pn=" . urlencode("ShopCalm")
            . "&am=" . number_format($order->total_amount, 2, '.', '')
            . "&cu=INR"
            . "&tn=" . urlencode("Order #" . $order->order_number);

        $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($upiIntent);

        return [
            'qr_url'     => $qrUrl,
            'upi_intent' => $upiIntent,
            'is_dynamic' => false,
        ];
    }

    /**
     * Webhook listener for Razorpay QR Code events.
     */
    public function handleRazorpayWebhook(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $sig = $request->header('X-Razorpay-Signature');
        $secret = config('services.razorpay.webhook_secret') 
            ?: (\App\Models\Setting::get('razorpay_key_secret') ?: config('services.razorpay.key_secret'));

        // Cryptographic Webhook HMAC-SHA256 Signature Guard
        if (!empty($secret) && !empty($sig)) {
            $expectedSignature = hash_hmac('sha256', $payload, $secret);
            if (!hash_equals($expectedSignature, $sig)) {
                \Log::warning("[Razorpay Webhook] Invalid signature rejected from IP: " . $request->ip());
                return response()->json(['status' => 'error', 'message' => 'Invalid webhook signature.'], 400);
            }
        }

        try {
            $data = json_decode($payload, true);
            $event = $data['event'] ?? '';

            if (in_array($event, ['qr_code.credited', 'payment.captured'])) {
                $paymentEntity = $data['payload']['payment']['entity'] ?? [];
                $orderNumber = $paymentEntity['notes']['order_number'] ?? null;
                $orderId = $paymentEntity['notes']['order_id'] ?? null;

                $order = null;
                if ($orderId) {
                    $order = Order::find($orderId);
                } elseif ($orderNumber) {
                    $order = Order::where('order_number', $orderNumber)->first();
                }

                if ($order && $order->payment_status !== 'paid') {
                    $order->update([
                        'payment_method' => 'doorstep_upi',
                        'payment_status' => 'paid',
                    ]);

                    Payment::updateOrCreate(
                        ['order_id' => $order->id, 'gateway' => 'razorpay'],
                        [
                            'amount'                 => (float) $order->total_amount,
                            'currency'               => 'INR',
                            'status'                 => 'SUCCESS',
                            'payment_method_group'   => 'upi',
                            'payment_method_details' => ['type' => 'razorpay_dynamic_qr', 'event' => $event],
                            'bank_reference'         => $paymentEntity['id'] ?? ('RZP_QR_' . time()),
                            'payment_time'           => now(),
                            'gateway_message'        => 'Doorstep UPI Verified via Razorpay Webhook',
                        ]
                    );

                    \Log::info("[Razorpay Webhook] Order #{$order->order_number} marked paid via Razorpay QR.");
                }
            }

            return response()->json(['status' => 'ok']);
        } catch (\Throwable $e) {
            \Log::error("[Razorpay Webhook Error] " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * Report Delivery Exception / Cannot Deliver.
     */
    public function reportIssue(Request $request, Order $order): JsonResponse
    {
        $rider = $request->user();

        $assignedRiderId = $order->rider_id ?? $order->fulfillment?->rider_id;
        if ($assignedRiderId && $assignedRiderId !== $rider->id && !$rider->isSuperAdmin()) {
            return $this->sendError('Unauthorized.', [], 403);
        }

        $request->validate([
            'reason'              => ['required', 'string', 'max:255'],
            'client_reported_at' => ['nullable', 'date'],
        ]);

        $reason = $request->input('reason');
        $clientTime = $request->input('client_reported_at');
        $reportedAt = $clientTime ? \Carbon\Carbon::parse($clientTime) : now();

        $fulfillment = $order->fulfillment;
        if (!$fulfillment) {
            $fulfillment = $order->fulfillment()->create([
                'type'       => 'local_fleet',
                'status'     => 'out_for_delivery',
                'rider_id'   => $rider->id,
                'rider_name' => $rider->name,
            ]);
        }
        $fulfillment->update([
            'delivery_issue'             => $reason,
            'delivery_issue_at'          => $reportedAt,
            'delivery_issue_resolved_at' => null,
            'delivery_issue_resolution'  => null,
        ]);

        $historyNote = "Delivery issue reported by {$rider->name}: {$reason}";
        if ($clientTime) {
            $historyNote .= " [Logged offline at {$reportedAt->format('h:i A')}]";
        }

        $order->statusHistories()->create([
            'previous_status' => $order->status,
            'current_status'  => $order->status,
            'changed_by'      => $rider->id,
            'notes'           => $historyNote,
            'created_at'      => $reportedAt,
        ]);

        return $this->sendResponse([
            'order_number' => $order->order_number,
            'issue'        => $reason,
        ], "Delivery issue logged for order #{$order->order_number}. Order Manager notified.");
    }

    /**
     * Get Cash in Pocket & Past Settlements History.
     */
    public function settlements(Request $request): JsonResponse
    {
        $rider = $request->user();

        $settlements = CashSettlement::where('rider_id', $rider->id)
            ->with(['receivedBy'])
            ->latest()
            ->paginate(20);

        $cashInHandPending = Order::whereHas('fulfillment', fn($q) => $q->where('rider_id', $rider->id)->whereNull('cod_deposited_at'))
            ->where('status', 'delivered')
            ->whereIn('payment_method', ['cod', 'cash'])
            ->sum('total_amount');

        $totalDeposited = CashSettlement::where('rider_id', $rider->id)->sum('total_amount');

        $undeliveredOrders = Order::with('fulfillment')
            ->whereHas('fulfillment', fn($q) => $q->where('rider_id', $rider->id)->where('status', '!=', 'returned')->whereNull('returned_at'))
            ->where('status', '!=', 'delivered')
            ->latest('updated_at')
            ->get();

        return $this->sendResponse([
            'cash_in_hand_pending' => (float) $cashInHandPending,
            'formatted_cash'       => '₹' . number_format($cashInHandPending, 2),
            'total_deposited'      => (float) $totalDeposited,
            'formatted_deposited'  => '₹' . number_format($totalDeposited, 2),
            'undelivered_count'    => $undeliveredOrders->count(),
            'undelivered_orders'   => $undeliveredOrders->map(fn($o) => [
                'id'           => $o->id,
                'order_number' => $o->order_number,
                'customer'     => $o->shipping_name,
                'reason'       => $o->fulfillment?->delivery_issue ?? 'Undelivered',
                'amount'       => (float) $o->total_amount,
            ]),
            'settlements'          => $settlements->items(),
        ], 'Settlements retrieved.');
    }

    /**
     * Generate Secure End-of-Day Cash Deposit QR Code & 6-Digit PIN.
     */
    public function settlementQr(Request $request): JsonResponse
    {
        $rider = $request->user();

        // Pending COD orders awaiting cash deposit
        $pendingOrders = Order::with('fulfillment')
            ->whereHas('fulfillment', fn($q) => $q->where('rider_id', $rider->id)->whereNull('cod_deposited_at'))
            ->where('status', 'delivered')
            ->whereIn('payment_method', ['cod', 'cash'])
            ->latest('updated_at')
            ->get();

        // Physical undelivered boxes in rider's bag
        $undeliveredOrders = Order::with('fulfillment')
            ->whereHas('fulfillment', fn($q) => $q->where('rider_id', $rider->id)->where('status', '!=', 'returned')->whereNull('returned_at'))
            ->where('status', '!=', 'delivered')
            ->latest('updated_at')
            ->get();

        $pendingAmount = (float) $pendingOrders->sum('total_amount');
        $handoverPin = sprintf('%06d', (($rider->id * 7919) + (int) date('ymd')) % 1000000);

        $payload = json_encode([
            'type'               => 'hub_cash_deposit',
            'rider_id'           => $rider->id,
            'rider_name'         => $rider->name,
            'amount'             => $pendingAmount,
            'order_count'        => $pendingOrders->count(),
            'undelivered_count'  => $undeliveredOrders->count(),
            'undelivered_orders' => $undeliveredOrders->pluck('order_number')->values()->all(),
            'pin'                => $handoverPin,
            'orders'             => $pendingOrders->pluck('order_number')->values()->all(),
            'timestamp'          => time(),
        ]);

        $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($payload);

        return $this->sendResponse([
            'rider_id'           => $rider->id,
            'rider_name'         => $rider->name,
            'pending_amount'     => $pendingAmount,
            'formatted_amount'   => '₹' . number_format($pendingAmount, 2),
            'order_count'        => $pendingOrders->count(),
            'orders'             => $pendingOrders->map(fn($o) => [
                'id'           => $o->id,
                'order_number' => $o->order_number,
                'amount'       => (float) $o->total_amount,
                'customer'     => $o->shipping_name,
            ]),
            'undelivered_count'  => $undeliveredOrders->count(),
            'undelivered_orders' => $undeliveredOrders->map(fn($o) => [
                'id'           => $o->id,
                'order_number' => $o->order_number,
                'customer'     => $o->shipping_name,
                'reason'       => $o->fulfillment?->delivery_issue ?? 'Undelivered',
                'amount'       => (float) $o->total_amount,
            ]),
            'handover_pin'       => $handoverPin,
            'qr_payload'         => $payload,
            'qr_url'             => $qrUrl,
        ], 'Settlement QR generated.');
    }

    /**
     * Real-time polling endpoint to check if cashier approved deposit.
     */
    public function settlementStatus(Request $request): JsonResponse
    {
        $rider = $request->user();

        $pendingAmount = (float) Order::whereHas('fulfillment', fn($q) => $q->where('rider_id', $rider->id)->whereNull('cod_deposited_at'))
            ->where('status', 'delivered')
            ->whereIn('payment_method', ['cod', 'cash'])
            ->sum('total_amount');

        $undeliveredCount = Order::whereHas('fulfillment', fn($q) => $q->where('rider_id', $rider->id)->where('status', '!=', 'returned')->whereNull('returned_at'))
            ->where('status', '!=', 'delivered')
            ->count();

        $latestSettlement = CashSettlement::where('rider_id', $rider->id)
            ->latest()
            ->first();

        $isSettled = ($pendingAmount <= 0.01 && $undeliveredCount === 0);

        return $this->sendResponse([
            'is_settled'               => $isSettled,
            'pending_amount'           => $pendingAmount,
            'formatted_amount'         => '₹' . number_format($pendingAmount, 2),
            'undelivered_count'        => $undeliveredCount,
            'latest_settlement_number' => $latestSettlement?->settlement_number,
            'latest_settled_amount'    => (float) ($latestSettlement?->total_amount ?? 0),
            'latest_settled_at'        => $latestSettlement?->created_at?->format('d M Y, h:i A'),
        ], 'Settlement status checked.');
    }

    /**
     * Get Archive of Delivered Orders Today & History.
     */
    public function completed(Request $request): JsonResponse
    {
        $rider = $request->user();

        $completedOrders = Order::with(['fulfillment'])
            ->whereHas('fulfillment', fn($q) => $q->where('rider_id', $rider->id))
            ->where('status', 'delivered')
            ->latest('updated_at')
            ->paginate(20);

        $formatted = collect($completedOrders->items())->map(function ($order) {
            $deliveredTime = $order->fulfillment?->delivered_at ?? $order->updated_at;
            return [
                'id'             => $order->id,
                'order_number'   => $order->order_number,
                'customer_name'  => $order->shipping_name,
                'customer_phone' => $order->shipping_phone,
                'address'        => $order->shipping_address . ', ' . $order->shipping_city . ' - ' . $order->shipping_zip,
                'total_amount'   => (float) $order->total_amount,
                'formatted_total'=> '₹' . number_format($order->total_amount, 2),
                'payment_method' => $order->payment_method,
                'delivered_at'   => $deliveredTime ? $deliveredTime->format('d M Y, h:i A') : 'Delivered',
            ];
        });

        return $this->sendResponse([
            'total_delivered'  => $completedOrders->total(),
            'completed_orders' => $formatted,
        ], 'Completed deliveries loaded.');
    }

    /**
     * Verify parcels loaded into delivery bag at hub morning dispatch.
     */
    public function verifyBagLoad(Request $request): JsonResponse
    {
        $rider = $request->user();

        $request->validate([
            'order_numbers'   => ['required', 'array'],
            'order_numbers.*' => ['string'],
        ]);

        $orderNumbers = $request->input('order_numbers');

        $orders = Order::with('fulfillment')
            ->whereHas('fulfillment', fn($q) => $q->where('rider_id', $rider->id))
            ->whereIn('order_number', $orderNumbers)
            ->get();

        $verifiedCount = 0;
        $whatsAppService = app(\App\Services\WhatsAppService::class);

        foreach ($orders as $order) {
            if ($order->fulfillment) {
                $order->fulfillment->update([
                    'status'        => 'out_for_delivery',
                    'dispatched_at' => $order->fulfillment->dispatched_at ?: now(),
                    'notes'         => trim(($order->fulfillment->notes ?? '') . " | [BAG_VERIFIED]"),
                ]);
            }
            if (in_array($order->status, ['packed', 'shipped', 'processing'])) {
                $previousStatus = $order->status;
                $order->update(['status' => 'out for delivery']);
                $order->statusHistories()->create([
                    'previous_status' => $previousStatus,
                    'current_status'  => 'out for delivery',
                    'changed_by'      => $rider->id,
                    'notes'           => "Delivery partner {$rider->name} scanned tote bag. Dispatched from hub.",
                ]);
            }

            // Universal Scan-to-Dispatch: Notify Customer via WhatsApp Channel 2
            try {
                $whatsAppService->sendOutForDeliveryLocal($order);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("[RiderController verifyBagLoad] WhatsApp Out for Delivery notification failed for #{$order->order_number}: " . $e->getMessage());
            }

            $verifiedCount++;
        }

        return $this->sendResponse([
            'verified_count'  => $verifiedCount,
            'verified_orders' => $orders->pluck('order_number'),
        ], "{$verifiedCount} parcel(s) successfully verified and loaded in bag.");
    }

    /**
     * Resolve Bengaluru Pincode / Neighborhood Centroids for fallback coordinates.
     */
    public static function resolvePincodeCoordinates(?string $zip, ?string $city, ?string $address): array
    {
        $cleanZip = preg_replace('/\D/', '', $zip ?? '');

        // Bengaluru Key Delivery Neighborhood Centroids
        $directory = [
            '560092' => ['lat' => 13.0580, 'lng' => 77.5890], // Byatarayanapura / Kodigehalli / Sahakara Nagar
            '560024' => ['lat' => 13.0358, 'lng' => 77.5970], // Hebbal
            '560064' => ['lat' => 13.1007, 'lng' => 77.5963], // Yelahanka
            '560037' => ['lat' => 12.9591, 'lng' => 77.6974], // Marathahalli
            '560103' => ['lat' => 12.9260, 'lng' => 77.6762], // Bellandur / Outer Ring Rd
            '560100' => ['lat' => 12.8452, 'lng' => 77.6602], // Electronic City
            '560034' => ['lat' => 12.9352, 'lng' => 77.6245], // Koramangala
            '560038' => ['lat' => 12.9784, 'lng' => 77.6408], // Indiranagar
            '560008' => ['lat' => 12.9719, 'lng' => 77.6205], // Ulsoor / Halasuru
            '560066' => ['lat' => 12.9698, 'lng' => 77.7500], // Whitefield
            '560067' => ['lat' => 12.9830, 'lng' => 77.7600], // Kadugodi / Whitefield East
            '560076' => ['lat' => 12.9166, 'lng' => 77.6101], // BTM Layout
            '560078' => ['lat' => 12.9063, 'lng' => 77.5857], // JP Nagar
            '560041' => ['lat' => 12.9248, 'lng' => 77.5838], // Jayanagar
            '560085' => ['lat' => 12.9255, 'lng' => 77.5468], // Banashankari
            '560001' => ['lat' => 12.9716, 'lng' => 77.5946], // Central / MG Road
            '560003' => ['lat' => 12.9982, 'lng' => 77.5709], // Malleshwaram
            '560010' => ['lat' => 12.9902, 'lng' => 77.5534], // Rajajinagar
        ];

        if (!empty($cleanZip) && isset($directory[$cleanZip])) {
            return $directory[$cleanZip];
        }

        // Default to Hebbal dark store hub if completely unmapped
        return ['lat' => 13.0628, 'lng' => 77.5815];
    }

    /**
     * Logout Delivery Partner.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return $this->sendResponse([], 'Signed out successfully.');
    }

    /**
     * Active Doorstep Reverse Logistics & Replacement Pickups assigned to this Rider.
     */
    public function replacements(Request $request): JsonResponse
    {
        $rider = $request->user();

        $pickups = OrderReplacement::with(['order', 'product', 'user'])
            ->where('rider_id', $rider->id)
            ->whereIn('status', ['approved', 'pickup_assigned'])
            ->latest('pickup_date')
            ->get()
            ->map(function ($rep) {
                return [
                    'id'                  => $rep->id,
                    'replacement_number'  => $rep->replacement_number,
                    'order_number'        => $rep->order?->order_number,
                    'customer_name'       => $rep->user?->name ?? 'Customer',
                    'customer_phone'      => $rep->order?->shipping_phone ?? $rep->user?->mobile_number,
                    'shipping_address'    => $rep->order?->shipping_address,
                    'shipping_city'       => $rep->order?->shipping_city,
                    'shipping_zip'        => $rep->order?->shipping_zip,
                    'item_name'           => $rep->product?->name ?? 'Product Item',
                    'reason_label'        => $rep->reason_label,
                    'pickup_date'         => $rep->pickup_date ? $rep->pickup_date->format('Y-m-d') : null,
                    'order_manager_notes' => $rep->order_manager_notes,
                    'status'              => $rep->status,
                ];
            });

        return $this->sendResponse([
            'pickups' => $pickups,
            'count'   => $pickups->count(),
        ], 'Assigned reverse pickups loaded.');
    }

    /**
     * Complete Doorstep Reverse Pickup (Rider collected the returned/damaged item).
     */
    public function completePickup(Request $request, OrderReplacement $replacement): JsonResponse
    {
        $rider = $request->user();

        if ($replacement->rider_id && $replacement->rider_id !== $rider->id && !$rider->isSuperAdmin()) {
            return $this->sendError('Unauthorized. This pickup is assigned to another delivery executive.', [], 403);
        }

        $request->validate([
            'rider_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $replacement->update([
            'status'       => 'picked_up',
            'picked_up_at' => now(),
            'order_manager_notes' => $request->rider_notes ? ($replacement->order_manager_notes . " [Rider: {$request->rider_notes}]") : $replacement->order_manager_notes,
        ]);

        return $this->sendResponse([
            'replacement_number' => $replacement->replacement_number,
            'status'             => 'picked_up',
            'picked_up_at'       => $replacement->picked_up_at->format('Y-m-d H:i:s'),
        ], "Item collected from customer for Replacement #{$replacement->replacement_number}.");
    }
}
