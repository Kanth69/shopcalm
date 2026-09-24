<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class CashSettlement extends Model
{
    use HasFactory;

    protected $fillable = [
        'settlement_number',
        'rider_id',
        'received_by',
        'total_amount',
        'order_count',
        'payment_mode',
        'notes',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'order_count'  => 'integer',
    ];

    public function rider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rider_id');
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function fulfillments(): HasMany
    {
        return $this->hasMany(OrderFulfillment::class, 'cod_settlement_id');
    }

    /**
     * Generate next settlement voucher number.
     */
    public static function generateSettlementNumber(): string
    {
        $todayPrefix = 'SETT-' . date('Ymd') . '-';
        $latest = static::where('settlement_number', 'like', $todayPrefix . '%')
            ->latest('id')
            ->first();

        if ($latest) {
            $lastNum = (int) substr($latest->settlement_number, strlen($todayPrefix));
            $nextNum = str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNum = '0001';
        }

        return $todayPrefix . $nextNum;
    }
}
