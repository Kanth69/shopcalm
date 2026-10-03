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
        'shipping_address',
        'shipping_city',
        'shipping_state',
        'shipping_zip',
        'shipping_country',
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
        return $this->hasOne(Payment::class)->ofMany([], function ($query) {
            $query->whereIn('status', ['SUCCESS', 'PAID', 'PENDING'])->orderByDesc('id');
        });
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
        return $this->fulfillment?->delivery_otp;
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
        return (float) ($this->total_amount - $this->total_cost);
    }

    public function getProfitMarginAttribute(): float
    {
        return $this->total_amount > 0 
            ? round(($this->gross_profit / $this->total_amount) * 100, 1) 
            : 0.0;
    }
}
