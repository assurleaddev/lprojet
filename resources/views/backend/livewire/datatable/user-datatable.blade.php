<div class="space-y-4">
    <x-datatable :searchbarPlaceholder="$searchbarPlaceholder" :filters="$filters" :customFilters="$customFilters"
        :perPageOptions="$perPageOptions" :headers="$headers" :enableCheckbox="$enableCheckbox"
        :noResultsMessage="$noResultsMessage" :customNoResultsMessage="$customNoResultsMessage" :data="$data"
        :newResourceLinkPermission="$newResourceLinkPermission" :newResourceLinkRouteName="$newResourceLinkRouteName"
        :newResourceLinkRouteUrl="$this->getCreateRouteUrl()" :newResourceLinkLabel="$newResourceLinkLabel"
        :multiLevel="$multiLevel" />

    {{-- Force Delete Confirmation Modal --}}
    @if($confirmingForceDelete)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="w-full max-w-lg p-6 bg-white rounded-lg shadow-xl dark:bg-gray-800">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    {{ __('Confirm Force Delete') }}
                </h3>
                <div class="mt-4">
                    <p class="text-gray-600 dark:text-gray-400">
                        {{ __('The following users have dependent records (Products or Orders). Deleting them will also permanently delete all their associated data.') }}
                    </p>
                    <ul
                        class="mt-4 list-disc list-inside text-sm text-gray-500 dark:text-gray-300 max-h-48 overflow-y-auto">
                        @foreach($usersWithDependencies as $user)
                            <li>
                                {{ $user['first_name'] }} {{ $user['last_name'] }} ({{ $user['email'] }}) -
                                {{ $user['products_count'] }} Products, {{ $user['orders_count'] }} Orders
                            </li>
                        @endforeach
                    </ul>
                    <p class="mt-4 font-bold text-red-600 dark:text-red-400">
                        {{ __('Are you sure you want to proceed? This action cannot be undone.') }}
                    </p>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <button wire:click="cancelForceDelete"
                        class="px-4 py-2 text-gray-700 bg-gray-200 rounded hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                        {{ __('Cancel') }}
                    </button>
                    <button wire:click="confirmedForceDelete"
                        class="px-4 py-2 text-white bg-red-600 rounded hover:bg-red-700">
                        {{ __('Force Delete') }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>