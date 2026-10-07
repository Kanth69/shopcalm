<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'image',
    ];

    protected $appends = [
        'image_url',
    ];

    public function getImagePathAttribute(): ?string
    {
        return $this->image;
    }

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            return null;
        }
        return str_starts_with($this->image, 'http') ? $this->image : asset('storage/' . $this->image);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
