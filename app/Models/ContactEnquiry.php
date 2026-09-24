<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactEnquiry extends Model
{
    protected $fillable = [
        'name',
        'email',
        'mobile',
        'subject',
        'message',
        'is_read',
        'status',
        'reply_notes',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'resolved_at' => 'datetime',
    ];

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
