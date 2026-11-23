<?php

namespace App\Enums;

/**
 * ShopCalm Payment Method Enum
 * 
 * Provides type-safe enum cases, bulletproof normalization of legacy strings,
 * and high-level helper methods for fleet & cashier operations.
 */
enum PaymentMethod: string
{
    case COD          = 'cod';
    case PREPAID      = 'prepaid';
    case ONLINE       = 'online';
    case DOORSTEP_UPI = 'doorstep_upi';
    case WALLET       = 'wallet';
    case CASH         = 'cash';

    /**
     * Get human-friendly display label.
     */
    public function label(): string
    {
        return match ($this) {
            self::COD          => 'Cash on Delivery (COD)',
            self::PREPAID      => 'Prepaid Online',
            self::ONLINE       => 'Online Payment',
            self::DOORSTEP_UPI => 'Doorstep Dynamic UPI',
            self::WALLET       => 'ShopCalm Wallet',
            self::CASH         => 'Counter Cash',
        };
    }

    /**
     * Check if payment requires physical cash handover from rider.
     */
    public function isCashInHand(): bool
    {
        return $this === self::COD || $this === self::CASH;
    }

    /**
     * Check if order is Doorstep UPI QR.
     */
    public function isDoorstepUpi(): bool
    {
        return $this === self::DOORSTEP_UPI;
    }

    /**
     * Check if order was prepaid before dispatch.
     */
    public function isPrepaid(): bool
    {
        return in_array($this, [self::PREPAID, self::ONLINE, self::WALLET], true);
    }

    /**
     * Safe factory that never throws on legacy strings or nulls.
     */
    public static function fromSafe(?string $value): self
    {
        if (empty($value)) {
            return self::PREPAID;
        }

        $normalized = strtolower(trim($value));

        return match ($normalized) {
            'cod', 'cash'                      => self::COD,
            'doorstep_upi', 'doorstep upi'     => self::DOORSTEP_UPI,
            'wallet', 'store_wallet'           => self::WALLET,
            'online', 'razorpay'               => self::ONLINE,
            default                            => self::PREPAID,
        };
    }
}
