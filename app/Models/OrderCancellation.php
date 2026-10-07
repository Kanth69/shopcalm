<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderCancellation extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'cancelled_by_type',
        'cancelled_by_id',
        'cancellation_reason',
        'admin_notes',
        'cancellation_fee',
        'wallet_refund_amount',
        'online_refund_amount',
        'refund_amount',
        'refund_status',
        'online_refund_status',
        'refund_method',
        'refund_upi_id',
        'payment_reference',
        'razorpay_refund_id',
        'payment_status',
    ];

    protected $casts = [
        'cancellation_fee'     => 'decimal:2',
        'wallet_refund_amount' => 'decimal:2',
        'online_refund_amount' => 'decimal:2',
        'refund_amount'        => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by_id');
    }
}
