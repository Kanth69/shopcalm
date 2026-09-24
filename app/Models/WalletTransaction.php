<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'wallet_id',
        'user_id',
        'type',
        'amount',
        'balance_after',
        'source',
        'order_id',
        'description',
    ];

    protected $casts = [
        'amount' => 'float',
        'balance_after' => 'float',
    ];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Source display name helper.
     */
    public function getSourceBadgeAttribute(): array
    {
        return match ($this->source) {
            'SIGNUP_BONUS' => ['label' => 'Signup Bonus', 'class' => 'bg-info bg-opacity-15 text-info'],
            'REFERRAL_BONUS' => ['label' => 'Referral Reward', 'class' => 'bg-success bg-opacity-15 text-success'],
            'CHECKOUT_REDEEM' => ['label' => 'Order Payment', 'class' => 'bg-primary bg-opacity-15 text-primary'],
            'ADMIN_ADJUSTMENT' => ['label' => 'Admin Adjustment', 'class' => 'bg-secondary bg-opacity-15 text-secondary'],
            'ORDER_REFUND' => ['label' => 'Refund Credit', 'class' => 'bg-warning bg-opacity-15 text-warning'],
            default => ['label' => $this->source, 'class' => 'bg-light text-dark'],
        };
    }
}
