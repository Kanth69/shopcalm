<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Exception;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'balance',
        'total_earned',
        'referral_code',
        'referred_by',
        'status',
    ];

    protected $casts = [
        'balance' => 'float',
        'total_earned' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class)->latest();
    }

    /**
     * Credit amount to wallet.
     */
    public function credit(float $amount, string $source, string $description, ?int $orderId = null): WalletTransaction
    {
        if ($amount <= 0) {
            throw new Exception("Credit amount must be greater than zero.");
        }

        $this->balance += $amount;
        $this->total_earned += $amount;
        $this->save();

        return $this->transactions()->create([
            'user_id' => $this->user_id,
            'type' => 'CREDIT',
            'amount' => $amount,
            'balance_after' => $this->balance,
            'source' => $source,
            'order_id' => $orderId,
            'description' => $description,
        ]);
    }

    /**
     * Debit amount from wallet.
     */
    public function debit(float $amount, string $source, string $description, ?int $orderId = null): WalletTransaction
    {
        if ($amount <= 0) {
            throw new Exception("Debit amount must be greater than zero.");
        }

        if ($this->balance < $amount) {
            throw new Exception("Insufficient wallet balance.");
        }

        $this->balance -= $amount;
        $this->save();

        return $this->transactions()->create([
            'user_id' => $this->user_id,
            'type' => 'DEBIT',
            'amount' => $amount,
            'balance_after' => $this->balance,
            'source' => $source,
            'order_id' => $orderId,
            'description' => $description,
        ]);
    }

    /**
     * Generate unique referral code for user (e.g. SHOP8492).
     */
    public static function generateReferralCode(User $user): string
    {
        $prefix = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $user->name ?? 'WISE'), 0, 4));
        if (strlen($prefix) < 3) {
            $prefix = 'WISE';
        }

        do {
            $code = $prefix . rand(1000, 9999);
        } while (static::where('referral_code', $code)->exists());

        return $code;
    }
}
