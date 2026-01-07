@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-3xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold">Notifications</h1>
                @if($notifications->count() > 0)
                    <form action="{{ route('notifications.mark-read') }}" method="POST">
                        @csrf
                        <button type="submit" class="text-sm font-medium text-teal-600 hover:text-teal-700 hover:underline">Mark
                            all as read</button>
                    </form>
                @endif
            </div>

            <div class="bg-white rounded-lg shadow overflow-hidden">
                @forelse ($notifications as $notification)
                    <div
                        class="p-4 border-b last:border-b-0 hover:bg-gray-50 transition-colors {{ $notification->read_at ? '' : 'bg-blue-50' }}">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <a href="{{ $notification->data['url'] ?? '#' }}" class="block">
                                    <p class="text-gray-800 font-medium">
                                        {{ $notification->data['message'] ?? 'New Notification' }}
                                    </p>
                                    <p class="text-sm text-gray-500 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                                </a>
                            </div>
                            @if(!$notification->read_at)
                                <span class="inline-block w-2 h-2 bg-blue-500 rounded-full mt-2"></span>
                            @endif
                        </div>
                    </div>
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