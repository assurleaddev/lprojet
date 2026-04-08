<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Twilio\Rest\Client;

class TwilioVerifyTestController extends Controller
{
    protected function getTwilioClient(): Client
    {
        $client = new Client(
            config('services.twilio.sid'),
            config('services.twilio.auth_token')
        );

        // Fix for local SSL certificate issues
        $client->setHttpClient(new \Twilio\Http\CurlClient([
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
        ]));

        return $client;
    }

    public function index()
    {
        return view('frontend.twilio-verify-test');
    }

    /**
     * Generate and send a custom OTP via SMS (Alphanumeric Sender ID).
     */
    public function sendCode(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
        ]);

        try {
            $client = $this->getTwilioClient();

            $otp = (string) random_int(100000, 999999);

            \Illuminate\Support\Facades\Cache::put('sms_otp_' . $request->phone, $otp, now()->addMinutes(10));

            $from = config('services.twilio.from');

            if (!$from) {
                return back()->with('error', 'TWILIO_FROM is not configured.');
            }

            $message = $client->messages->create(
                $request->phone,
                [
                    'from' => $from,
                    'body' => "Your verification code is {$otp}. It will expire in 10 minutes.",
                ]
            );

            return back()->with('success', "SMS OTP sent to {$request->phone}. Message SID: {$message->sid}");
        } catch (\Exception $e) {
            return back()->with('error', "Failed to send code: {$e->getMessage()}")->withInput();
        }
    }

    /**
     * Check a custom SMS OTP.
     */
    public function checkCode(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'code' => 'required|string|min:4|max:10',
        ]);

        try {
            $cachedCode = \Illuminate\Support\Facades\Cache::get('sms_otp_' . $request->phone);

            if (!$cachedCode) {
                return back()->with('error', "❌ Verification failed. Code has expired or was not found for this number.")->withInput();
            }

            if ((string) $cachedCode === (string) $request->code) {
                \Illuminate\Support\Facades\Cache::forget('sms_otp_' . $request->phone);

                return back()->with('success', "✅ Phone verified successfully via SMS!");
            } else {
                return back()->with('error', "❌ Verification failed. Incorrect code.")->withInput();
            }
        } catch (\Exception $e) {
            return back()->with('error', "Verification check failed: {$e->getMessage()}")->withInput();
        }
    }
}
