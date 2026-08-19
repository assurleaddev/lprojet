<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
        // FIX: Bypass SSL certificate error for local development
        'guzzle' => [
            'verify' => false,
        ],
    ],

    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('FACEBOOK_REDIRECT_URI'),
    ],

    'apple' => [
        'client_id' => env('APPLE_CLIENT_ID'),
        'client_secret' => env('APPLE_CLIENT_SECRET'),
        'redirect' => env('APPLE_REDIRECT_URI'),
    ],

    'openai' => [
        'key' => env('OPENAI_API_KEY'),
    ],

    'huggingface' => [
        'token' => env('HUGGING_FACE_TOKEN'),
    ],

    'gemini' => [
        'key' => env('GEMINI_API_KEY'),
    ],

    'tawssil_proxy' => env('TAWSSIL_PROXY_URL'),

    'twilio' => [
        'sid' => env('TWILIO_SID'),
        'auth_token' => env('TWILIO_AUTH_TOKEN', env('TWILIO_TOKEN')),
        'token' => env('TWILIO_TOKEN'),
        'from' => env('TWILIO_FROM'),
        'whatsapp_from' => env('TWILIO_WHATSAPP_FROM'),
        'verify_sid' => env('TWILIO_VERIFY_SID'),
        'phone_verification_enabled' => env('PHONE_VERIFICATION_ENABLED', true),
    ],

    // 1Confirmed — WhatsApp OTP (2FA). When enabled + configured, phone
    // verification codes are delivered via WhatsApp instead of Twilio SMS.
    'oneconfirmed' => [
        'enabled' => env('ONECONFIRMED_ENABLED', false),
        'base_url' => env('ONECONFIRMED_BASE_URL', 'https://1confirmed.com/api/v1'),
        // 1Confirmed issues a JWT from the login endpoint (no static API key),
        // so the service authenticates with email + password and caches the token.
        'email' => env('ONECONFIRMED_EMAIL'),
        'password' => env('ONECONFIRMED_PASSWORD'),
        'template_id' => env('ONECONFIRMED_OTP_TEMPLATE_ID'),
        'template_account_flow_id' => env('ONECONFIRMED_OTP_FLOW_ID'),
        // The template variable that receives the code (as defined in your template).
        'otp_variable' => env('ONECONFIRMED_OTP_VARIABLE', 'otp'),
        'sms_fallback' => env('ONECONFIRMED_SMS_FALLBACK', true),
        'sms_fallback_delay' => env('ONECONFIRMED_SMS_FALLBACK_DELAY', 90),
    ],

];
