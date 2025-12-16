@if($user->isBanned())
    <button wire:click="unban({{ $user->id }})" wire:confirm="{{ __('Are you sure you want to unban this user?') }}"
        class="flex items-center gap-2 px-4 py-2 text-sm text-green-600 hover:bg-gray-100 w-full text-left"
        title="{{ __('Unban User') }}">
        <iconify-icon icon="lucide:check-circle" class="w-4 h-4"></iconify-icon>
        {{ __('Unban User') }}
    </button>
@else
    <button wire:click="ban({{ $user->id }})" wire:confirm="{{ __('Are you sure you want to ban this user?') }}"
        class="flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-gray-100 w-full text-left"
        title="{{ __('Ban User') }}">
        <iconify-icon icon="lucide:ban" class="w-4 h-4"></iconify-icon>
        {{ __('Ban User') }}
    </button>
@endif