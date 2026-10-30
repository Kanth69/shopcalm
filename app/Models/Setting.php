<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * List of sensitive keys that are encrypted at rest in the database.
     */
    protected static array $encryptedKeys = [
        'whatsapp_api_token',
        'razorpay_key_secret',
        'brevo_api_key',
        'locationiq_api_key',
    ];

    /**
     * Runtime request-level in-memory cache.
     */
    protected static ?array $runtimeCache = null;

    protected static function booted(): void
    {
        static::saved(function () {
            self::clearCache();
        });

        static::deleted(function () {
            self::clearCache();
        });
    }

    /**
     * Clear all settings caches (both runtime and store).
     */
    public static function clearCache(): void
    {
        self::$runtimeCache = null;
        \Illuminate\Support\Facades\Cache::forget('app_settings');
        \App\Http\Controllers\Api\V1\HomeController::clearCache();
    }

    /**
     * Accessor: Auto-decrypt sensitive credentials so they are returned as original plain text.
     * In the database, they remain safely encrypted.
     *
     * @param string|null $value
     * @return string|null
     */
    public function getValueAttribute($value)
    {
        if ($value !== null && $value !== '' && in_array($this->attributes['key'] ?? null, self::$encryptedKeys)) {
            try {
                return Crypt::decryptString($value);
            } catch (\Throwable $e) {
                // If already plain text or decryption fails, return as-is
                return $value;
            }
        }

        return $value;
    }

    /**
     * Get a setting value by key (cached for high production speed).
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        $all = self::getAll();
        if (array_key_exists($key, $all) && $all[$key] !== null && $all[$key] !== '') {
            return $all[$key];
        }

        return $default;
    }

    /**
     * Set a setting value by key (auto-encrypts sensitive API secrets in the database).
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public static function set(string $key, $value)
    {
        if ($value !== null && $value !== '' && in_array($key, self::$encryptedKeys)) {
            // Check if already encrypted to prevent double-encryption
            $alreadyEncrypted = false;
            try {
                Crypt::decryptString($value);
                $alreadyEncrypted = true;
            } catch (\Throwable $e) {
                $alreadyEncrypted = false;
            }

            if (!$alreadyEncrypted) {
                try {
                    $value = Crypt::encryptString($value);
                } catch (\Throwable $e) {
                    // Fallback
                }
            }
        }

        // Store raw encrypted value directly in database
        $record = self::firstOrNew(['key' => $key]);
        $record->setRawAttributes(array_merge($record->getAttributes(), [
            'key' => $key,
            'value' => $value,
        ]));
        $record->save();

        self::clearCache();
    }

    /**
     * Get all settings as key-value array with encrypted keys automatically decrypted.
     *
     * @return array<string, mixed>
     */
    public static function getAll(): array
    {
        if (self::$runtimeCache !== null) {
            return self::$runtimeCache;
        }

        self::$runtimeCache = \Illuminate\Support\Facades\Cache::remember('app_settings', 86400, function () {
            return self::all()->pluck('value', 'key')->toArray();
        });

        return self::$runtimeCache;
    }
}
