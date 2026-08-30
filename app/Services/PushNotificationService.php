<?php

namespace App\Services;

use App\Models\UserDevice;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class PushNotificationService
{
    /**
     * Get or Generate VAPID Keys for Web / PWA Push
     */
    public static function getVapidKeys(): array
    {
        $publicKey = config('services.vapid.public_key') ?: env('VAPID_PUBLIC_KEY');
        $privateKey = config('services.vapid.private_key') ?: env('VAPID_PRIVATE_KEY');
        $subject = config('services.vapid.subject') ?: env('VAPID_SUBJECT', config('app.url', 'http://127.0.0.1:8000'));

        return [
            'publicKey' => $publicKey,
            'privateKey' => $privateKey,
            'subject' => $subject,
        ];
    }

    /**
     * Check if Push Notification keys are configured.
     */
    public static function isConfigured(): bool
    {
        $vapid = self::getVapidKeys();
        return !empty($vapid['publicKey']) && !empty($vapid['privateKey']);
    }

    /**
     * Dispatch notification to all active devices of a user
     */
    public static function sendToUser(int $userId, array $payload): array
    {
        $devices = UserDevice::where('user_id', $userId)
            ->where('is_active', true)
            ->get();

        $results = [
            'total' => $devices->count(),
            'sent' => 0,
            'failed' => 0,
            'details' => []
        ];

        foreach ($devices as $device) {
            $status = self::sendToDevice($device, $payload);
            if ($status['success']) {
                $results['sent']++;
            } else {
                $results['failed']++;
            }
            $results['details'][] = $status;
        }

        return $results;
    }

    /**
     * Dispatch notification to a specific UserDevice
     */
    public static function sendToDevice(UserDevice $device, array $payload): array
    {
        $defaultUrl = function_exists('url') ? url('/panel') : '/panel';
        $targetUrl = !empty($payload['url']) ? $payload['url'] : $defaultUrl;

        $formattedPayload = [
            'title' => $payload['title'] ?? 'FileFusion Alert',
            'body'  => $payload['body'] ?? '',
            'icon'  => $payload['icon'] ?? '/favicon.ico',
            'badge' => $payload['badge'] ?? '/favicon.ico',
            'tag'   => $payload['tag'] ?? 'filefusion_notification',
            'channelId' => $payload['channelId'] ?? 'filefusion_high_priority_v2',
            'sound' => 'default',
            'url'   => $targetUrl,
            'data'  => array_merge($payload['data'] ?? [], [
                'url' => $targetUrl,
                'channelId' => $payload['channelId'] ?? 'filefusion_high_priority_v2',
                'sound' => 'default'
            ]),
            'id'    => $payload['id'] ?? time()
        ];

        // 1. Dispatch via VAPID Web Push (Web / PWA / Apple iOS)
        if ($device->push_type === 'vapid' && $device->endpoint) {
            return self::sendVapidPush($device, $formattedPayload);
        }

        // 2. Dispatch via Firebase Cloud Messaging (Android Native App)
        if ($device->push_type === 'fcm' && $device->push_token) {
            return self::sendFcmPush($device, $formattedPayload);
        }

        return [
            'success' => false,
            'device_id' => $device->id,
            'device_name' => $device->device_name,
            'error' => 'Unsupported push_type or missing push token'
        ];
    }

    /**
     * Send Web Push notification via VAPID
     */
    protected static function sendVapidPush(UserDevice $device, array $payload): array
    {
        try {
            // Windows OpenSSL config path resolution for EC Key creation
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' && !getenv('OPENSSL_CONF')) {
                $possiblePaths = [
                    'C:/wamp64/bin/apache/apache2.4.58/bin/openssl.cnf',
                    'C:/wamp64/bin/php/php' . PHP_VERSION . '/extras/ssl/openssl.cnf',
                    'C:/php/extras/ssl/openssl.cnf',
                    'C:/xampp/apache/bin/openssl.cnf',
                ];
                foreach ($possiblePaths as $path) {
                    if (file_exists($path)) {
                        putenv("OPENSSL_CONF={$path}");
                        break;
                    }
                }
            }

            $vapid = self::getVapidKeys();
            if (!$vapid['publicKey'] || !$vapid['privateKey']) {
                return [
                    'success' => false,
                    'device_id' => $device->id,
                    'device_name' => $device->device_name,
                    'error' => 'VAPID keys not configured on server'
                ];
            }

            $subscription = Subscription::create([
                'endpoint' => $device->endpoint,
                'publicKey' => $device->public_key,
                'authToken' => $device->auth_token,
            ]);

            $auth = [
                'VAPID' => [
                    'subject' => $vapid['subject'],
                    'publicKey' => $vapid['publicKey'],
                    'privateKey' => $vapid['privateKey'],
                ],
            ];

            $defaultOptions = [
                'TTL' => 3600,
                'urgency' => 'high',
            ];

            $clientOptions = [
                'verify' => false,
                'timeout' => 15,
            ];

            $webPush = new WebPush($auth, $defaultOptions, 15, $clientOptions);
            $report = $webPush->sendOneNotification(
                $subscription,
                json_encode($payload),
                ['TTL' => 3600, 'urgency' => 'high']
            );

            if ($report->isSuccess()) {
                $device->update(['last_active_at' => now(), 'is_active' => true]);
                return [
                    'success' => true,
                    'device_id' => $device->id,
                    'device_name' => $device->device_name,
                    'mode' => 'vapid'
                ];
            }

            if ($report->isSubscriptionExpired()) {
                $device->update(['is_active' => false]);
            }

            return [
                'success' => false,
                'device_id' => $device->id,
                'device_name' => $device->device_name,
                'error' => $report->getReason() ?: 'Push service returned non-200 response'
            ];
        } catch (\Throwable $e) {
            Log::error('[PushNotificationService] VAPID Error: ' . $e->getMessage());
            return [
                'success' => false,
                'device_id' => $device->id,
                'device_name' => $device->device_name,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send Native Android push notification via FCM (HTTP v1 & Legacy)
     */
    protected static function sendFcmPush(UserDevice $device, array $payload): array
    {
        // 1. Try FCM HTTP v1 API with Service Account JSON
        $candidatePaths = array_filter([
            config('services.firebase.credentials_file'),
            storage_path('app/firebase/firebase_credentials.json'),
            storage_path('app/private/firebase/firebase_credentials.json'),
            base_path('storage/app/firebase/firebase_credentials.json'),
            base_path('storage/app/private/firebase/firebase_credentials.json'),
            __DIR__ . '/../../storage/app/firebase/firebase_credentials.json',
        ]);

        foreach ($candidatePaths as $serviceAccountPath) {
            if (file_exists($serviceAccountPath)) {
                $content = @file_get_contents($serviceAccountPath);
                if ($content) {
                    $serviceAccount = json_decode($content, true);
                    if (!empty($serviceAccount['project_id']) && !empty($serviceAccount['private_key']) && !str_contains($serviceAccount['private_key'], 'REPLACE_WITH_YOUR_ACTUAL_PRIVATE_KEY')) {
                        return self::sendFcmHttpV1($device, $payload, $serviceAccount);
                    }
                }
            }
        }

        // 2. Fallback to Legacy Server Key if set
        $fcmServerKey = config('services.fcm.server_key') ?: env('FCM_SERVER_KEY');
        if ($fcmServerKey) {
            return self::sendFcmLegacy($device, $payload, $fcmServerKey);
        }

        Log::warning('[PushNotificationService] Firebase credentials not found in checked paths: ' . json_encode($candidatePaths));

        return [
            'success' => false,
            'device_id' => $device->id,
            'device_name' => $device->device_name,
            'error' => 'Firebase credentials not configured in storage/app/firebase/firebase_credentials.json'
        ];
    }

    /**
     * Google FCM HTTP v1 Dispatch with OAuth2 Bearer Token
     */
    protected static function sendFcmHttpV1(UserDevice $device, array $payload, array $serviceAccount): array
    {
        try {
            $accessToken = self::getGoogleAccessToken($serviceAccount);
            if (!$accessToken) {
                return [
                    'success' => false,
                    'device_id' => $device->id,
                    'device_name' => $device->device_name,
                    'error' => 'Failed to generate Google OAuth2 Access Token'
                ];
            }

            $projectId = $serviceAccount['project_id'];
            $endpoint = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

            $channelId = (string) ($payload['channelId'] ?? 'filefusion_default_channel');

            $message = [
                'token' => $device->push_token,
                'notification' => [
                    'title' => (string) $payload['title'],
                    'body'  => (string) $payload['body'],
                ],
                'android' => [
                    'priority' => 'high',
                    'notification' => [
                        'icon' => 'ic_stat_filefusion',
                        'channel_id' => $channelId,
                        'sound' => 'default',
                        'default_sound' => true,
                        'default_vibrate_timings' => true,
                        'notification_priority' => 'PRIORITY_HIGH',
                        'click_action' => (string) ($payload['url'] ?? (function_exists('url') ? url('/panel') : '/panel'))
                    ]
                ]
            ];

            if (!empty($payload['data']) && is_array($payload['data'])) {
                $message['data'] = array_map('strval', $payload['data']);
            }

            $response = Http::withoutVerifying()
                ->withToken($accessToken)
                ->post($endpoint, ['message' => $message]);

            if ($response->successful()) {
                $device->update(['last_active_at' => now(), 'is_active' => true]);
                return [
                    'success' => true,
                    'device_id' => $device->id,
                    'device_name' => $device->device_name,
                    'mode' => 'fcm_v1'
                ];
            }

            return [
                'success' => false,
                'device_id' => $device->id,
                'device_name' => $device->device_name,
                'error' => $response->body()
            ];
        } catch (\Throwable $e) {
            Log::error('[PushNotificationService] FCM v1 Error: ' . $e->getMessage());
            return [
                'success' => false,
                'device_id' => $device->id,
                'device_name' => $device->device_name,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Legacy FCM Send
     */
    protected static function sendFcmLegacy(UserDevice $device, array $payload, string $fcmServerKey): array
    {
        try {
            $channelId = (string) ($payload['channelId'] ?? 'filefusion_default_channel');

            $response = Http::withoutVerifying()->withHeaders([
                'Authorization' => 'key=' . $fcmServerKey,
                'Content-Type'  => 'application/json',
            ])->post('https://fcm.googleapis.com/fcm/send', [
                'to' => $device->push_token,
                'notification' => [
                    'title' => $payload['title'],
                    'body'  => $payload['body'],
                    'icon'  => 'ic_stat_filefusion',
                    'sound' => 'default',
                    'android_channel_id' => $channelId,
                    'click_action' => $payload['url'],
                ],
                'data' => $payload['data'] ?? [],
                'priority' => 'high'
            ]);

            if ($response->successful()) {
                $device->update(['last_active_at' => now(), 'is_active' => true]);
                return [
                    'success' => true,
                    'device_id' => $device->id,
                    'device_name' => $device->device_name,
                    'mode' => 'fcm_legacy'
                ];
            }

            return [
                'success' => false,
                'device_id' => $device->id,
                'device_name' => $device->device_name,
                'error' => $response->body()
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'device_id' => $device->id,
                'device_name' => $device->device_name,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Generate & Cache Google OAuth2 Access Token for Firebase Admin
     */
    protected static function getGoogleAccessToken(array $sa): ?string
    {
        $cacheKey = 'fcm_v1_access_token_' . md5($sa['client_email']);

        return Cache::remember($cacheKey, 3300, function () use ($sa) {
            $now = time();
            $header = base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
            $claim = base64_encode(json_encode([
                'iss' => $sa['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud' => $sa['token_uri'] ?? 'https://oauth2.googleapis.com/token',
                'exp' => $now + 3600,
                'iat' => $now,
            ]));

            $signingInput = "{$header}.{$claim}";
            $signature = '';

            $privateKey = openssl_pkey_get_private($sa['private_key']);
            if (!$privateKey) {
                Log::error('[PushNotificationService] Invalid private key in Firebase JSON');
                return null;
            }

            openssl_sign($signingInput, $signature, $privateKey, OPENSSL_ALGO_SHA256);
            $jwt = $signingInput . '.' . rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');

            $res = Http::withoutVerifying()->asForm()->post($sa['token_uri'] ?? 'https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

            if ($res->successful()) {
                return $res->json()['access_token'] ?? null;
            }

            Log::error('[PushNotificationService] OAuth2 Token Error: ' . $res->body());
            return null;
        });
    }
}
