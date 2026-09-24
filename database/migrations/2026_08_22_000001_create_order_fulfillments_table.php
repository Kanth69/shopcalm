<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_fulfillments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            
            // Fulfillment Mode: local_fleet (Bengaluru) or courier (Pan-India)
            $table->enum('type', ['local_fleet', 'courier'])->default('courier')->index();
            
            // Fulfillment Status
            $table->enum('status', [
                'pending',
                'assigned',
                'dispatched',
                'in_transit',
                'out_for_delivery',
                'delivered',
                'failed',
                'returned'
            ])->default('pending')->index();

            // Local Fleet Attributes (For Bengaluru in-house fleet / Porter / Dunzo)
            $table->string('rider_name')->nullable();
            $table->string('rider_phone')->nullable();
            $table->string('delivery_slot')->nullable(); // e.g. "Morning (10 AM - 2 PM)", "Same-Day Express"
            $table->string('delivery_otp', 6)->nullable(); // Optional delivery completion verification code

            // Courier Logistics Attributes (For Pan-India 3rd-party logistics)
            $table->string('carrier_code')->nullable(); // e.g. "bluedart", "delhivery", "dtdc", "india_post"
            $table->string('carrier_name')->nullable(); // e.g. "BlueDart Express"
            $table->string('tracking_number')->nullable()->index(); // AWB Number
            $table->text('tracking_url')->nullable(); // Live courier tracking URL
            $table->text('shipping_label_url')->nullable();

            // Audit & Timestamps
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();

            $table->timestamps();

            // Each order has exactly one primary fulfillment record
            $table->unique('order_id');
        });

        // ── Data Backfill: Populate order_fulfillments for all existing orders ──
        $existingOrders = DB::table('orders')->get();
        foreach ($existingOrders as $order) {
            $pincode = trim($order->shipping_zip ?? '');
            $city = strtolower(trim($order->shipping_city ?? ''));
            
            $isBengaluru = str_starts_with($pincode, '560') || str_contains($city, 'bengaluru') || str_contains($city, 'bangalore');
            $type = $isBengaluru ? 'local_fleet' : 'courier';

            $fulfillmentStatus = match($order->status) {
                'delivered' => 'delivered',
                'shipped' => 'dispatched',
                'out for delivery' => 'out_for_delivery',
                'cancelled' => 'returned',
                default => ($order->tracking_number ? 'assigned' : 'pending'),
            };

            DB::table('order_fulfillments')->insert([
                'order_id'        => $order->id,
                'type'            => $type,
                'status'          => $fulfillmentStatus,
                'carrier_name'    => $order->courier_partner ?? null,
                'tracking_number' => $order->tracking_number ?? null,
                'tracking_url'    => $order->tracking_url ?? null,
                'dispatched_at'   => $order->shipped_at ?? null,
                'delivered_at'    => $order->delivered_at ?? null,
                'created_at'      => $order->created_at ?? now(),
                'updated_at'      => $order->updated_at ?? now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_fulfillments');
    }
};
