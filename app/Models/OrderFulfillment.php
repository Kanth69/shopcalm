<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderFulfillment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'type',
        'status',
        'rider_id',
        'rider_name',
        'rider_phone',
        'delivery_slot',
        'delivery_otp',
        'delivery_issue',
        'delivery_issue_at',
        'delivery_issue_resolved_at',
        'delivery_issue_resolution',
        'cod_settlement_id',
        'cod_deposited_at',
        'carrier_code',
        'carrier_name',
        'tracking_number',
        'tracking_url',
        'shipping_label_url',
        'assigned_at',
        'dispatched_at',
        'delivered_at',
        'assigned_by',
        'notes',
    ];

    protected $casts = [
        'assigned_at'                => 'datetime',
        'dispatched_at'              => 'datetime',
        'delivered_at'               => 'datetime',
        'delivery_issue_at'          => 'datetime',
        'delivery_issue_resolved_at' => 'datetime',
        'cod_deposited_at'           => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function rider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rider_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function settlement(): BelongsTo
    {
        return $this->belongsTo(CashSettlement::class, 'cod_settlement_id');
    }

    /**
     * Check if this order is fulfilled via Bengaluru Local Fleet.
     */
    public function isLocal(): bool
    {
        return $this->type === 'local_fleet';
    }

    /**
     * Check if this order is fulfilled via Pan-India 3rd-party Courier.
     */
    public function isCourier(): bool
    {
        return $this->type === 'courier';
    }

    /**
     * Check if an active unresolved delivery exception exists.
     */
    public function hasActiveDeliveryIssue(): bool
    {
        return !empty($this->delivery_issue) && empty($this->delivery_issue_resolved_at);
    }

    /**
     * Scope for active unresolved exceptions.
     */
    public function scopeActiveExceptions(Builder $query): Builder
    {
        return $query->whereNotNull('delivery_issue')
                     ->whereNull('delivery_issue_resolved_at');
    }

    /**
     * Scope for resolved exceptions.
     */
    public function scopeResolvedExceptions(Builder $query): Builder
    {
        return $query->whereNotNull('delivery_issue')
                     ->whereNotNull('delivery_issue_resolved_at');
    }

    /**
     * Get user-friendly zone label.
     */
    public function getZoneLabelAttribute(): string
    {
        return $this->isLocal() ? 'Bengaluru Local Fleet' : 'National Courier';
    }

    /**
     * Get HTML formatted Zone Badge.
     */
    public function getZoneBadgeAttribute(): string
    {
        if ($this->isLocal()) {
            return '<span class="badge rounded-pill px-2.5 py-1 fw-bold shadow-xs d-inline-flex align-items-center gap-1" style="background: rgba(14, 165, 233, 0.15); color: #0284c7; border: 1px solid rgba(14, 165, 233, 0.3); font-size: 0.72rem;"><i class="bi bi-geo-fill"></i> Bengaluru Local</span>';
        }

        return '<span class="badge rounded-pill px-2.5 py-1 fw-bold shadow-xs d-inline-flex align-items-center gap-1" style="background: rgba(99, 102, 241, 0.15); color: #4f46e5; border: 1px solid rgba(99, 102, 241, 0.3); font-size: 0.72rem;"><i class="bi bi-truck"></i> National Courier</span>';
    }
}
