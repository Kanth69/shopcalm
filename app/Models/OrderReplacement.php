<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderReplacement extends Model
{
    use HasFactory;

    protected $fillable = [
        'replacement_number',
        'order_id',
        'order_item_id',
        'product_id',
        'user_id',
        'reason',
        'reason_label',
        'customer_notes',
        'proof_images',
        'status',
        'pickup_date',
        'rider_id',
        'rider_name',
        'rider_phone',
        'reviewed_by',
        'order_manager_notes',
        'rejection_reason',
        'reviewed_at',
        'picked_up_at',
        'completed_at',
    ];

    protected $casts = [
        'proof_images' => 'array',
        'pickup_date'  => 'date',
        'reviewed_at'  => 'datetime',
        'picked_up_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Standard Valid Replacement Reasons
     */
    public const VALID_REASONS = [
        'physical_damage' => [
            'label'           => 'Physical Damage / Broken Item',
            'photo_mandatory' => true,
            'description'     => 'Item received with physical crack, breakage, or damaged packaging seal.',
        ],
        'defective_not_working' => [
            'label'           => 'Manufacturing Defect / Product Not Working',
            'photo_mandatory' => false,
            'description'     => 'Product does not power on, function as expected, or has manufacturing defect.',
        ],
        'wrong_item' => [
            'label'           => 'Wrong Item Delivered / Mismatch with Order',
            'photo_mandatory' => false,
            'description'     => 'Item received is different from what was ordered (wrong model, variant, or size).',
        ],
        'missing_parts' => [
            'label'           => 'Missing Accessories / Parts Inside Box',
            'photo_mandatory' => false,
            'description'     => 'Box seal was intact or open, but accessories, manual, or charger were missing.',
        ],
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rider_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public static function generateReplacementNumber(): string
    {
        return 'REP-' . strtoupper(substr(uniqid(), -6)) . rand(10, 99);
    }
}
