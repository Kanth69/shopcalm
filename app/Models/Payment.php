<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'order_number',
        'gateway',
        'gateway_order_id',
        'gateway_payment_id',
        'payment_session_id',
        'amount',
        'currency',
        'status',
        'payment_method_group',
        'payment_method_details',
        'bank_reference',
        'payment_time',
        'gateway_message',
        'raw_response',
    ];

    protected $casts = [
        'amount'                 => 'decimal:2',
        'payment_method_details' => 'array',
        'raw_response'           => 'array',
        'payment_time'           => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Human-readable formatted payment method label.
     */
    public function getMethodDisplayAttribute(): string
    {
        $group = strtolower($this->payment_method_group ?? '');
        $details = $this->payment_method_details ?? [];

        return match ($group) {
            'upi'        => 'UPI' . (isset($details['upi']['channel']) ? ' (' . ucfirst($details['upi']['channel']) . ')' : '') . (isset($details['upi']['upi_id']) ? ' - ' . $details['upi']['upi_id'] : ''),
            'card'       => (isset($details['card']['card_network']) ? ucfirst($details['card']['card_network']) . ' ' : '') . 'Card' . (isset($details['card']['card_number']) ? ' •••• ' . substr($details['card']['card_number'], -4) : ''),
            'netbanking' => 'NetBanking' . (isset($details['netbanking']['bank_name']) ? ' (' . $details['netbanking']['bank_name'] . ')' : ''),
            'wallet'     => 'Wallet' . (isset($details['wallet']['provider']) ? ' (' . ucfirst($details['wallet']['provider']) . ')' : ''),
            'cod'        => 'Cash on Delivery (COD)',
            default      => ucfirst($this->gateway ?? 'Online'),
        };
    }

    /**
     * HTML Status badge for tables and detail views.
     */
    public function getStatusBadgeAttribute(): string
    {
        $status = strtoupper($this->status ?? 'PENDING');

        return match ($status) {
            'SUCCESS', 'PAID' => '<span class="badge rounded-pill px-2.5 py-1 fw-bold shadow-xs d-inline-flex align-items-center gap-1" style="background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; font-size: 0.75rem;"><i class="bi bi-check-circle-fill"></i> Paid</span>',
            'PENDING'         => '<span class="badge rounded-pill px-2.5 py-1 fw-bold shadow-xs d-inline-flex align-items-center gap-1" style="background: #fffbeb; color: #b45309; border: 1px solid #fde68a; font-size: 0.75rem;"><i class="bi bi-clock-history"></i> Pending</span>',
            'FAILED'          => '<span class="badge rounded-pill px-2.5 py-1 fw-bold shadow-xs d-inline-flex align-items-center gap-1" style="background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; font-size: 0.75rem;"><i class="bi bi-x-circle-fill"></i> Failed</span>',
            'USER_DROPPED'    => '<span class="badge rounded-pill px-2.5 py-1 fw-bold shadow-xs d-inline-flex align-items-center gap-1" style="background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; font-size: 0.75rem;"><i class="bi bi-dash-circle"></i> Cancelled by User</span>',
            default           => '<span class="badge bg-secondary rounded-pill px-2.5 py-1 font-semibold">' . $status . '</span>',
        };
    }
}
