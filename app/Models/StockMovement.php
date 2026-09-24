<?php

namespace App\Models;

use App\Enums\MovementType;
use App\Enums\StockSource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'movement_type',
        'source',
        'quantity',
        'stock_before',
        'stock_after',
        'reference_type',
        'reference_id',
        'notes',
        'created_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'movement_type' => MovementType::class,
            'source' => StockSource::class,
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the parent reference model (e.g. Order).
     */
    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Determine if this stock movement was triggered automatically by the system (e.g. Orders/Checkout/Returns).
     */
    public function isSystemMovement(): bool
    {
        $sourceVal = is_object($this->source) ? $this->source->value : $this->source;
        $typeVal   = is_object($this->movement_type) ? $this->movement_type->value : $this->movement_type;

        return $sourceVal === 'ORDER' || $typeVal === 'SALE' || $this->reference_type === Order::class || !$this->created_by;
    }

    /**
     * Get human-readable attribution label.
     */
    public function getUpdatedByLabelAttribute(): string
    {
        if ($this->isSystemMovement()) {
            return 'System';
        }

        if ($this->createdBy) {
            $role = $this->createdBy->role_name ?? ($this->createdBy->role->name ?? 'Staff');
            return "{$this->createdBy->name} ({$role})";
        }

        return 'System';
    }
}
