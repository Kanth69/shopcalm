<?php

namespace App\Observers;

use App\Models\Order;
use App\Services\MarketingAutomationService;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        try {
            app(MarketingAutomationService::class)->triggerOrderEvent($order, 'placed');
        } catch (\Throwable $e) {
            Log::error("[OrderObserver] Failed on order created notification: " . $e->getMessage());
        }
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        try {
            if ($order->isDirty('status')) {
                $newStatus = $order->status;
                app(MarketingAutomationService::class)->triggerOrderEvent($order, $newStatus);
            }
        } catch (\Throwable $e) {
            Log::error("[OrderObserver] Failed on order updated notification: " . $e->getMessage());
        }
    }
}
