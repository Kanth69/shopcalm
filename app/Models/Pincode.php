<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Pincode extends Model
{
    use HasFactory;

    protected $fillable = [
        'pincode',
        'city',
        'state',
        'delivery_days',
        'is_cod_available',
        'delivery_charge',
        'cod_fee',
        'is_serviceable',
    ];

    protected $casts = [
        'delivery_days' => 'integer',
        'is_cod_available' => 'boolean',
        'delivery_charge' => 'decimal:2',
        'cod_fee' => 'decimal:2',
        'is_serviceable' => 'boolean',
    ];

    public function scopeServiceable($query)
    {
        return $query->where('is_serviceable', true);
    }

    public function scopeCodAvailable($query)
    {
        return $query->where('is_serviceable', true)->where('is_cod_available', true);
    }

    /**
     * Calculate estimated delivery date excluding Sundays
     */
    public function getEstimatedDeliveryDate(): Carbon
    {
        $days = max(1, $this->delivery_days ?? 3);
        $date = Carbon::now();

        while ($days > 0) {
            $date->addDay();
            // Skip Sunday
            if ($date->dayOfWeek !== Carbon::SUNDAY) {
                $days--;
            }
        }

        return $date;
    }

    /**
     * Format delivery date string (e.g. "Thursday, 27 Aug")
     */
    public function getEstimatedDeliveryText(): string
    {
        $date = $this->getEstimatedDeliveryDate();
        return $date->format('l, d M');
    }
}
