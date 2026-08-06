<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Offer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'type',
        'badge_text',
        'description',
        'banner_image',
        'theme_color',
        'discount_type',
        'discount_value',
        'min_purchase_amount',
        'max_discount_amount',
        'stock_limit',
        'claimed_count',
        'start_time',
        'end_time',
        'is_active',
        'priority',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_active' => 'boolean',
        'discount_value' => 'decimal:2',
        'min_purchase_amount' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
    ];

    public function banners(): HasMany
    {
        return $this->hasMany(Banner::class);
    }

    public function targets(): HasMany
    {
        return $this->hasMany(OfferTarget::class);
    }

    public function isLive(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();

        if ($this->start_time && $now->lt($this->start_time) && !$this->start_time->isToday()) {
            return false;
        }

        if ($this->end_time && $now->gt($this->end_time->copy()->endOfDay())) {
            return false;
        }

        return true;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLive($query)
    {
        $now = now();
        return $query->where('is_active', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('start_time')->orWhere('start_time', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('end_time')->orWhere('end_time', '>=', $now->startOfDay());
            });
    }

    public function scopeMegaSale($query)
    {
        return $query->live()->where('type', 'MEGA_SALE');
    }

    public function scopeFlashDeals($query)
    {
        return $query->live()->where('type', 'FLASH_DEAL');
    }

    public function calculateDiscount(?float $amount): float
    {
        if (!$amount || $amount <= 0) {
            return 0.0;
        }

        if ($this->discount_type === 'PERCENTAGE') {
            $discount = ($amount * $this->discount_value) / 100;
            if ($this->max_discount_amount && $discount > $this->max_discount_amount) {
                $discount = $this->max_discount_amount;
            }
            return round($discount, 2);
        }

        return min((float) $this->discount_value, $amount);
    }
}
