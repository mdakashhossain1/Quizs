<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Firebase Cloud Messaging via the HTTP v1 API. No Firebase SDK dependency
 * is needed — just a service-account JSON, PHP's built-in openssl for the
 * JWT, and Laravel's Http client.
 *
 * Credentials aren't configured yet (see config('services.fcm')), so every
 * send currently logs what WOULD have been sent instead of calling the real
 * API. Nothing else needs to change once real credentials are supplied.
 */
class FcmService
{
    public static function isConfigured(): bool
    {
        return filled(config('services.fcm.project_id')) && self::credentials() !== null;
    }

    /**
     * The service-account JSON, decoded. Prefers FCM_CREDENTIALS_JSON (the
     * whole file inline in .env — shared hosting can't rely on a private
     * storage path surviving a deploy) and falls back to reading
     * FCM_CREDENTIALS_PATH from disk when the inline value isn't set.
     */
    private static function credentials(): ?array
    {
        $json = config('services.fcm.credentials_json');
        if (filled($json)) {
            $decoded = json_decode($json, true);
            return is_array($decoded) ? $decoded : null;
        }

        $path = config('services.fcm.credentials_path');
        if (filled($path) && is_string($path) && file_exists($path)) {
            $decoded = json_decode(file_get_contents($path), true);
            return is_array($decoded) ? $decoded : null;
        }

        return null;
    }

    /**
     * Sends to every device token registered for $user. Never throws — a
     * failed or unconfigured push must not break the caller's request.
     */
    public static function sendToUser(User $user, string $title, string $body, array $data = [], ?string $imageUrl = null): void
    {
        $tokens = $user->fcmTokens()->pluck('token');

        if ($tokens->isEmpty()) {
            return;
        }

        if (! self::isConfigured()) {
            Log::info('FCM not configured; logging notification instead of sending', [
                'user_id' => $user->id,
                'title' => $title,
                'body' => $body,
                'data' => $data,
                'image_url' => $imageUrl,
            ]);

            return;
        }

        foreach ($tokens as $token) {
            self::sendToToken($token, $title, $body, $data, $imageUrl);
        }
    }

    private static function sendToToken(string $token, string $title, string $body, array $data, ?string $imageUrl = null): void
    {
        try {
            $accessToken = self::accessToken();
            $projectId = config('services.fcm.project_id');

            $notification = ['title' => $title, 'body' => $body];
            if ($imageUrl) {
                $notification['image'] = $imageUrl;
            }

            $response = Http::withToken($accessToken)->post(
                "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send",
                [
                    'message' => [
                        'token' => $token,
                        'notification' => $notification,
                        'data' => array_map('strval', $data),
                    ],
                ],
            );

            if (! $response->successful()) {
                Log::warning('FCM send failed', ['status' => $response->status(), 'body' => $response->body()]);
            }
        } catch (\Throwable $e) {
            Log::warning('FCM send threw', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Exchanges the service-account key for a short-lived OAuth2 access
     * token (cached just under its 1-hour expiry) via a hand-rolled RS256
     * JWT — this is the entire "service account auth" flow, no library
     * needed for it.
     */
    private static function accessToken(): string
    {
        return Cache::remember('fcm_access_token', 3000, function () {
            $credentials = self::credentials() ?? throw new \RuntimeException('FCM credentials are not configured.');
            $result = self::requestAccessToken($credentials);

            return $result['token'] ?? throw new \RuntimeException(
                'Failed to obtain an FCM access token: '.$result['error']
            );
        });
    }

    /**
     * Exchanges an arbitrary (not necessarily the currently-configured)
     * service-account array for an access token, uncached — used by the
     * admin settings panel to verify a pasted Firebase JSON actually works
     * with Google before it's saved to .env.
     */
    public static function verifyCredentials(array $credentials): array
    {
        if (empty($credentials['client_email']) || empty($credentials['private_key'])) {
            return ['ok' => false, 'error' => 'Missing client_email or private_key.'];
        }

        $result = self::requestAccessToken($credentials);

        return $result['token']
            ? ['ok' => true, 'error' => null]
            : ['ok' => false, 'error' => $result['error']];
    }

    /**
     * @return array{token: ?string, error: ?string}
     */
    private static function requestAccessToken(array $credentials): array
    {
        try {
            $now = time();

            $jwt = self::signedJwt([
                'iss' => $credentials['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud' => 'https://oauth2.googleapis.com/token',
                'iat' => $now,
                'exp' => $now + 3600,
            ], $credentials['private_key']);

            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

            $token = $response->json('access_token');

            return $token
                ? ['token' => $token, 'error' => null]
                : ['token' => null, 'error' => $response->json('error_description') ?? $response->body()];
        } catch (\Throwable $e) {
            return ['token' => null, 'error' => $e->getMessage()];
        }
    }

    private static function signedJwt(array $claims, string $privateKey): string
    {
        $header = self::base64UrlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $payload = self::base64UrlEncode(json_encode($claims));
        $signingInput = "{$header}.{$payload}";

        openssl_sign($signingInput, $signature, $privateKey, 'sha256WithRSAEncryption');

        return $signingInput . '.' . self::base64UrlEncode($signature);
    }

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
