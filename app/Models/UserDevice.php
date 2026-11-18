<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'device_id',
        'fcm_token',
        'device_type',
        'app_version',
        'last_active_at',
        'last_cart_nudge_at',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_active_at' => 'datetime',
        'last_cart_nudge_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope audience query by gender, age bracket, dormant activity, and regional language/pincode.
     */
    public function scopeTargetAudience($query, ?string $gender = 'all', ?string $ageGroup = 'all', ?string $activity = 'all', ?string $language = 'all')
    {
        $query->where('is_active', true)->whereNotNull('fcm_token');

        if ($gender && $gender !== 'all') {
            $query->whereHas('user.profile', fn($q) => $q->where('gender', $gender));
        }

        if ($ageGroup === 'birthday_month') {
            $query->whereHas('user.profile', fn($q) => $q->whereRaw('MONTH(date_of_birth) = ?', [now()->month]));
        } elseif ($ageGroup === 'under_25') {
            $query->whereHas('user.profile', fn($q) => $q->where('date_of_birth', '>', now()->subYears(25)));
        } elseif ($ageGroup === '25_40') {
            $query->whereHas('user.profile', fn($q) => $q->whereBetween('date_of_birth', [now()->subYears(40), now()->subYears(25)]));
        } elseif ($ageGroup === 'over_40') {
            $query->whereHas('user.profile', fn($q) => $q->where('date_of_birth', '<', now()->subYears(40)));
        }

        if ($activity === 'dormant') {
            $query->whereHas('user', fn($q) => $q->whereDoesntHave('orders', fn($oq) => $oq->where('created_at', '>=', now()->subDays(30))));
        }

        if ($language && $language !== 'all') {
            $query->where(function ($lq) use ($language) {
                // 1. Explicit preferred language in user profile
                $lq->whereHas('user.profile', fn($pq) => $pq->where('preferred_language', $language));

                // 2. Regional Pincodes & States fallback
                if ($language === 'te') {
                    $lq->orWhereHas('user.addresses', function ($aq) {
                        $aq->whereIn('state', ['Andhra Pradesh', 'Telangana', 'AP', 'TS'])
                           ->orWhere('zip', 'LIKE', '50%')
                           ->orWhere('zip', 'LIKE', '51%')
                           ->orWhere('zip', 'LIKE', '52%')
                           ->orWhere('zip', 'LIKE', '53%');
                    });
                } elseif ($language === 'hi') {
                    $lq->orWhereHas('user.addresses', function ($aq) {
                        $aq->whereIn('state', ['Delhi', 'Uttar Pradesh', 'Rajasthan', 'Madhya Pradesh', 'Bihar', 'Haryana', 'UP', 'MP'])
                           ->orWhere('zip', 'LIKE', '11%')
                           ->orWhere('zip', 'LIKE', '20%')
                           ->orWhere('zip', 'LIKE', '22%')
                           ->orWhere('zip', 'LIKE', '30%')
                           ->orWhere('zip', 'LIKE', '46%')
                           ->orWhere('zip', 'LIKE', '80%');
                    });
                } elseif ($language === 'ta') {
                    $lq->orWhereHas('user.addresses', function ($aq) {
                        $aq->where('state', 'Tamil Nadu')
                           ->orWhere('zip', 'LIKE', '60%')
                           ->orWhere('zip', 'LIKE', '61%')
                           ->orWhere('zip', 'LIKE', '62%')
                           ->orWhere('zip', 'LIKE', '63%')
                           ->orWhere('zip', 'LIKE', '64%');
                    });
                } elseif ($language === 'kn') {
                    $lq->orWhereHas('user.addresses', function ($aq) {
                        $aq->where('state', 'Karnataka')
                           ->orWhere('zip', 'LIKE', '56%')
                           ->orWhere('zip', 'LIKE', '57%')
                           ->orWhere('zip', 'LIKE', '58%')
                           ->orWhere('zip', 'LIKE', '59%');
                    });
                }
            });
        }

        return $query;
    }
}
