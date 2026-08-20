<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\ApiController;
use App\Notifications\SendVerificationCode;
use App\Services\OneConfirmedService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Email + phone verification for the mobile app. Mirrors the web Livewire
 * flow (VerifyEmail / VerifyPhone / VerifyPhoneCode): a code is generated and
 * stored on the user, delivered by email (4 digits) or WhatsApp/SMS (6 digits),
 * then confirmed here.
 *
 * @tags Authentication
 */
class VerificationController extends ApiController
{
    /** Email: (re)send a 4-digit verification code to the user's email. */
    public function sendEmail(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email déjà vérifié.', 'email_verified' => true]);
        }

        $code = $user->generateVerificationCode();
        $user->notify(new SendVerificationCode($code));

        return response()->json(['message' => 'Un code a été envoyé à votre adresse e-mail.']);
    }

    /** Email: confirm the 4-digit code. */
    public function verifyEmail(Request $request): JsonResponse
    {
        $request->validate(['code' => ['required', 'digits:4']]);

        $user = $request->user();

        if ((string) $user->verification_code === (string) $request->code
            && $user->verification_code_expires_at
            && now()->lt($user->verification_code_expires_at)) {
            $user->markEmailAsVerified();
            $user->verification_code = null;
            $user->verification_code_expires_at = null;
            $user->save();

            return response()->json(['message' => 'E-mail vérifié.', 'email_verified' => true]);
        }

        return response()->json(['message' => 'Le code de vérification est invalide ou a expiré.'], 422);
    }

    /** Phone: store the number and send a 6-digit code via WhatsApp (1Confirmed) / SMS. */
    public function sendPhone(Request $request): JsonResponse
    {
        $data = $request->validate([
            'country_code' => ['required', 'string', 'max:6'],
            'phone_number' => ['required', 'numeric', 'digits_between:8,15'],
        ]);

        $user = $request->user();
        $phone = preg_replace('/[^0-9]/', '', $data['phone_number']);

        $user->phone_country_code = $data['country_code'];
        $user->phone_number = $phone;
        $user->phone_verified_at = null;

        $code = rand(100000, 999999);
        $user->phone_verification_code = (string) $code;
        $user->phone_verification_code_expires_at = now()->addMinutes(10);
        $user->save();

        $to = $data['country_code'].$phone;
        $oneConfirmed = app(OneConfirmedService::class);

        if ($oneConfirmed->enabled()) {
            $oneConfirmed->sendOtp($to, $code);
        } else {
            $this->sendTwilioSms($to, $code);
        }

        return response()->json(['message' => 'Un code a été envoyé à votre numéro.']);
    }

    /** Phone: confirm the 6-digit code. */
    public function verifyPhone(Request $request): JsonResponse
    {
        $request->validate(['code' => ['required', 'numeric', 'digits:6']]);

        $user = $request->user();

        if ((string) $user->phone_verification_code === (string) $request->code
            && $user->phone_verification_code_expires_at
            && $user->phone_verification_code_expires_at->isFuture()) {
            $user->phone_verified_at = now();
            $user->phone_verification_code = null;
            $user->phone_verification_code_expires_at = null;
            $user->save();

            return response()->json(['message' => 'Numéro vérifié.', 'phone_verified' => true]);
        }

        return response()->json(['message' => 'Le code est invalide ou a expiré.'], 422);
    }

    /** Twilio SMS fallback (only used when 1Confirmed is not configured). */
    private function sendTwilioSms(string $to, int $code): void
    {
        try {
            $sid = config('services.twilio.sid');
            $token = config('services.twilio.token');
            $from = config('services.twilio.from');

            if ($sid && $token && $from) {
                $client = new \Twilio\Rest\Client($sid, $token);
                $client->messages->create($to, [
                    'from' => $from,
                    'body' => "Votre code de vérification est : {$code}",
                ]);
            } else {
                Log::warning('Phone OTP: no 1Confirmed and Twilio credentials missing.');
            }
        } catch (\Throwable $e) {
            Log::error('Twilio SMS error (mobile verify): '.$e->getMessage());
        }
    }
}
