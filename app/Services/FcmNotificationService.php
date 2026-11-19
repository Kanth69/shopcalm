<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserDevice;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmNotificationService
{
    /**
     * Get the active Firebase Project ID and Service Account Credentials.
     */
    protected function getCredentials(): ?array
    {
        // 1. Check database setting (stored via Admin Panel)
        $dbCreds = Setting::get('firebase_service_account_json');
        if (!empty($dbCreds)) {
            $decoded = json_decode($dbCreds, true);
            if (is_array($decoded) && !empty($decoded['project_id']) && !empty($decoded['private_key'])) {
                return $decoded;
            }
        }

        // 2. Check local file (storage/app/firebase-service-account.json)
        $filePath = storage_path('app/firebase-service-account.json');
        if (file_exists($filePath)) {
            $content = file_get_contents($filePath);
            $decoded = json_decode($content, true);
            if (is_array($decoded) && !empty($decoded['project_id']) && !empty($decoded['private_key'])) {
                return $decoded;
            }
        }

        // 3. Check environment variables
        $envJson = env('FIREBASE_SERVICE_ACCOUNT_JSON');
        if (!empty($envJson)) {
            $decoded = json_decode($envJson, true);
            if (is_array($decoded) && !empty($decoded['project_id']) && !empty($decoded['private_key'])) {
                return $decoded;
            }
        }

        return null;
    }

    /**
     * Generate or fetch cached OAuth2 Access Token for FCM HTTP v1 API.
     */
    public function getAccessToken(): ?string
    {
        $creds = $this->getCredentials();
        if (!$creds) {
            return null;
        }

        $cacheKey = 'fcm_v1_access_token_' . md5($creds['client_email']);

        return Cache::remember($cacheKey, 3500, function () use ($creds) {
            try {
                $now = time();
                $header = base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
                $claimSet = base64_encode(json_encode([
                    'iss'   => $creds['client_email'],
                    'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                    'aud'   => 'https://oauth2.googleapis.com/token',
                    'exp'   => $now + 3600,
                    'iat'   => $now,
                ]));

                $signatureInput = $header . '.' . $claimSet;
                $privateKey = openssl_pkey_get_private($creds['private_key']);
                if (!$privateKey) {
                    Log::error('[FCM] Invalid private key in Firebase service account.');
                    return null;
                }

                $signature = '';
                openssl_sign($signatureInput, $signature, $privateKey, OPENSSL_ALGO_SHA256);
                $jwt = $signatureInput . '.' . str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

                $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                    'assertion'  => $jwt,
                ]);

                if ($response->successful()) {
                    return $response->json('access_token');
                }

                Log::error('[FCM] OAuth2 token exchange failed: ' . $response->body());
                return null;
            } catch (\Throwable $e) {
                Log::error('[FCM] Error generating access token: ' . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * Send a notification to a specific device FCM token.
     */
    public function sendToToken(string $token, string $title, string $body, array $data = [], ?string $imageUrl = null): bool
    {
        $creds = $this->getCredentials();
        if (!$creds) {
            Log::info("[FCM] Firebase credentials not configured. Notification '{$title}' not sent.");
            return false;
        }

        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            Log::error("[FCM] Failed to acquire OAuth2 access token for project {$creds['project_id']}");
            return false;
        }

        $projectId = $creds['project_id'];
        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

        // Convert all data values to strings as required by FCM
        $stringData = [];
        foreach ($data as $k => $v) {
            $stringData[(string) $k] = is_array($v) ? json_encode($v) : (string) $v;
        }

        $notificationData = [
            'title' => $title,
            'body'  => $body,
        ];

        $androidNotification = [
            'channel_id'   => 'shopcalm_orders_channel',
            'sound'        => 'default',
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
        ];

        if (!empty($imageUrl)) {
            $notificationData['image'] = $imageUrl;
            $androidNotification['image'] = $imageUrl;
        }

        $payload = [
            'message' => [
                'token' => $token,
                'notification' => $notificationData,
                'data' => $stringData,
                'android' => [
                    'priority' => 'HIGH',
                    'notification' => $androidNotification,
                ],
            ],
        ];

        try {
            $res = Http::withToken($accessToken)
                ->timeout(5)
                ->post($url, $payload);

            if ($res->successful()) {
                Log::info("[FCM] Notification dispatched successfully: {$title}");
                return true;
            }

            // Handle invalid/expired tokens (unregister them)
            $responseBody = $res->body();
            if (str_contains($responseBody, 'UNREGISTERED') || str_contains($responseBody, 'NOT_FOUND')) {
                UserDevice::where('fcm_token', $token)->update(['is_active' => false]);
                Log::info("[FCM] Marked invalid device token as inactive.");
            } else {
                Log::warning("[FCM] Send failed ({$res->status()}): {$responseBody}");
            }

            return false;
        } catch (\Throwable $e) {
            Log::error("[FCM] Exception while sending: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send notification to all active devices of a given User.
     */
    public function sendToUser(User $user, string $title, string $body, array $data = []): int
    {
        $devices = UserDevice::where('user_id', $user->id)
            ->where('is_active', true)
            ->get();

        $sentCount = 0;

        foreach ($devices as $device) {
            if ($this->sendToToken($device->fcm_token, $title, $body, $data)) {
                $sentCount++;
            }
        }

        // Fallback to user.fcm_token if user_devices was empty
        if ($devices->isEmpty() && !empty($user->fcm_token)) {
            if ($this->sendToToken($user->fcm_token, $title, $body, $data)) {
                $sentCount++;
            }
        }

        return $sentCount;
    }

    /**
     * Send Real-Time Order Lifecycle Notification.
     */
    public function sendOrderNotification(Order $order, string $status): bool
    {
        try {
            $marketingService = app(MarketingAutomationService::class);
            return $marketingService->triggerOrderEvent($order, $status);
        } catch (\Throwable $e) {
            Log::error("[FCM] Failed in automated order notification: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send Abandoned Cart Nudge Notification.
     */
    public function sendCartNudge(User $user, int $itemCount): bool
    {
        $firstName = explode(' ', trim($user->name))[0] ?? 'there';
        $title = "🛒 {$firstName}, you left items in your cart!";
        $body = "Complete your order now before your {$itemCount} item(s) sell out!";

        $data = [
            'type'   => 'cart_nudge',
            'screen' => 'cart',
        ];

        $sent = $this->sendToUser($user, $title, $body, $data) > 0;

        if ($sent) {
            UserDevice::where('user_id', $user->id)->update([
                'last_cart_nudge_at' => now(),
            ]);
        }

        return $sent;
    }
}
