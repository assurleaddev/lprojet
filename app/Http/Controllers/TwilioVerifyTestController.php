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
     * Generate and send a custom OTP via WhatsApp.
     */
    public function sendCode(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
        ]);

        try {
            $client = $this->getTwilioClient();
            
            // Generate a random 6-digit OTP
            $otp = (string) random_int(100000, 999999);
            
            // Store it in the cache for 10 minutes
            \Illuminate\Support\Facades\Cache::put('whatsapp_otp_' . $request->phone, $otp, now()->addMinutes(10));
            
            // Formatting numbers for WhatsApp
            $to = str_starts_with($request->phone, 'whatsapp:') ? $request->phone : 'whatsapp:' . $request->phone;
            $from = config('services.twilio.whatsapp_from'); // e.g. whatsapp:+14155238886
            
            if (!$from) {
                return back()->with('error', 'TWILIO_WHATSAPP_FROM is not configured.');
            }

            // Send via Programmable Messaging API
            $message = $client->messages->create(
                $to,
                [
                    'from' => $from,
                    'body' => "Your verification code is {$otp}. It will expire in 10 minutes."
                ]
            );

            return back()->with('success', "WhatsApp OTP sent to {$request->phone}. Message SID: {$message->sid}");
        } catch (\Exception $e) {
            return back()->with('error', "Failed to send code: {$e->getMessage()}")->withInput();
        }
    }

    /**
     * Check a custom WhatsApp OTP.
     */
    public function checkCode(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'code' => 'required|string|min:4|max:10',
        ]);

        try {
            $cachedCode = \Illuminate\Support\Facades\Cache::get('whatsapp_otp_' . $request->phone);

            if (!$cachedCode) {
                return back()->with('error', "❌ Verification failed. Code has expired or was not found for this number.")->withInput();
            }

            if ((string)$cachedCode === (string)$request->code) {
                // Clear the cache after successful verification
                \Illuminate\Support\Facades\Cache::forget('whatsapp_otp_' . $request->phone);
                
                return back()->with('success', "✅ Phone verified successfully via WhatsApp!");
            } else {
                return back()->with('error', "❌ Verification failed. Incorrect code.")->withInput();
            }
        } catch (\Exception $e) {
            return back()->with('error', "Verification check failed: {$e->getMessage()}")->withInput();
        }
    }
}
