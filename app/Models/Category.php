<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'image',
        'description',
        'status',
    ];

    protected static function booted(): void
    {
        static::creating(function ($category) {
            if (empty($category->slug) && !empty($category->name)) {
                $slug = Str::slug($category->name);
                $originalSlug = $slug;
                $count = 1;
                while (static::where('slug', $slug)->exists()) {
                    $slug = "{$originalSlug}-{$count}";
                    $count++;
                }
                $category->slug = $slug;
            }
        });

        static::saved(function () {
            \Illuminate\Support\Facades\Cache::forget('home_categories');
            \Illuminate\Support\Facades\Cache::forget('all_categories');
        });

        static::deleted(function () {
            \Illuminate\Support\Facades\Cache::forget('home_categories');
            \Illuminate\Support\Facades\Cache::forget('all_categories');
        });
    }

    public function brands(): HasMany
    {
        return $this->hasMany(Brand::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
