<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Log;
use Livewire\Component;

class VerifyPhone extends Component
{
    public $country_code = '+33'; // Default to France
    public $phone_number = '';

    public function mount()
    {
        $this->country_code = auth()->user()->phone_country_code ?? '+33';
        $this->phone_number = auth()->user()->phone_number ?? '';
    }

    public function send()
    {
        // Sanitize phone number (remove spaces, dashes, parens)
        $this->phone_number = preg_replace('/[^0-9]/', '', $this->phone_number);

        $this->validate([
            'country_code' => 'required|string',
            'phone_number' => 'required|numeric|digits_between:8,15',
        ]);

        $user = auth()->user();
        $user->phone_country_code = $this->country_code;
        $user->phone_number = $this->phone_number;
        $user->phone_verified_at = null; // Unverify until confirmed

        // Generate Code
        $code = rand(100000, 999999);
        $user->phone_verification_code = $code;
        $user->phone_verification_code_expires_at = now()->addMinutes(10);
        $user->save();

        // Send verification code via SMS (Alphanumeric Sender ID)
        try {
            $sid = config('services.twilio.sid');
            $token = config('services.twilio.token');
            $from = config('services.twilio.from'); // Set TWILIO_FROM=Used in .env

            $to = $this->country_code . $this->phone_number;

            Log::info("Attempting to send SMS. SID: " . substr((string) $sid, 0, 5) . "..., From: $from, To: $to");

            if ($sid && $token && $from) {
                $client = new \Twilio\Rest\Client($sid, $token);
                $client->setHttpClient(new \Twilio\Http\CurlClient([
                    CURLOPT_SSL_VERIFYHOST => 0,
                    CURLOPT_SSL_VERIFYPEER => 0,
                ]));

                $message = $client->messages->create(
                    $to,
                    [
                        'from' => $from,
                        'body' => "Votre code de vérification est : {$code}",
                    ]
                );
                Log::info("SMS Sent Successfully. SID: " . $message->sid);
            } else {
                Log::warning("Twilio credentials missing in config.");

                $this->addError('phone_number', __("L'envoi du SMS est indisponible pour le moment. Veuillez réessayer plus tard."));

                return null;
            }
        } catch (\Exception $e) {
            Log::error("Twilio SMS Error: " . $e->getMessage(), [
                'user_id' => $user->id,
                'exception' => $e,
            ]);

            $this->addError('phone_number', __("Nous n'avons pas pu envoyer le SMS. Veuillez vérifier votre numéro et réessayer."));

            return null;
        }

        return redirect()->route('auth.verify_phone_code');
    }

    public function render()
    {
        return view('livewire.auth.verify-phone')->layout('layouts.auth-simple');
    }
}
