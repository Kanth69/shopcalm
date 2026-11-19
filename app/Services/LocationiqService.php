<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LocationiqService
{
    const CACHE_EXHAUSTED_PREFIX = 'locationiq_exhausted_';

    /**
     * Get all keys configured in the pool with full metadata.
     * Backwards-compatible: if pool is empty but legacy single key exists, migrates it.
     */
    public static function getAllKeys(): array
    {
        $raw = Setting::get('locationiq_api_keys');
        $pool = [];

        if (!empty($raw)) {
            if (is_array($raw)) {
                $pool = $raw;
            } elseif (is_string($raw)) {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    $pool = $decoded;
                }
            }
        }

        // Backwards compatibility: Check legacy single key
        $legacyKey = Setting::get('locationiq_api_key') ?: config('services.locationiq.key');
        if (empty($pool) && !empty($legacyKey)) {
            $pool = [
                [
                    'id'          => 'key_' . substr(md5($legacyKey), 0, 8),
                    'key'         => $legacyKey,
                    'label'       => 'Primary Key (Account 1)',
                    'is_active'   => true,
                    'added_at'    => now()->toDateTimeString(),
                    'last_status' => 'active',
                ]
            ];
            self::savePool($pool);
        }

        // Enrich with live transient state (e.g., temporary rate limit in cache)
        foreach ($pool as &$item) {
            $item['is_exhausted'] = Cache::has(self::CACHE_EXHAUSTED_PREFIX . md5($item['key']));
            $item['masked_key']   = self::maskKey($item['key']);
        }
        unset($item);

        return $pool;
    }

    /**
     * Get list of active, working keys (plain strings) for client/API rotation.
     * Prioritizes non-exhausted keys first.
     */
    public static function getActiveKeys(): array
    {
        $all = self::getAllKeys();
        $activeNonExhausted = [];
        $activeExhausted = [];

        foreach ($all as $item) {
            if (!empty($item['is_active']) && !empty($item['key'])) {
                if (!empty($item['is_exhausted'])) {
                    $activeExhausted[] = $item['key'];
                } else {
                    $activeNonExhausted[] = $item['key'];
                }
            }
        }

        // Return non-exhausted keys first, with exhausted keys at the end as fallback
        $combined = array_merge($activeNonExhausted, $activeExhausted);

        if (empty($combined)) {
            $fallback = config('services.locationiq.key');
            if (!empty($fallback)) {
                $combined[] = $fallback;
            }
        }

        return $combined;
    }

    /**
     * Get single best active key (round-robin / failover choice).
     */
    public static function getActiveKey(): ?string
    {
        $keys = self::getActiveKeys();
        if (empty($keys)) {
            return null;
        }

        // Simple round-robin counter to distribute load evenly across keys
        $cacheKey = 'locationiq_rr_index';
        $index = Cache::get($cacheKey, 0);
        $selectedKey = $keys[$index % count($keys)];

        Cache::put($cacheKey, ($index + 1) % count($keys), 86400);

        return $selectedKey;
    }

    /**
     * Add a new LocationIQ key to the pool after live API verification.
     */
    public static function addKey(string $key, ?string $label = null): array
    {
        $key = trim($key);

        if (empty($key)) {
            return ['success' => false, 'message' => 'API Token cannot be empty.'];
        }

        if (!str_starts_with($key, 'pk.')) {
            return ['success' => false, 'message' => 'Invalid format. LocationIQ public keys must start with "pk." (e.g. pk.xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx).'];
        }

        if (strlen($key) < 20) {
            return ['success' => false, 'message' => 'Token is too short to be a valid LocationIQ key.'];
        }

        $pool = self::getAllKeys();

        // Check if key already exists
        foreach ($pool as $existing) {
            if ($existing['key'] === $key) {
                return ['success' => false, 'message' => 'This LocationIQ key already exists in your pool.'];
            }
        }

        // Live validation against LocationIQ API
        $validation = self::validateToken($key);
        if (!$validation['valid']) {
            return ['success' => false, 'message' => 'LocationIQ rejected this token: ' . ($validation['error'] ?? 'Invalid token')];
        }

        $count = count($pool) + 1;
        $newEntry = [
            'id'          => 'key_' . substr(md5($key . microtime()), 0, 8),
            'key'         => $key,
            'label'       => !empty($label) ? trim($label) : "Key #{$count}",
            'is_active'   => true,
            'added_at'    => now()->toDateTimeString(),
            'last_status' => 'verified',
        ];

        $pool[] = $newEntry;
        self::savePool($pool);

        return [
            'success'      => true,
            'message'      => 'LocationIQ Key added and verified successfully!',
            'entry'        => $newEntry,
            'display_name' => $validation['display_name'] ?? 'LocationIQ Verified',
            'pool_count'   => count($pool),
        ];
    }

    /**
     * Remove a key from the pool.
     */
    public static function deleteKey(string $id): bool
    {
        $pool = self::getAllKeys();
        $updated = [];
        $found = false;

        foreach ($pool as $item) {
            if ($item['id'] === $id) {
                $found = true;
                continue;
            }
            $updated[] = $item;
        }

        if ($found) {
            self::savePool($updated);
            return true;
        }

        return false;
    }

    /**
     * Toggle active status of a key in the pool.
     */
    public static function toggleKey(string $id): array
    {
        $pool = self::getAllKeys();
        $found = false;
        $newState = false;

        foreach ($pool as &$item) {
            if ($item['id'] === $id) {
                $item['is_active'] = !($item['is_active'] ?? true);
                $newState = $item['is_active'];
                $found = true;
                break;
            }
        }
        unset($item);

        if ($found) {
            self::savePool($pool);
            return ['success' => true, 'is_active' => $newState];
        }

        return ['success' => false, 'message' => 'Key not found.'];
    }

    /**
     * Validate a token with live reverse-geocode against US and EU LocationIQ clusters.
     */
    public static function validateToken(string $token): array
    {
        $token = trim($token);

        if (empty($token) || !str_starts_with($token, 'pk.')) {
            return ['valid' => false, 'error' => 'Token must start with "pk."'];
        }

        $endpoints = [
            'https://us1.locationiq.com/v1/reverse',
            'https://eu1.locationiq.com/v1/reverse',
        ];

        $lastError = 'LocationIQ connection failed';

        foreach ($endpoints as $endpoint) {
            try {
                $response = Http::timeout(6)->get($endpoint, [
                    'key'             => $token,
                    'lat'             => 12.9716,
                    'lon'             => 77.5946,
                    'format'          => 'json',
                    'accept-language' => 'en',
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    return [
                        'valid'        => true,
                        'display_name' => $data['display_name'] ?? 'Bengaluru, Karnataka, India',
                    ];
                }

                $error = $response->json('error') ?? ('HTTP ' . $response->status() . ': ' . Str::limit($response->body(), 80));
                return [
                    'valid' => false,
                    'error' => $error,
                ];
            } catch (\Exception $e) {
                $lastError = $e->getMessage();
            }
        }

        return [
            'valid' => false,
            'error' => 'Connection timed out: ' . $lastError,
        ];
    }

    /**
     * Mark a key as exhausted (hit HTTP 429 or daily 5,000 limit) for a duration.
     */
    public static function markKeyExhausted(string $key, int $seconds = 3600): void
    {
        Cache::put(self::CACHE_EXHAUSTED_PREFIX . md5($key), true, $seconds);
        Log::warning("[LocationiqService] Key " . self::maskKey($key) . " marked exhausted for {$seconds}s due to quota limit.");
    }

    /**
     * Mask a key for secure display in UI (e.g. pk.8fcb...443f).
     */
    public static function maskKey(string $key): string
    {
        if (strlen($key) <= 12) {
            return $key;
        }
        return substr($key, 0, 7) . '••••••••' . substr($key, -4);
    }

    /**
     * Save the pool to Settings and maintain backward compatibility.
     */
    protected static function savePool(array $pool): void
    {
        // Clean out transient fields before saving
        $clean = [];
        $firstActiveKey = '';

        foreach ($pool as $item) {
            $entry = [
                'id'          => $item['id'] ?? ('key_' . substr(md5(($item['key'] ?? '') . microtime()), 0, 8)),
                'key'         => $item['key'] ?? '',
                'label'       => $item['label'] ?? 'LocationIQ Key',
                'is_active'   => !empty($item['is_active']),
                'added_at'    => $item['added_at'] ?? now()->toDateTimeString(),
                'last_status' => $item['last_status'] ?? 'active',
            ];
            $clean[] = $entry;

            if (empty($firstActiveKey) && $entry['is_active'] && !empty($entry['key'])) {
                $firstActiveKey = $entry['key'];
            }
        }

        Setting::set('locationiq_api_keys', json_encode($clean));

        // Sync legacy single key setting so any existing consumers continue to work seamlessly
        Setting::set('locationiq_api_key', $firstActiveKey);
    }
}
