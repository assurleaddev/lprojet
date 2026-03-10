@extends('layouts.app')

@section('title', 'Twilio Verify Test')

@section('content')
    <main class="w-full">
        <section class="shell px-4 md:px-6 py-8 max-w-2xl mx-auto">
            <h1 class="text-2xl font-bold mb-2">Twilio Verify API Test</h1>
            <p class="text-sm text-gray-500 mb-6">Send and verify OTP codes via SMS, Call, or WhatsApp.</p>

            {{-- Flash messages --}}
            @if(session('success'))
                <div
                    class="mb-6 p-4 rounded-xl bg-green-50 border border-green-200 text-green-800 text-sm font-medium flex items-start gap-3">
                    <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div
                    class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm font-medium flex items-start gap-3">
                    <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ session('error') }}
                </div>
            @endif

            {{-- ENV status --}}
            <div class="mb-6 p-4 rounded-xl border border-gray-200 bg-gray-50">
                <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Configuration Status</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex items-center gap-2">
                        @if(config('services.twilio.sid'))
                            <span class="w-2 h-2 rounded-full bg-green-500"></span>
                            <span class="text-gray-700">TWILIO_SID: <code
                                    class="text-xs bg-gray-200 px-1 py-0.5 rounded">{{ Str::mask(config('services.twilio.sid'), '*', 8) }}</code></span>
                        @else
                            <span class="w-2 h-2 rounded-full bg-red-500"></span>
                            <span class="text-red-600 font-medium">TWILIO_SID: not set</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        @if(config('services.twilio.auth_token'))
                            <span class="w-2 h-2 rounded-full bg-green-500"></span>
                            <span class="text-gray-700">TWILIO_AUTH_TOKEN: <code
                                    class="text-xs bg-gray-200 px-1 py-0.5 rounded">{{ Str::mask(config('services.twilio.auth_token'), '*', 4) }}</code></span>
                        @else
                            <span class="w-2 h-2 rounded-full bg-red-500"></span>
                            <span class="text-red-600 font-medium">TWILIO_AUTH_TOKEN: not set</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        @if(config('services.twilio.verify_sid'))
                            <span class="w-2 h-2 rounded-full bg-green-500"></span>
                            <span class="text-gray-700">TWILIO_VERIFY_SID: <code
                                    class="text-xs bg-gray-200 px-1 py-0.5 rounded">{{ Str::mask(config('services.twilio.verify_sid'), '*', 8) }}</code></span>
                        @else
                            <span class="w-2 h-2 rounded-full bg-red-500"></span>
                            <span class="text-red-600 font-medium">TWILIO_VERIFY_SID: not set</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Step 1: Send Code --}}
                <div class="bg-white border border-gray-200 rounded-xl p-5">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-7 h-7 rounded-full flex items-center justify-center text-white text-xs font-bold"
                            style="background-color: var(--brand)">1</div>
                        <h2 class="font-bold text-gray-900">Send Verification Code</h2>
                    </div>

                    <form action="{{ route('twilio.test.send') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Phone Number</label>
                            <input type="text" name="phone" value="{{ old('phone') }}" placeholder="+212612345678"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-red-500 focus:border-red-500">
                            <p class="text-[11px] text-gray-400 mt-1">Include country code (e.g. +212 for Morocco)</p>
                            @error('phone') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Channel</label>
                            <div class="flex gap-2">
                                <label class="flex-1 cursor-pointer">
                                    <input type="radio" name="channel" value="sms" class="sr-only peer" checked>
                                    <div class="text-center text-sm font-medium py-2 rounded-lg border-2 border-gray-200 peer-checked:border-red-500 peer-checked:bg-red-50 transition-colors"
                                        style="peer-checked:color: var(--brand)">
                                        📱 SMS
                                    </div>
                                </label>
                                <label class="flex-1 cursor-pointer">
                                    <input type="radio" name="channel" value="call" class="sr-only peer">
                                    <div
                                        class="text-center text-sm font-medium py-2 rounded-lg border-2 border-gray-200 peer-checked:border-red-500 peer-checked:bg-red-50 transition-colors">
                                        📞 Call
                                    </div>
                                </label>
                                <label class="flex-1 cursor-pointer">
                                    <input type="radio" name="channel" value="whatsapp" class="sr-only peer">
                                    <div
                                        class="text-center text-sm font-medium py-2 rounded-lg border-2 border-gray-200 peer-checked:border-red-500 peer-checked:bg-red-50 transition-colors">
                                        💬 WhatsApp
                                    </div>
                                </label>
                            </div>
                            @error('channel') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <button type="submit"
                            class="w-full py-2.5 text-white text-sm font-bold rounded-lg hover:opacity-90 transition"
                            style="background-color: var(--brand)">
                            Send Code
                        </button>
                    </form>
                </div>

                {{-- Step 2: Verify Code --}}
                <div class="bg-white border border-gray-200 rounded-xl p-5">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-7 h-7 rounded-full flex items-center justify-center text-white text-xs font-bold"
                            style="background-color: var(--brand)">2</div>
                        <h2 class="font-bold text-gray-900">Verify Code</h2>
                    </div>

                    <form action="{{ route('twilio.test.check') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Phone Number</label>
                            <input type="text" name="phone" value="{{ old('phone') }}" placeholder="+212612345678"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-red-500 focus:border-red-500">
                            <p class="text-[11px] text-gray-400 mt-1">Same number you sent the code to</p>
                            @error('phone') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Verification Code</label>
                            <input type="text" name="code" value="{{ old('code') }}" placeholder="123456" maxlength="10"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm text-center tracking-[0.3em] text-lg font-mono focus:ring-1 focus:ring-red-500 focus:border-red-500">
                            @error('code') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <button type="submit"
                            class="w-full py-2.5 text-white text-sm font-bold rounded-lg hover:opacity-90 transition"
                            style="background-color: var(--brand)">
                            Verify Code
                        </button>
                    </form>
                </div>
            </div>

            {{-- Info --}}
            <div class="mt-6 p-4 bg-blue-50 border border-blue-100 rounded-lg flex items-start gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500 mt-0.5 shrink-0" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="text-xs text-blue-800 space-y-1">
                    <p class="font-bold">Required .env variables:</p>
                    <pre class="bg-blue-100 p-2 rounded text-[11px] font-mono">TWILIO_SID=your_account_sid
    TWILIO_AUTH_TOKEN=your_auth_token
    TWILIO_VERIFY_SID=your_verify_service_sid</pre>
                    <p class="mt-2">Get these from your <a href="https://console.twilio.com" target="_blank"
                            class="underline font-bold">Twilio Console</a>. The Verify Service SID starts with
                        <code>VA</code>.</p>
                </div>
            </div>
        </section>
    </main>
@endsection