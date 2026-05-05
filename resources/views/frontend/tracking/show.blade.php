@extends('layouts.app')

@section('content')
<div class="max-w-lg mx-auto px-4 py-8">

    {{-- Back link --}}
    <a href="{{ route('chat.dashboard') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-800 mb-6">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        {{ __('Back to messages') }}
    </a>

    {{-- Header card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-5">
        <h1 class="text-xl font-bold text-gray-900 mb-0.5">{{ __('Order tracking') }}</h1>
        <p class="text-sm text-gray-500">
            {{ \App\Services\TrackingService::CARRIER_LABELS[$carrier] ?? $carrier }}
            &middot;
            {{ $order->tracking_code }}
        </p>
    </div>

    {{-- Error state --}}
    @if($error)
        <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-xl mb-5">
            <p class="font-semibold text-sm">{{ __('Error') }}</p>
            <p class="text-sm mt-0.5">{{ $error }}</p>
        </div>
    @endif

    {{-- Info cards --}}
    @if(!empty($info) && array_filter($info))
        <div class="grid grid-cols-2 gap-3 mb-5">
            @if(!empty($info['receipt']))
                <div class="bg-white border border-gray-100 rounded-xl p-3 shadow-sm">
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">{{ __('Tracking #') }}</p>
                    <p class="font-semibold text-gray-900 text-sm">{{ $info['receipt'] }}</p>
                </div>
            @endif
            @if(!empty($info['current_status']))
                <div class="bg-white border border-gray-100 rounded-xl p-3 shadow-sm">
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">{{ __('Status') }}</p>
                    <p class="font-semibold text-gray-900 text-sm">{{ $info['current_status'] }}</p>
                </div>
            @endif
            @if(!empty($info['last_update']))
                <div class="bg-white border border-gray-100 rounded-xl p-3 shadow-sm">
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">{{ __('Last update') }}</p>
                    <p class="font-semibold text-gray-900 text-sm">{{ $info['last_update'] }}</p>
                </div>
            @endif
            @if(!empty($info['destination']))
                <div class="bg-white border border-gray-100 rounded-xl p-3 shadow-sm">
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">{{ __('Destination') }}</p>
                    <p class="font-semibold text-gray-900 text-sm">{{ $info['destination'] }}</p>
                </div>
            @endif
        </div>
    @endif

    {{-- Timeline --}}
    @if(!empty($events))
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-5">{{ __('Tracking history') }}</p>

            <div class="relative">
                @foreach($events as $i => $event)
                    <div class="relative flex gap-4 {{ !$loop->last ? 'pb-7' : '' }}">

                        {{-- Vertical line --}}
                        @if(!$loop->last)
                            <div class="absolute left-[11px] top-6 bottom-0 w-[2px] bg-teal-200"></div>
                        @endif

                        {{-- Dot --}}
                        <div class="relative z-10 flex h-6 w-6 items-center justify-center rounded-full border-2 border-teal-500 bg-white shrink-0">
                            @if($loop->first)
                                <div class="h-3 w-3 rounded-full bg-teal-500"></div>
                            @else
                                <svg class="h-3.5 w-3.5 text-teal-500" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            @endif
                        </div>

                        {{-- Content --}}
                        <div class="flex-1 -mt-0.5">
                            <p class="text-sm font-semibold text-gray-900 leading-tight">
                                {{ $event['status'] ?: $event['step'] }}
                            </p>
                            @if(!empty($event['date']))
                                <p class="text-xs text-gray-400 mt-0.5">{{ $event['date'] }}</p>
                            @endif
                            @if(!empty($event['step']) && $event['step'] !== ($event['status'] ?? '') && !in_array($event['step'], array_values(\App\Services\TrackingService::CARRIER_LABELS)))
                                <p class="text-xs text-gray-400 mt-0.5">{{ $event['step'] }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @elseif(!$error)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-center">
            <p class="text-gray-400 text-sm">{{ __('No tracking events yet. Check back soon.') }}</p>
        </div>
    @endif

</div>
@endsection
