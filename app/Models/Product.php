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
        'cost_price',
        'price',
        'stock',
        'featured',
        'trending',
        'status',
        'rejection_reason',
        'submitted_by',
        'main_image',
        'weight',
        'length',
        'width',
        'height',
        'has_options',
        'option_type',
        'option_stocks',
        'hsn_code',
        'tax_rate',
    ];

    protected $casts = [
        'featured'      => 'boolean',
        'trending'      => 'boolean',
        'has_options'   => 'boolean',
        'option_stocks' => 'array',
        'cost_price'    => 'decimal:2',
        'price'         => 'decimal:2',
        'weight'        => 'decimal:2',
        'length'        => 'decimal:2',
        'width'         => 'decimal:2',
        'height'        => 'decimal:2',
        'tax_rate'      => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function ($product) {
            if (empty($product->slug) && !empty($product->name)) {
                $slug = \Illuminate\Support\Str::slug($product->name);
                $originalSlug = $slug;
                $count = 1;
                while (static::where('slug', $slug)->exists()) {
                    $slug = "{$originalSlug}-{$count}";
                    $count++;
                }
                $product->slug = $slug;
            }
        });
    }

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

    public function getWeightKg(): float
    {
        return (float) ($this->weight > 0 ? $this->weight : 0.50);
    }

    public function getDimensionsCm(): array
    {
        return [
            'length' => (float) ($this->length > 0 ? $this->length : 15.00),
            'width'  => (float) ($this->width > 0 ? $this->width : 10.00),
            'height' => (float) ($this->height > 0 ? $this->height : 5.00),
        ];
    }

    public function getOptionStock(?string $optionKey): int
    {
        if (!$this->has_options || empty($optionKey) || empty($this->option_stocks)) {
            return (int) $this->stock;
        }

        $stocks = is_array($this->option_stocks) ? $this->option_stocks : json_decode($this->option_stocks, true);
        return (int) ($stocks[$optionKey] ?? 0);
    }

    public function reduceOptionStock(?string $optionKey, int $quantity = 1): void
    {
        if ($this->has_options && !empty($optionKey) && !empty($this->option_stocks)) {
            $stocks = is_array($this->option_stocks) ? $this->option_stocks : json_decode($this->option_stocks, true);
            if (isset($stocks[$optionKey])) {
                $stocks[$optionKey] = max(0, ((int) $stocks[$optionKey]) - $quantity);
                $this->option_stocks = $stocks;
            }
        }
        $this->decrement('stock', $quantity);
    }
}
