<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'user_id',
        'rating',
        'title',
        'review',
        'status',
        'is_verified_purchase',
    ];

    protected static function booted(): void
    {
        static::saved(function () {
            \Illuminate\Support\Facades\Cache::forget('home_testimonials');
            \Illuminate\Support\Facades\Cache::forget('featured_products');
            \Illuminate\Support\Facades\Cache::forget('trending_products');
            \Illuminate\Support\Facades\Cache::forget('latest_products');
        });

        static::deleted(function () {
            \Illuminate\Support\Facades\Cache::forget('home_testimonials');
            \Illuminate\Support\Facades\Cache::forget('featured_products');
            \Illuminate\Support\Facades\Cache::forget('trending_products');
            \Illuminate\Support\Facades\Cache::forget('latest_products');
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
