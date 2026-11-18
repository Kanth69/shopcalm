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
        'campaign_type',
        'target_gender',
        'target_age_group',
        'target_activity',
        'target_language',
        'recipient_count',
        'status',
        'rejection_reason',
        'approved_by',
        'approved_at',
        'sent_by',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'destination_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'Pending_Approval';
    }

    public function isApproved(): bool
    {
        return $this->status === 'Approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'Rejected';
    }
}
