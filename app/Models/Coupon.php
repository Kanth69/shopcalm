<?php

namespace App\Models;

use App\Enums\CouponType;
use App\Enums\CouponApplicableType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Coupon extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code', 'name', 'description', 'discount_type', 'discount_value',
        'minimum_order_amount', 'maximum_discount_amount', 'usage_limit',
        'usage_limit_per_customer', 'used_count', 'valid_from', 'valid_until',
        'is_active', 'stackable', 'priority', 'applicable_type', 'applicable_id',
        'created_by', 'updated_by'
    ];

    protected $casts = [
        'discount_type' => CouponType::class,
        'applicable_type' => CouponApplicableType::class,
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'is_active' => 'boolean',
        'stackable' => 'boolean',
        'discount_value' => 'decimal:2',
        'minimum_order_amount' => 'decimal:2',
        'maximum_discount_amount' => 'decimal:2',
    ];

    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        // Check if start date is strictly in the future (allowing same day / timezone offsets)
        if ($this->valid_from) {
            if (now()->lt($this->valid_from) && !$this->valid_from->isToday()) {
                return false;
            }
        }

        // Check if expiry date has passed (include the full end of day)
        if ($this->valid_until) {
            if (now()->gt($this->valid_until->copy()->endOfDay())) {
                return false;
            }
        }

        if ($this->usage_limit && $this->used_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'coupon_category');
    }

    public function brands(): BelongsToMany
    {
        return $this->belongsToMany(Brand::class, 'coupon_brand');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'coupon_product');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'applicable_id');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'applicable_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'applicable_id');
    }
}
