<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * 1Confirmed WhatsApp OTP sender (2FA).
 *
 * 1Confirmed has no permanent API key: a JWT is obtained from the login
 * endpoint (email + password) and expires. This service logs in, caches the
 * token, and re-logs-in automatically when the token is rejected.
 *
 * Docs: https://1confirmed.com/developers/api
 *   POST {base}/login    { email, password }              -> data.token (JWT)
 *   POST {base}/messages  (Bearer token)
 *     { template_id, template_account_flow_id, phone (no '+'),
 *       data:{otp_var:code}, sms_fallback, sms_fallback_text, sms_fallback_delay }
 *
 * Configure via config/services.php -> 'oneconfirmed' (env ONECONFIRMED_*).
 */
class OneConfirmedService
{
    private const CACHE_KEY = 'oneconfirmed.token';

    /** True when the integration is switched on and fully configured. */
    public function enabled(): bool
    {
        $c = config('services.oneconfirmed');

        return (bool) ($c['enabled'] ?? false)
            && filled($c['email'] ?? null)
            && filled($c['password'] ?? null)
            && filled($c['template_id'] ?? null)
            && filled($c['template_account_flow_id'] ?? null);
    }

    /**
     * Send a WhatsApp OTP. $phone may include a leading '+' / spaces — it is
     * normalised to digits only (country code included, no '+') as the API expects.
     *
     * @return bool whether the request was accepted by 1Confirmed
     */
    public function sendOtp(string $phone, string|int $code): bool
    {
        $c = config('services.oneconfirmed');
        $phone = preg_replace('/[^0-9]/', '', $phone);

        $payload = [
            'template_id' => (int) $c['template_id'],
            'template_account_flow_id' => (int) $c['template_account_flow_id'],
            'phone' => $phone,
            'name' => 'OTP',
            // template 132 requires otp + otp_app + otp_reference (all non-empty).
            'data' => [
                ($c['otp_variable'] ?? 'otp') => (string) $code,
                'otp_app' => (string) ($c['otp_app'] ?? 'USED'),
                'otp_reference' => (string) ($c['otp_reference'] ?? 'USED'),
            ],
            'sms_fallback' => (bool) ($c['sms_fallback'] ?? true),
            'sms_fallback_text' => "Votre code de vérification est : {$code}",
            'sms_fallback_delay' => (int) ($c['sms_fallback_delay'] ?? 90),
        ];

        // Try with the cached token; if it is rejected as expired/invalid,
        // force a fresh login once and retry.
        for ($attempt = 0; $attempt < 2; $attempt++) {
            $token = $this->token(forceRefresh: $attempt === 1);
            if (! $token) {
                return false;
            }

            try {
                $response = Http::withToken($token)
                    ->acceptJson()
                    ->asJson()
                    ->timeout(15)
                    ->post($this->base().'/messages', $payload);

                if ($response->successful()) {
                    Log::info('1Confirmed OTP sent', ['phone' => substr($phone, 0, 5).'…']);

                    return true;
                }

                // Stale/invalid token — drop it and retry with a fresh login.
                if (in_array($response->status(), [401, 403], true) && $attempt === 0) {
                    Cache::forget(self::CACHE_KEY);

                    continue;
                }

                Log::error('1Confirmed OTP failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return false;
            } catch (\Throwable $e) {
                Log::error('1Confirmed OTP exception: '.$e->getMessage());

                return false;
            }
        }

        return false;
    }

    /** Return a JWT from cache, logging in (and caching) when missing or forced. */
    private function token(bool $forceRefresh = false): ?string
    {
        if ($forceRefresh) {
            Cache::forget(self::CACHE_KEY);
        }

        if ($cached = Cache::get(self::CACHE_KEY)) {
            return $cached;
        }

        $token = $this->login();
        if ($token) {
            // Conservative TTL; the 401-retry above covers earlier expiry.
            Cache::put(self::CACHE_KEY, $token, now()->addMinutes(30));
        }

        return $token;
    }

    /** Authenticate with email + password and return a JWT, or null on failure. */
    private function login(): ?string
    {
        $c = config('services.oneconfirmed');

        try {
            $response = Http::acceptJson()
                ->asJson()
                ->timeout(15)
                ->post($this->base().'/login', [
                    'email' => $c['email'],
                    'password' => $c['password'],
                ]);

            if ($response->successful()) {
                $token = data_get($response->json(), 'data.token')
                    ?? data_get($response->json(), 'token');

                if ($token) {
                    return $token;
                }
            }

            Log::error('1Confirmed login failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        } catch (\Throwable $e) {
            Log::error('1Confirmed login exception: '.$e->getMessage());
        }

        return null;
    }

    private function base(): string
    {
        return rtrim((string) config('services.oneconfirmed.base_url'), '/');
    }
}
