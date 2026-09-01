<x-layouts.backend-layout>
    <x-slot name="title">{{ __('Edit Brand') }}</x-slot>

    <h2 class="text-2xl font-semibold mb-4 text-gray-900 dark:text-white">{{ __('Edit Brand') }}</h2>

    <form method="POST" action="{{ route('admin.brands.update', $brand) }}"
        class="max-w-lg bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 p-6 space-y-4">
        @csrf
        @method('PUT')
        @include('backend.brands._form')
        <div class="flex gap-3">
            <button type="submit"
                class="bg-gray-900 text-white px-6 py-2 rounded-lg font-medium hover:bg-gray-800">{{ __('Update') }}</button>
            <a href="{{ route('admin.brands.index') }}"
                class="px-6 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:text-gray-300">{{ __('Cancel') }}</a>
        </div>
    </form>
</x-layouts.backend-layout>
