<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductRejectionReason extends Model
{
    use HasFactory;

    protected $table = 'product_rejection_reasons';

    protected $fillable = [
        'product_id',
        'rejected_by',
        'reason',
        'status', // 'active', 'resolved'
    ];

    /**
     * Get the product that was rejected.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user (Admin / Super Admin) who rejected the product.
     */
    public function rejector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }
}
