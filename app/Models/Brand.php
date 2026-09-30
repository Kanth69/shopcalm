<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Brand extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'logo',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function ($brand) {
            if (empty($brand->slug) && !empty($brand->name)) {
                $slug = Str::slug($brand->name);
                $originalSlug = $slug;
                $count = 1;
                while (static::where('slug', $slug)->exists()) {
                    $slug = "{$originalSlug}-{$count}";
                    $count++;
                }
                $brand->slug = $slug;
            }
        });

        static::saved(function () {
            \Illuminate\Support\Facades\Cache::forget('home_brands');
            \Illuminate\Support\Facades\Cache::forget('all_brands');
        });

        static::deleted(function () {
            \Illuminate\Support\Facades\Cache::forget('home_brands');
            \Illuminate\Support\Facades\Cache::forget('all_brands');
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
