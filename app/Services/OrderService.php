<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

class OrderService
{
    public function getCustomerOrders(Request $request)
    {
        $query = Auth::user()->orders()->with('items.product');

        if ($request->filled('search')) {
            $query->where('order_number', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return $query->latest()->paginate(10);
    }

    public function getAdminOrders(Request $request)
    {
        $query = Order::with(['user', 'items']);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('shipping_name', 'like', "%{$search}%")
                  ->orWhere('shipping_phone', 'like', "%{$search}%")
                  ->orWhere('shipping_email', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQ) use ($search) {
                      $userQ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        return $query->latest()->paginate(15);
    }

    /**
     * Update order status and record history.
     */
    public function updateOrderStatus(Order $order, string $newStatus, ?string $notes = null): Order
    {
        if ($order->status === $newStatus) {
            return $order;
        }

        return DB::transaction(function () use ($order, $newStatus, $notes) {
            $previousStatus = $order->status;

            // 1. Update the order
            $order->update(['status' => $newStatus]);

            // 2. Record the history
            $order->statusHistories()->create([
                'previous_status' => $previousStatus,
                'current_status'  => $newStatus,
                'changed_by'      => Auth::id(), // Usually an admin ID
                'notes'           => $notes,
            ]);

            return $order;
        });
    }

    /**
     * Helper to log initial order creation status
     */
    public function recordInitialStatus(Order $order): void
    {
        $order->statusHistories()->create([
            'previous_status' => null,
            'current_status'  => $order->status,
            'changed_by'      => Auth::id(), // The customer placing the order
            'notes'           => 'Order Placed',
        ]);
    }

    /**
     * Calculate cancellation fee (GST) and net refund breakdown.
     */
    public function calculateCancellationSummary(Order $order): array
    {
        // GST is applicable ONLY on the Product Price Net Portion (excluding Shipping & COD Fees)
        $productPayable = max(0, (float) $order->subtotal_amount - (float) $order->coupon_discount_amount);
        
        $taxAmount = (float) $order->tax_amount;
        if ($taxAmount <= 0) {
            $productTaxableBase = round($productPayable / 1.18, 2);
            $taxAmount = round($productPayable - $productTaxableBase, 2);
        }

        $cancellationFee = min((float) $order->total_amount, $taxAmount);
        $netRefund = max(0, (float) $order->total_amount - $cancellationFee);

        return [
            'total_amount'         => (float) $order->total_amount,
            'product_payable'      => $productPayable,
            'product_taxable_base' => round($productPayable / 1.18, 2),
            'cancellation_fee'     => $cancellationFee,
            'net_refund'           => $netRefund,
        ];
    }

    /**
     * Cancel a Prepaid Order (Paid Online) & Trigger Direct PG Refund (Less GST Fee).
     * Bypasses wallet completely as wallet is reserved for referrals.
     */
    public function cancelPrepaidOrder(Order $order, string $reason, string $refundMethod = 'original_source', ?string $upiId = null): \App\Models\OrderCancellation
    {
        if (!in_array($order->status, ['pending', 'confirmed'])) {
            throw new Exception("Order #{$order->order_number} cannot be cancelled as it is in '{$order->status}' status. Cancellations are only allowed during Pending or Confirmed stage.");
        }

        return DB::transaction(function () use ($order, $reason, $refundMethod, $upiId) {
            $summary = $this->calculateCancellationSummary($order);
            $user = $order->user;

            $refundStatus = 'pending';
            $refundRef = null;

            if ($summary['net_refund'] > 0) {
                // Trigger Direct Gateway Refund via Razorpay API back to Original Payment Method
                try {
                    $targetRef = $order->payment_reference ?: $order->order_number;
                    $razorpay = app(\App\Services\RazorpayService::class);
                    $rzResult = $razorpay->createRefund(
                        $targetRef,
                        $summary['net_refund'],
                        "Prepaid Cancellation Refund for Order #{$order->order_number} (Less GST Fee)"
                    );

                    if (!empty($rzResult['success'])) {
                        $refundStatus = ($rzResult['refund_status'] === 'success' || $rzResult['refund_status'] === 'processed') ? 'processed' : 'pending';
                        $refundRef = $rzResult['razorpay_refund_id'] ?? ('RFD-' . time());
                    }
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning("Direct Razorpay Refund attempt error for #{$order->order_number}: " . $e->getMessage());
                    $refundStatus = 'pending';
                }
            } else {
                $refundStatus = 'none';
            }

            // Record in order_cancellations table
            $cancellation = \App\Models\OrderCancellation::create([
                'order_id'            => $order->id,
                'user_id'             => $user ? $user->id : $order->user_id,
                'cancelled_by_type'   => 'customer',
                'cancelled_by_id'     => $user ? $user->id : null,
                'cancellation_reason' => $reason,
                'cancellation_fee'    => $summary['cancellation_fee'],
                'refund_amount'       => $summary['net_refund'],
                'refund_status'       => $refundStatus,
                'refund_method'       => $refundMethod ?: 'original_source',
                'refund_upi_id'       => $upiId,
                'payment_reference'   => $refundRef,
            ]);

            // Restore product & option stocks
            $this->restoreOrderProductStocks($order);

            // Update order status
            $this->updateOrderStatus($order, 'cancelled', "Cancelled by customer (Prepaid direct refund to payment source): {$reason}");

            return $cancellation;
        });
    }

    /**
     * Cancel a COD Order (100% Free Cancellation, Zero Fee).
     */
    public function cancelCodOrderFree(Order $order, string $reason): \App\Models\OrderCancellation
    {
        if (!in_array($order->status, ['pending', 'confirmed'])) {
            throw new Exception("Order #{$order->order_number} cannot be cancelled as it is in '{$order->status}' status. Cancellations are only allowed during Pending or Confirmed stage.");
        }

        return DB::transaction(function () use ($order, $reason) {
            $user = $order->user;

            $cancellation = \App\Models\OrderCancellation::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'user_id'             => $order->user_id,
                    'cancelled_by_type'   => 'customer',
                    'cancelled_by_id'     => Auth::id() ?: $order->user_id,
                    'cancellation_reason' => $reason,
                    'cancellation_fee'    => 0.00, // 100% Free for COD
                    'refund_amount'       => 0.00,
                    'refund_status'       => 'none',
                    'refund_method'       => 'none',
                    'payment_status'      => 'waived',
                ]
            );

            // Restore product & option stocks
            $this->restoreOrderProductStocks($order);

            // Update order status
            $this->updateOrderStatus($order, 'cancelled', "Cancelled by customer (100% Free COD Cancellation): {$reason}");

            return $cancellation;
        });
    }

    /**
     * Cancel an Order by Super Admin / Store Management (Full 100% Refund, Zero Penalty).
     */
    public function cancelOrderByAdmin(Order $order, string $reason, string $refundMethod = 'wallet', ?string $adminNotes = null): \App\Models\OrderCancellation
    {
        if (in_array($order->status, ['cancelled', 'delivered', 'returned'])) {
            throw new Exception("Order #{$order->order_number} cannot be cancelled as its status is '{$order->status}'.");
        }

        return DB::transaction(function () use ($order, $reason, $refundMethod, $adminNotes) {
            $adminUser = Auth::guard('admin')->user() ?? Auth::user();
            $adminId = $adminUser ? $adminUser->id : null;
            $customer = $order->user;

            $existing = \App\Models\OrderCancellation::where('order_id', $order->id)->first();
            if ($existing) {
                // Preserve existing customer cancellation details
                if ($adminNotes) {
                    $existing->update(['admin_notes' => $adminNotes]);
                }
                return $existing;
            }

            $totalPaid = (float) $order->total_amount;
            $refundAmount = 0.00;
            $refundStatus = 'none';

            // If order was paid online or via wallet, issue full 100% refund (zero fee)
            if ($order->payment_status === 'paid' || $order->wallet_amount_used > 0) {
                $refundAmount = $totalPaid;
                if ($refundMethod === 'wallet') {
                    app(\App\Services\WalletService::class)->getOrCreateWallet($customer)->credit(
                        $refundAmount,
                        'ORDER_REFUND',
                        "Full Refund for Order #{$order->order_number} cancelled by store admin ({$reason})",
                        $order->id
                    );
                    $refundStatus = 'processed';
                } else {
                    $refundStatus = 'pending';
                }
            }

            // Record in order_cancellations table
            $cancellation = \App\Models\OrderCancellation::create([
                'order_id'            => $order->id,
                'user_id'             => $customer ? $customer->id : $order->user_id,
                'cancelled_by_type'   => 'admin',
                'cancelled_by_id'     => $adminId,
                'cancellation_reason' => $reason,
                'admin_notes'         => $adminNotes,
                'cancellation_fee'    => 0.00, // Zero fee for admin cancellations
                'refund_amount'       => $refundAmount,
                'refund_status'       => $refundStatus,
                'refund_method'       => $refundMethod,
            ]);

            // Restore product & option stocks
            $this->restoreOrderProductStocks($order);

            // Update order status
            $adminName = $adminUser ? $adminUser->name : 'Store Admin';
            $this->updateOrderStatus($order, 'cancelled', "Cancelled by Store Admin ({$adminName}): {$reason}" . ($adminNotes ? " - Notes: {$adminNotes}" : ''));

            return $cancellation;
        });
    }

    /**
     * Mark COD Cancellation Payment as Failed or Dropped.
     */
    public function recordCodCancellationPaymentFailure(Order $order, string $paymentReference, string $paymentStatus = 'failed'): \App\Models\OrderCancellation
    {
        $cancellation = \App\Models\OrderCancellation::where('order_id', $order->id)->first();
        if ($cancellation) {
            $cancellation->update([
                'payment_reference' => $paymentReference,
                'payment_status'    => $paymentStatus,
            ]);
        }
        return $cancellation ?? $this->initiateCodCancellationGstPayment($order, 'Cancellation GST payment ' . $paymentStatus, $paymentReference);
    }

    /**
     * Helper to restore product stocks and option stocks.
     */
    protected function restoreOrderProductStocks(Order $order): void
    {
        foreach ($order->items as $item) {
            $product = \App\Models\Product::find($item->product_id);
            if ($product) {
                $stockBefore = $product->stock;
                $stockAfter = $stockBefore + $item->quantity;
                $product->increment('stock', $item->quantity);

                // Restore option stock if selected
                if ($item->selected_option && !empty($product->option_stocks)) {
                    $stocks = is_array($product->option_stocks) ? $product->option_stocks : json_decode($product->option_stocks, true);
                    if (isset($stocks[$item->selected_option])) {
                        $stocks[$item->selected_option] = (int) $stocks[$item->selected_option] + $item->quantity;
                        $product->update(['option_stocks' => $stocks]);
                    }
                }

                // Log stock movement
                $order->stockMovements()->create([
                    'product_id'    => $product->id,
                    'movement_type' => \App\Enums\MovementType::ADJUSTMENT,
                    'source'        => \App\Enums\StockSource::ORDER,
                    'quantity'      => $item->quantity,
                    'stock_before'  => $stockBefore,
                    'stock_after'   => $stockAfter,
                    'notes'         => "Restocked due to cancellation of Order #{$order->order_number}.",
                    'created_by'    => Auth::id(),
                ]);
            }
        }
    }
}
