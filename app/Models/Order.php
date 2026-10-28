<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Order extends Model
{
    use HasFactory;

    /**
     * Order statuses that count toward realized store income, revenue, sales, and profit
     * (from Confirmed through Delivered; excludes pending, failed, cancelled, returned).
     */
    public const INCOME_STATUSES = [
        'confirmed',
        'processing',
        'packed',
        'shipped',
        'out for delivery',
        'out_for_delivery',
        'delivered',
    ];

    protected $fillable = [
        'user_id',
        'order_number',
        'subtotal_amount',
        'shipping_charge',
        'coupon_id',
        'coupon_discount_amount',
        'wallet_amount_used',
        'cod_fee',
        'tax_amount',
        'total_amount',
        'status',
        'payment_method',
        'payment_status',
        'shipping_name',
        'shipping_email',
        'shipping_phone',
        'shipping_house_no',
        'shipping_street',
        'shipping_address',
        'shipping_city',
        'shipping_state',
        'shipping_zip',
        'shipping_country',
        'shipping_latitude',
        'shipping_longitude',
        'shipping_landmark',
        'notes',
    ];

    protected $casts = [
        'subtotal_amount' => 'decimal:2',
        'shipping_charge' => 'decimal:2',
        'coupon_discount_amount' => 'decimal:2',
        'wallet_amount_used' => 'decimal:2',
        'cod_fee' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'order_number';
    }

    /**
     * Retrieve the model for a bound value (resolves by id OR order_number).
     */
    public function resolveRouteBinding($value, $field = null)
    {
        if (is_numeric($value)) {
            return $this->where('id', $value)->orWhere('order_number', $value)->first();
        }

        return $this->where('order_number', $value)->first();
    }

    /**
     * Normalize empty/null status (caused by legacy MySQL enum coercion of 'failed') to 'pending'.
     */
    public function getStatusAttribute($value): string
    {
        if ($value === null || trim((string) $value) === '') {
            return 'pending';
        }

        return (string) $value;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function walletTransaction(): HasOne
    {
        return $this->hasOne(WalletTransaction::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function fulfillment(): HasOne
    {
        return $this->hasOne(OrderFulfillment::class);
    }

    public function feedback(): HasOne
    {
        return $this->hasOne(OrderFeedback::class);
    }

    public function cancellation(): HasOne
    {
        return $this->hasOne(OrderCancellation::class);
    }

    public function rider()
    {
        return $this->hasOneThrough(User::class, OrderFulfillment::class, 'order_id', 'id', 'id', 'rider_id');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function stockMovements(): MorphMany
    {
        return $this->morphMany(StockMovement::class, 'reference');
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function latestPayment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function primaryPayment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    /**
     * Check if this order is fulfilled in Bengaluru via local fleet.
     */
    public function isLocalBengaluruDelivery(): bool
    {
        if ($this->relationLoaded('fulfillment') && $this->fulfillment) {
            return $this->fulfillment->isLocal();
        }

        $pincode = trim($this->shipping_zip ?? '');
        $city = strtolower(trim($this->shipping_city ?? ''));

        return str_starts_with($pincode, '560') || str_contains($city, 'bengaluru') || str_contains($city, 'bangalore');
    }

    /**
     * Get HTML formatted Zone Badge for table rows.
     */
    public function getFulfillmentBadgeAttribute(): string
    {
        if ($this->fulfillment) {
            return $this->fulfillment->zone_badge;
        }

        return $this->isLocalBengaluruDelivery()
            ? '<span class="badge rounded-pill px-2.5 py-1 fw-bold shadow-xs d-inline-flex align-items-center gap-1" style="background: rgba(14, 165, 233, 0.15); color: #0284c7; border: 1px solid rgba(14, 165, 233, 0.3); font-size: 0.72rem;"><i class="bi bi-geo-fill"></i> Bengaluru Local</span>'
            : '<span class="badge rounded-pill px-2.5 py-1 fw-bold shadow-xs d-inline-flex align-items-center gap-1" style="background: rgba(99, 102, 241, 0.15); color: #4f46e5; border: 1px solid rgba(99, 102, 241, 0.3); font-size: 0.72rem;"><i class="bi bi-truck"></i> National Courier</span>';
    }

    // ── Backward-compatible proxies for logistics & tracking ──
    public function getCourierPartnerAttribute()
    {
        return $this->fulfillment?->carrier_name;
    }

    public function getTrackingNumberAttribute()
    {
        return $this->fulfillment?->tracking_number;
    }

    public function getTrackingUrlAttribute()
    {
        return $this->fulfillment?->tracking_url;
    }

    public function getRiderNameAttribute(): ?string
    {
        return $this->fulfillment?->rider_name;
    }

    public function getRiderPhoneAttribute(): ?string
    {
        return $this->fulfillment?->rider_phone;
    }

    public function getRiderIdAttribute(): ?int
    {
        return $this->fulfillment?->rider_id;
    }

    public function getDeliveryOtpAttribute(): ?string
    {
        $otp = $this->fulfillment?->delivery_otp;

        // Auto-provision 4-digit handover OTP if missing on active orders
        if (!$otp && !in_array(strtolower((string)$this->status), ['cancelled', 'failed'])) {
            $otp = (string) random_int(1000, 9999);
            try {
                if ($this->relationLoaded('fulfillment') && $this->fulfillment) {
                    $this->fulfillment->update(['delivery_otp' => $otp]);
                } else {
                    $fulfillment = $this->fulfillment()->firstOrCreate(
                        ['order_id' => $this->id],
                        [
                            'type'         => $this->isLocalBengaluruDelivery() ? 'local_fleet' : 'courier',
                            'status'       => 'pending',
                            'delivery_otp' => $otp,
                        ]
                    );
                    if (!$fulfillment->delivery_otp) {
                        $fulfillment->update(['delivery_otp' => $otp]);
                    }
                    $this->load('fulfillment');
                }
            } catch (\Throwable $e) {
                // Silently fallback to the generated OTP in memory
            }
        }

        return $otp;
    }

    public function getShippedAtAttribute()
    {
        return $this->fulfillment?->dispatched_at;
    }

    public function getDeliveredAtAttribute()
    {
        return $this->fulfillment?->delivered_at;
    }

    public function getRiderAttribute(): ?User
    {
        return $this->fulfillment?->rider;
    }

    public function getDeliverySlotAttribute(): ?string
    {
        return $this->fulfillment?->delivery_slot;
    }

    public function getTotalCostAttribute(): float
    {
        return (float) $this->items->sum(function ($item) {
            return ($item->product?->cost_price ?? 0) * $item->quantity;
        });
    }

    public function getGrossProfitAttribute(): float
    {
        if (!in_array($this->status, self::INCOME_STATUSES)) {
            return 0.0;
        }

        return (float) ($this->total_amount - $this->total_cost);
    }

    public function getProfitMarginAttribute(): float
    {
        if (!in_array($this->status, self::INCOME_STATUSES) || $this->total_amount <= 0) {
            return 0.0;
        }

        return round(($this->gross_profit / $this->total_amount) * 100, 1);
    }

    /**
     * Get user-friendly formatted title of the payment mode.
     */
    public function getPaymentMethodDisplayAttribute(): string
    {
        $method = strtolower($this->payment_method ?? 'cod');
        $isPaid = ($this->payment_status === 'paid');
        $walletUsed = (float) ($this->wallet_amount_used ?? 0);

        if ($method === 'wallet' || ($this->total_amount <= 0 && $walletUsed > 0)) {
            return 'ShopCalm Wallet (100% Paid)';
        }

        if ($walletUsed > 0) {
            $base = match($method) {
                'doorstep_upi' => 'Doorstep UPI (Razorpay QR)',
                'online'       => 'Prepaid Online (Razorpay)',
                default        => 'Cash on Delivery',
            };
            return "ShopCalm Wallet (₹" . number_format($walletUsed, 2) . ") + {$base}";
        }

        if ($method === 'doorstep_upi') {
            return 'Doorstep UPI (Razorpay QR)';
        }

        if ($method === 'online') {
            $sub = $this->primaryPayment?->method_display;
            return $sub ? "Prepaid Online ({$sub})" : 'Prepaid Online (Razorpay)';
        }

        if ($method === 'cod') {
            return $isPaid ? 'Cash on Delivery (Paid in Cash)' : 'Cash on Delivery (Pay on Delivery)';
        }

        if ($method === 'cash') {
            return 'Cash Paid at Doorstep';
        }

        return strtoupper($method);
    }

    /**
     * Get HTML formatted payment method badge with icon.
     */
    public function getPaymentMethodBadgeAttribute(): string
    {
        $method = strtolower($this->payment_method ?? 'cod');
        $isPaid = ($this->payment_status === 'paid');

        return match($method) {
            'doorstep_upi' => '<span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-2.5 py-1 fw-bold"><i class="bi bi-qr-code-scan me-1"></i> Doorstep UPI</span>',
            'online'       => '<span class="badge rounded-pill bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1 fw-bold"><i class="bi bi-shield-check me-1"></i> Prepaid Online</span>',
            'wallet'       => '<span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-2.5 py-1 fw-bold"><i class="bi bi-wallet2 me-1"></i> ShopCalm Wallet</span>',
            'cod'          => $isPaid 
                ? '<span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-2.5 py-1 fw-bold"><i class="bi bi-cash-stack me-1"></i> COD (Paid)</span>'
                : '<span class="badge rounded-pill bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2.5 py-1 fw-bold"><i class="bi bi-cash me-1"></i> Pay on Delivery</span>',
            default        => '<span class="badge bg-light text-dark border px-2.5 py-1 fw-bold">' . strtoupper($method) . '</span>',
        };
    }
}
