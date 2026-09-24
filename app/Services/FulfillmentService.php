<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderFulfillment;
use Illuminate\Support\Facades\DB;

class FulfillmentService
{
    /**
     * Automatically detect the fulfillment zone based on shipping address.
     * Bengaluru pincodes (starting with 560) or city = 'bengaluru'/'bangalore' -> local_fleet
     */
    public function detectZone(string $pincode, string $city): string
    {
        $cleanZip = trim($pincode);
        $cleanCity = strtolower(trim($city));

        if (str_starts_with($cleanZip, '560') || str_contains($cleanCity, 'bengaluru') || str_contains($cleanCity, 'bangalore')) {
            return 'local_fleet';
        }

        return 'courier';
    }

    /**
     * Create initial fulfillment record when an order is created.
     */
    public function createInitialFulfillment(Order $order): OrderFulfillment
    {
        $zoneType = $this->detectZone($order->shipping_zip ?? '', $order->shipping_city ?? '');

        return OrderFulfillment::updateOrCreate(
            ['order_id' => $order->id],
            [
                'type'   => $zoneType,
                'status' => 'pending',
            ]
        );
    }

    /**
     * Assign Bengaluru Local Delivery Rider / Executive.
     */
    public function assignLocalRider(Order $order, array $data, ?int $staffId = null): OrderFulfillment
    {
        return DB::transaction(function () use ($order, $data, $staffId) {
            $fulfillment = $order->fulfillment ?: new OrderFulfillment(['order_id' => $order->id]);
            $otp = $order->delivery_otp ?: (string) random_int(1000, 9999);

            $fulfillment->fill([
                'type'          => 'local_fleet',
                'status'        => 'out_for_delivery',
                'rider_id'      => $data['rider_id'] ?? null,
                'rider_name'    => $data['rider_name'],
                'rider_phone'   => $data['rider_phone'],
                'delivery_slot' => $data['delivery_slot'] ?? null,
                'delivery_otp'  => $otp,
                'notes'         => $data['notes'] ?? null,
                'assigned_by'   => $staffId,
                'assigned_at'   => now(),
                'dispatched_at' => now(),
            ]);
            $fulfillment->save();

            // Advance Order status to 'out for delivery'
            $previousStatus = $order->status;
            $order->update([
                'status' => 'out for delivery',
            ]);

            $order->statusHistories()->create([
                'previous_status' => $previousStatus,
                'current_status'  => 'out for delivery',
                'changed_by'      => $staffId,
                'notes'           => "Bengaluru Local Delivery: Assigned to {$data['rider_name']} ({$data['rider_phone']}). Slot: " . ($data['delivery_slot'] ?? 'Express') . " [OTP: {$otp}]",
            ]);

            return $fulfillment;
        });
    }

    /**
     * Assign Pan-India 3rd-Party Courier & AWB Tracking Number.
     */
    public function assignCourierTracking(Order $order, array $data, ?int $staffId = null): OrderFulfillment
    {
        return DB::transaction(function () use ($order, $data, $staffId) {
            $fulfillment = $order->fulfillment ?: new OrderFulfillment(['order_id' => $order->id]);

            $fulfillment->fill([
                'type'            => 'courier',
                'status'          => 'dispatched',
                'carrier_name'    => $data['courier_partner'],
                'tracking_number' => $data['tracking_number'],
                'tracking_url'    => $data['tracking_url'] ?? null,
                'notes'           => $data['notes'] ?? null,
                'assigned_by'     => $staffId,
                'assigned_at'     => now(),
                'dispatched_at'   => now(),
            ]);
            $fulfillment->save();

            // Sync legacy columns on order as well
            $previousStatus = $order->status;
            $order->update([
                'status'          => 'shipped',
                'courier_partner' => $data['courier_partner'],
                'tracking_number' => $data['tracking_number'],
                'tracking_url'    => $data['tracking_url'] ?? null,
                'shipped_at'      => $order->shipped_at ?? now(),
            ]);

            $order->statusHistories()->create([
                'previous_status' => $previousStatus,
                'current_status'  => 'shipped',
                'changed_by'      => $staffId,
                'notes'           => "National Courier Dispatched: {$data['courier_partner']} (AWB: {$data['tracking_number']})",
            ]);

            return $fulfillment;
        });
    }

    /**
     * 1-Click Smart Auto-Assign Local Fleet Orders to Active Delivery Partners.
     */
    public function autoAssignLocalFleetOrders(?int $staffId = null): array
    {
        // 1. Fetch active delivery partners
        $riders = \App\Models\User::where('role_id', \App\Models\User::ROLE_DELIVERY_PARTNER)
            ->where('status', 'active')
            ->get();

        if ($riders->isEmpty()) {
            return [
                'count'   => 0,
                'status'  => 'warning',
                'message' => 'No active delivery partners currently registered or online.',
            ];
        }

        // 2. Fetch unassigned local fleet orders
        $unassignedOrders = Order::whereHas('fulfillment', function ($q) {
                $q->where('type', 'local_fleet')->whereNull('rider_id');
            })
            ->whereIn('status', ['confirmed', 'processing', 'packed', 'pending'])
            ->get();

        if ($unassignedOrders->isEmpty()) {
            return [
                'count'   => 0,
                'status'  => 'info',
                'message' => 'No pending local fleet orders waiting for auto-assignment.',
            ];
        }

        $assignedCount = 0;
        $riderIndex = 0;
        $totalRiders = $riders->count();

        foreach ($unassignedOrders as $order) {
            $rider = $riders[$riderIndex % $totalRiders];

            $data = [
                'rider_id'      => $rider->id,
                'rider_name'    => $rider->name,
                'rider_phone'   => $rider->mobile_number ?: '9900000000',
                'delivery_slot' => 'Express Local',
                'notes'         => 'Auto-assigned by Logistics Hub Dispatch Engine',
            ];

            $this->assignLocalRider($order, $data, $staffId);
            $assignedCount++;
            $riderIndex++;
        }

        return [
            'count'   => $assignedCount,
            'status'  => 'success',
            'message' => "Successfully auto-assigned {$assignedCount} local fleet parcels across {$totalRiders} active riders!",
        ];
    }
}
