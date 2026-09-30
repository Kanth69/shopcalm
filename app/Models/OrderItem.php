<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'original_price',
        'offer_discount',
        'unit_price',
        'quantity',
        'total_price',
        'tax_rate',
        'tax_amount',
        'selected_option',
    ];

    protected $casts = [
        'original_price' => 'decimal:2',
        'offer_discount' => 'decimal:2',
        'unit_price'     => 'decimal:2',
        'total_price'    => 'decimal:2',
        'tax_rate'       => 'decimal:2',
        'tax_amount'     => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function getCostPriceAttribute(): float
    {
        return (float) ($this->product?->cost_price ?? 0);
    }

    public function getTotalCostAttribute(): float
    {
        return (float) ($this->cost_price * $this->quantity);
    }

    public function getProfitAttribute(): float
    {
        return (float) ($this->total_price - $this->total_cost);
    }

    public function getProfitMarginAttribute(): float
    {
        return $this->total_price > 0 
            ? round(($this->profit / $this->total_price) * 100, 1) 
            : 0.0;
    }
}
