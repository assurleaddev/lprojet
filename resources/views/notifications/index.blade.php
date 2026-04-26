@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-3xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold">Notifications</h1>
                @if($notifications->count() > 0)
                    <form action="{{ route('notifications.mark-read') }}" method="POST">
                        @csrf
                        <button type="submit" class="text-sm font-medium text-gray-700 hover:text-gray-900 hover:underline">Mark
                            all as read</button>
                    </form>
                @endif
            </div>

            <div class="bg-white rounded-lg shadow overflow-hidden">
                @forelse ($notifications as $notification)
                    <a href="{{ route('notifications.read', $notification->id) }}"
                        class="p-4 border-b last:border-b-0 hover:bg-gray-50 transition-colors flex items-start gap-4 {{ $notification->read_at ? '' : 'bg-blue-50' }}">
                        @if(!empty($notification->data['product_image']))
                            <img src="{{ $notification->data['product_image'] }}" class="w-12 h-12 rounded object-cover flex-shrink-0 mt-0.5">
                        @else
                            <div class="w-12 h-12 rounded bg-gray-100 flex-shrink-0 mt-0.5"></div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <p class="text-gray-800 font-medium">
                                {{ $notification->data['message'] ?? 'New Notification' }}
                            </p>
                            <p class="text-sm text-gray-500 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                        </div>
                        @if(!$notification->read_at)
                            <span class="inline-block w-2 h-2 bg-blue-500 rounded-full mt-2 flex-shrink-0"></span>
                        @endif
                    </a>
                @empty
                    <div class="p-8 text-center text-gray-500">
                        You have no notifications.
                    </div>
                @endforelse
            </div>

            <div class="mt-6">
                {{ $notifications->links('pagination::tailwind') }}
            </div>
        </div>
    </div>
@endsection