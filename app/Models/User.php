<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Role Mappings:
     * 1 = Super Admin
     * 2 = Admin
     * 3 = User (Customer)
     */
    const ROLE_SUPER_ADMIN = 1;
    const ROLE_ADMIN = 2;
    const ROLE_CUSTOMER = 3;
    const ROLE_PRODUCT_MANAGER = 4;
    const ROLE_ORDER_MANAGER = 5;
    const ROLE_SUPPORT = 6;
    const ROLE_DELIVERY_PARTNER = 7;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'mobile_number',
        'mobile_verified_at',
        'email',
        'password',
        'avatar',
        'role_id',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'mobile_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isMobileVerified(): bool
    {
        return !is_null($this->mobile_verified_at);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function isSuperAdmin(): bool
    {
        return (int) $this->role_id === self::ROLE_SUPER_ADMIN;
    }

    public function isAdmin(): bool
    {
        return in_array((int) $this->role_id, [self::ROLE_SUPER_ADMIN, self::ROLE_ADMIN]);
    }

    public function isProductManager(): bool
    {
        return (int) $this->role_id === self::ROLE_PRODUCT_MANAGER;
    }

    public function isOrderManager(): bool
    {
        return (int) $this->role_id === self::ROLE_ORDER_MANAGER;
    }

    public function isCustomerSupport(): bool
    {
        return (int) $this->role_id === self::ROLE_SUPPORT;
    }

    public function isDeliveryPartner(): bool
    {
        return (int) $this->role_id === self::ROLE_DELIVERY_PARTNER;
    }

    public function isCustomer(): bool
    {
        return (int) $this->role_id === self::ROLE_CUSTOMER;
    }

    public function isStaff(): bool
    {
        return in_array((int) $this->role_id, [
            self::ROLE_SUPER_ADMIN,
            self::ROLE_ADMIN,
            self::ROLE_PRODUCT_MANAGER,
            self::ROLE_ORDER_MANAGER,
            self::ROLE_SUPPORT,
            self::ROLE_DELIVERY_PARTNER,
        ]);
    }

    public function canAccessProductManagerPortal(): bool
    {
        return in_array((int) $this->role_id, [self::ROLE_SUPER_ADMIN, self::ROLE_PRODUCT_MANAGER]);
    }

    public function canAccessOrderManagerPortal(): bool
    {
        return in_array((int) $this->role_id, [self::ROLE_SUPER_ADMIN, self::ROLE_ORDER_MANAGER]);
    }

    public function canAccessDeliveryPortal(): bool
    {
        return in_array((int) $this->role_id, [self::ROLE_SUPER_ADMIN, self::ROLE_DELIVERY_PARTNER]);
    }

    public function canAccessSupportPortal(): bool
    {
        return in_array((int) $this->role_id, [self::ROLE_SUPER_ADMIN, self::ROLE_SUPPORT]);
    }

    public function canAccessAdminPortal(): bool
    {
        return in_array((int) $this->role_id, [self::ROLE_SUPER_ADMIN, self::ROLE_ADMIN]);
    }

    public function getRoleNameAttribute(): string
    {
        return match((int) $this->role_id) {
            self::ROLE_SUPER_ADMIN     => 'Super Admin',
            self::ROLE_ADMIN           => 'Admin',
            self::ROLE_PRODUCT_MANAGER => 'Product Manager',
            self::ROLE_ORDER_MANAGER   => 'Order Manager',
            self::ROLE_SUPPORT         => 'Customer Support',
            default                    => 'Customer',
        };
    }

    public function getWishlistedProductIds()
    {
        return $this->wishlist ? $this->wishlist->items->pluck('product_id')->toArray() : [];
    }

    public function wishlist(): HasOne
    {
        return $this->hasOne(Wishlist::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class);
    }

    public function couponUsages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function profile(): HasOne
    {
        return $this->hasOne(CustomerProfile::class);
    }

    public function interests(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'customer_interest_map');
    }

    public function assignedDeliveries(): HasMany
    {
        return $this->hasMany(OrderFulfillment::class, 'rider_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function scopeDeliveryPartners($query)
    {
        return $query->where('role_id', self::ROLE_DELIVERY_PARTNER);
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    public function walletTransactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function referredUsers(): HasMany
    {
        return $this->hasMany(Wallet::class, 'referred_by', 'id');
    }
}
