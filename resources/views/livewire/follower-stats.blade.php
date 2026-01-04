<div class="mt-1 flex items-center gap-2 text-[15px]" wire:poll.5s="refreshCounts">
    <svg class="h-4 w-4 text-zinc-600" viewBox="0 0 24 24" fill="currentColor">
        <path d="M4 4h16v2H4V4Zm0 5h16v2H4V9Zm0 5h12v2H4v-2Z" />
    </svg>
    <a href="#" class="text-teal-700 hover:underline">
        <span wire:key="followers-{{ $followersCount }}">{{ $followersCount }}</span> followers
    </a>,
    <a href="#" class="text-teal-700 hover:underline">
        <span wire:key="following-{{ $followingCount }}">{{ $followingCount }}</span> following
    </a>
</div>