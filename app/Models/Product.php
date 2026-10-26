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
        'tags',
        'video_url',
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

        static::saved(function () {
            \Illuminate\Support\Facades\Cache::forget('featured_products');
            \Illuminate\Support\Facades\Cache::forget('trending_products');
            \Illuminate\Support\Facades\Cache::forget('latest_products');
            \Illuminate\Support\Facades\Cache::forget('home_explore_products_p1');
            \Illuminate\Support\Facades\Cache::forget('home_total_active_products');
            \Illuminate\Support\Facades\Cache::forget('search_product_words');
        });

        static::deleted(function () {
            \Illuminate\Support\Facades\Cache::forget('featured_products');
            \Illuminate\Support\Facades\Cache::forget('trending_products');
            \Illuminate\Support\Facades\Cache::forget('latest_products');
            \Illuminate\Support\Facades\Cache::forget('home_explore_products_p1');
            \Illuminate\Support\Facades\Cache::forget('home_total_active_products');
            \Illuminate\Support\Facades\Cache::forget('search_product_words');
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
        if ($this->relationLoaded('approvedReviews')) {
            $approved = $this->approvedReviews;
            return $approved->isEmpty() ? 0.0 : (float) round($approved->avg('rating'), 1);
        }
        return (float) ($this->reviews()->where('status', 'Approved')->avg('rating') ?? 0);
    }

    public function ratingPercentage(int $rating): float
    {
        if ($this->relationLoaded('approvedReviews')) {
            $total = $this->approvedReviews->count();
            if ($total === 0) {
                return 0;
            }
            $count = $this->approvedReviews->where('rating', $rating)->count();
            return ($count / $total) * 100;
        }

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
        if (!$this->has_options || empty($this->option_stocks)) {
            return (int) $this->stock;
        }

        if (empty($optionKey)) {
            return 0;
        }

        $stocks = is_array($this->option_stocks) ? $this->option_stocks : json_decode($this->option_stocks, true);
        return (int) ($stocks[$optionKey] ?? 0);
    }

    public function getOptionImage(?string $optionKey): ?string
    {
        if (!empty($optionKey) && !empty($this->option_images)) {
            $images = is_array($this->option_images) ? $this->option_images : json_decode($this->option_images, true);
            if (is_array($images)) {
                if (!empty($images[$optionKey])) {
                    return $images[$optionKey];
                }
                if (str_contains($optionKey, ' - ')) {
                    $colorPart = trim(explode(' - ', $optionKey)[0]);
                    if (!empty($images[$colorPart])) {
                        return $images[$colorPart];
                    }
                }
            }
        }

        return $this->main_image;
    }

    public function getYoutubeEmbedUrl(): ?string
    {
        if (empty($this->video_url)) {
            return null;
        }

        $raw = trim($this->video_url);

        // 1. If user entered an <iframe ... src="..."> embed snippet, extract the src URL
        if (preg_match('/<iframe[^>]*\s+src=["\']([^"\']+)["\']/i', $raw, $matches)) {
            $raw = trim($matches[1]);
        }

        // 2. If it's already an embed link (youtube.com/embed/ or youtube-nocookie.com/embed/)
        if (preg_match('/https?:\/\/(?:www\.)?(?:youtube\.com|youtube-nocookie\.com)\/embed\/([a-zA-Z0-9_\-]+)/i', $raw, $matches)) {
            $videoId = $matches[1];
            return 'https://www.youtube-nocookie.com/embed/' . $videoId . '?rel=0';
        }

        // 3. Standard links: youtu.be, watch?v=, shorts
        $videoId = null;
        if (preg_match('/youtu\.be\/([a-zA-Z0-9_\-]+)/i', $raw, $matches)) {
            $videoId = $matches[1];
        } elseif (preg_match('/[?&]v=([a-zA-Z0-9_\-]+)/i', $raw, $matches)) {
            $videoId = $matches[1];
        } elseif (preg_match('/youtube\.com\/shorts\/([a-zA-Z0-9_\-]+)/i', $raw, $matches)) {
            $videoId = $matches[1];
        }

        if ($videoId) {
            return 'https://www.youtube-nocookie.com/embed/' . $videoId . '?rel=0';
        }

        // 4. If it starts with http/https and looks like a direct embed URL, fallback to it
        if (preg_match('/^https?:\/\//i', $raw)) {
            return $raw;
        }

        return null;
    }

    public function getYoutubeWatchUrl(): string
    {
        $embedUrl = $this->getYoutubeEmbedUrl();
        if ($embedUrl && preg_match('/embed\/([a-zA-Z0-9_\-]+)/i', $embedUrl, $matches)) {
            return 'https://www.youtube.com/watch?v=' . $matches[1];
        }
        if (!empty($this->video_url) && preg_match('/^https?:\/\/[^\s]+/i', trim($this->video_url), $matches)) {
            return $matches[0];
        }
        return 'https://www.youtube.com';
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
