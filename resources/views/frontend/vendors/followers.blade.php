@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-[1200px] px-6 md:px-10 py-8">
        <div class="flex items-center gap-2 mb-6">
            <a href="{{ route('vendor.show', $user) }}" class="flex items-center gap-2 text-zinc-500 hover:text-zinc-800">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                <img src="{{ $user->avatar_url }}" class="w-8 h-8 rounded-full object-cover">
                <span class="font-semibold">{{ $user->username }}</span>
            </a>
        </div>

        <h1 class="text-2xl font-semibold mb-6">{{ $user->username }}'s followers</h1>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($followers as $followerPivot)
                @php
                    $follower = $followerPivot->user;
                @endphp
                <a href="{{ route('vendor.show', $follower) }}"
                    class="flex items-center gap-4 p-4 border border-zinc-200 rounded-lg hover:bg-zinc-50 transition">
                    @if($follower->avatar_url)
                        <img src="{{ $follower->avatar_url }}" class="w-12 h-12 rounded-full object-cover">
                    @else
                        <div
                            class="w-12 h-12 rounded-full bg-[var(--brand)] text-white flex items-center justify-center font-bold text-lg">
                            {{ $follower->initials }}
                        </div>
                    @endif
                    <div>
                        <div class="font-semibold text-zinc-900">{{ $follower->username }}</div>
                        <!-- Rating (Placeholder or Real) -->
                        <div class="flex items-center gap-1 text-amber-400 text-sm">
                            @for($i = 0; $i < 5; $i++)
                                <svg class="w-3 h-3 fill-current" viewBox="0 0 20 20">
                                    <path
                                        d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            @endfor
                            <span class="text-zinc-400 ml-1">(0)</span>
                        </div>
                    </div>
                </a>
            @empty
                <div class="col-span-full text-center py-12 text-zinc-500">
                    No followers yet.
                </div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $followers->links() }}
        </div>
    </div>
@endsection