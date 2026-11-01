<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Verifies a Google Sign-In ID token sent by the mobile app.
 *
 * Uses Google's tokeninfo endpoint, which checks the signature and expiry,
 * then checks that the token was issued by Google for one of OUR OAuth
 * clients and that the email is verified.
 */
class GoogleIdTokenVerifier
{
    private const TOKENINFO_URL = 'https://oauth2.googleapis.com/tokeninfo';

    private const ISSUERS = ['accounts.google.com', 'https://accounts.google.com'];

    /**
     * @return array{sub: string, email: string, name: ?string, given_name: ?string, family_name: ?string}|null
     *                                                                                                          The verified identity, or null if the token is not acceptable.
     */
    public function verify(string $idToken): ?array
    {
        $allowedAudiences = $this->allowedAudiences();
        if ($allowedAudiences === []) {
            Log::warning('Google mobile login attempted but no Google client ID is configured.');

            return null;
        }

        try {
            $response = Http::timeout(10)->get(self::TOKENINFO_URL, ['id_token' => $idToken]);
        } catch (\Throwable $e) {
            Log::warning('Google tokeninfo request failed: '.$e->getMessage());

            return null;
        }

        if (! $response->ok()) {
            return null;
        }

        $claims = $response->json();

        $valid = is_array($claims)
            && in_array($claims['aud'] ?? null, $allowedAudiences, true)
            && in_array($claims['iss'] ?? null, self::ISSUERS, true)
            && (int) ($claims['exp'] ?? 0) > time()
            && in_array($claims['email_verified'] ?? null, [true, 'true'], true)
            && ! empty($claims['email'])
            && ! empty($claims['sub']);

        if (! $valid) {
            return null;
        }

        return [
            'sub' => (string) $claims['sub'],
            'email' => strtolower((string) $claims['email']),
            'name' => $claims['name'] ?? null,
            'given_name' => $claims['given_name'] ?? null,
            'family_name' => $claims['family_name'] ?? null,
        ];
    }

    /** @return list<string> */
    private function allowedAudiences(): array
    {
        return array_values(array_filter(array_unique([
            (string) config('services.google.client_id'),
            ...(array) config('services.google.mobile_client_ids', []),
        ])));
    }
}
