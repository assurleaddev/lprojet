<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * 1Confirmed WhatsApp OTP sender (2FA).
 *
 * Docs: https://1confirmed.com/developers/api
 *   POST {base}/messages  (Bearer token)
 *   { template_id, template_account_flow_id, phone (no '+'), data:{otp_var:code},
 *     sms_fallback, sms_fallback_text, sms_fallback_delay }
 *
 * Configure via config/services.php → 'oneconfirmed' (env ONECONFIRMED_*).
 */
class OneConfirmedService
{
    /** True when the integration is switched on and fully configured. */
    public function enabled(): bool
    {
        $c = config('services.oneconfirmed');

        return (bool) ($c['enabled'] ?? false)
            && filled($c['token'] ?? null)
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

        try {
            $response = Http::withToken($c['token'])
                ->acceptJson()
                ->asJson()
                ->timeout(15)
                ->post(rtrim($c['base_url'], '/').'/messages', [
                    'template_id' => (int) $c['template_id'],
                    'template_account_flow_id' => (int) $c['template_account_flow_id'],
                    'phone' => $phone,
                    'name' => 'OTP',
                    'data' => [$c['otp_variable'] => (string) $code],
                    'sms_fallback' => (bool) ($c['sms_fallback'] ?? true),
                    'sms_fallback_text' => "Votre code de vérification est : {$code}",
                    'sms_fallback_delay' => (int) ($c['sms_fallback_delay'] ?? 90),
                ]);

            if ($response->successful()) {
                Log::info('1Confirmed OTP sent', ['phone' => substr($phone, 0, 5).'…']);

                return true;
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
}
