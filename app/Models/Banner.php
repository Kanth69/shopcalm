<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Banner extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'offer_id',
        'banner_type',
        'bg_gradient',
        'title',
        'subtitle',
        'desktop_image',
        'mobile_image',
        'primary_button_text',
        'primary_button_link',
        'display_order',
        'is_active',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope for active banners.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
