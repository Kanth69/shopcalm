<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderFeedback extends Model
{
    use HasFactory;

    protected $table = 'order_feedbacks';

    protected $fillable = [
        'order_id',
        'user_id',
        'rider_id',
        'delivery_rating',
        'product_rating',
        'tags',
        'comment',
    ];

    protected $casts = [
        'delivery_rating' => 'integer',
        'product_rating'  => 'integer',
        'tags'            => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rider_id');
    }
}
