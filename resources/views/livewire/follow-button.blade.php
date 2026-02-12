<div>
    @php
        $isAuth = auth()->check();
        $isSelf = $isAuth && auth()->id() === $user->id;
    @endphp

    @if(!$isSelf)
        <button wire:click="toggleFollow" wire:loading.attr="disabled"
            class="rounded-md px-5 py-2.5 font-semibold shadow-card transition-colors"
            style="{{ $isFollowing ? 'background-color: white; color: var(--brand); border: 1px solid var(--brand)' : 'background-color: var(--brand); color: white' }}">
            <span wire:loading.remove>{{ $isFollowing ? 'Following' : 'Follow' }}</span>
            <span wire:loading class="inline-flex items-center gap-2">
                <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
            </span>
        </button>
    @endif

    <!-- Followers/Following Count (exposed for parent to use) -->
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('followersUpdated', (data) => {
                // Update any elements that display follower count
                const countElements = document.querySelectorAll('.followers-count');
                countElements.forEach(el => el.textContent = data.count);
            });
        });
    </script>
</div>