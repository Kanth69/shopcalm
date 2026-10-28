<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'phone',
        'house_no',
        'street',
        'address',
        'city',
        'state',
        'zip',
        'country',
        'latitude',
        'longitude',
        'landmark',
        'type',
    ];

    protected static function booted()
    {
        static::saving(function ($address) {
            // Auto-generate unified single-line address if not explicitly set
            if (empty($address->address) && (!empty($address->house_no) || !empty($address->street))) {
                $parts = array_filter([$address->house_no, $address->street]);
                $address->address = implode(', ', $parts);
            }
        });
    }

    public function getFormattedAddressAttribute(): string
    {
        $parts = array_filter([
            $this->house_no,
            $this->street,
            $this->landmark ? "(Near {$this->landmark})" : null,
        ]);
        if (!empty($parts)) {
            return implode(', ', $parts);
        }
        return $this->address ?? '';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
