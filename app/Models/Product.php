<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'brand_id',
        'product_type',
        'name',
        'slug',
        'sku',
        'short_description',
        'description',
        'price',
        'stock',
        'featured',
        'trending',
        'status',
        'rejection_reason',
        'submitted_by',
        'main_image',
    ];

    protected $casts = [
        'featured' => 'boolean',
        'trending' => 'boolean',
        'price' => 'decimal:2',
    ];

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function isPendingApproval(): bool
    {
        return $this->status === 'Pending_Approval';
    }

    public function isRejected(): bool
    {
        return $this->status === 'Rejected';
    }

    public function isActive(): bool
    {
        return $this->status === 'Active';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function galleryImages(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function approvedReviews(): HasMany
    {
        return $this->hasMany(ProductReview::class)->where('status', 'Approved');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function averageRating(): float
    {
        if (isset($this->avg_rating)) {
            return (float) $this->avg_rating;
        }
        if (isset($this->reviews_avg_rating)) {
            return (float) $this->reviews_avg_rating;
        }
        return (float) ($this->reviews()->where('status', 'Approved')->avg('rating') ?? 0);
    }

    public function ratingPercentage(int $rating): float
    {
        $total = $this->reviews()->where('status', 'Approved')->count();
        if ($total === 0) {
            return 0;
        }
        $count = $this->reviews()->where('status', 'Approved')->where('rating', $rating)->count();
        return ($count / $total) * 100;
    }

    public function rejectionReasons(): HasMany
    {
        return $this->hasMany(ProductRejectionReason::class)->latest();
    }

    public function latestRejectionReason(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ProductRejectionReason::class)->latestOfMany();
    }

    public function getActiveRejectionReasonAttribute(): ?string
    {
        if ($this->relationLoaded('latestRejectionReason') && $this->latestRejectionReason) {
            return $this->latestRejectionReason->reason;
        }
        return $this->latestRejectionReason()->value('reason') ?? $this->rejection_reason;
    }
}
