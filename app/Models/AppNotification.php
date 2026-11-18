<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppNotification extends Model
{
    protected $fillable = [
        'title',
        'body',
        'banner_image',
        'destination_type',
        'destination_id',
        'target_gender',
        'target_age_group',
        'target_activity',
        'recipient_count',
        'sent_by',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'destination_id');
    }
}
