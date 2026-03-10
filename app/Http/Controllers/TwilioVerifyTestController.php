<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Twilio\Rest\Client;

class TwilioVerifyTestController extends Controller
{
    protected function getTwilioClient(): Client
    {
        return new Client(
            config('services.twilio.sid'),
            config('services.twilio.auth_token')
        );
    }

    public function index()
    {
        return view('frontend.twilio-verify-test');
    }

    /**
     * Send a verification code to the given phone number.
     */
    public function sendCode(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'channel' => 'required|in:sms,call,whatsapp',
        ]);

        try {
            $client = $this->getTwilioClient();

            $verification = $client->verify->v2
                ->services(config('services.twilio.verify_sid'))
                ->verifications
                ->create($request->phone, $request->channel);

            return back()->with('success', "Verification code sent via {$request->channel} to {$request->phone}. Status: {$verification->status}");
        } catch (\Exception $e) {
            return back()->with('error', "Failed to send code: {$e->getMessage()}")->withInput();
        }
    }

    /**
     * Check a verification code.
     */
    public function checkCode(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'code' => 'required|string|min:4|max:10',
        ]);

        try {
            $client = $this->getTwilioClient();

            $verificationCheck = $client->verify->v2
                ->services(config('services.twilio.verify_sid'))
                ->verificationChecks
                ->create([
                    'to' => $request->phone,
                    'code' => $request->code,
                ]);

            if ($verificationCheck->status === 'approved') {
                return back()->with('success', "✅ Phone verified successfully! Status: {$verificationCheck->status}");
            } else {
                return back()->with('error', "❌ Verification failed. Status: {$verificationCheck->status}")->withInput();
            }
        } catch (\Exception $e) {
            return back()->with('error', "Verification check failed: {$e->getMessage()}")->withInput();
        }
    }
}
