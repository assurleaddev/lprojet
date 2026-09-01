<x-layouts.backend-layout>
    <x-slot name="title">{{ __('Brands') }}</x-slot>

    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ __('Brands') }}</h2>
        <a href="{{ route('admin.brands.create') }}"
            class="bg-gray-900 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-800">
            + {{ __('Add Brand') }}
        </a>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg bg-green-100 text-green-800 px-4 py-2 text-sm">{{ session('success') }}</div>
    @endif

    <form method="GET" class="mb-4">
        <input type="text" name="q" value="{{ $search }}" placeholder="{{ __('Search brands...') }}"
            class="w-full max-w-sm border border-gray-300 dark:border-gray-600 rounded-lg p-2.5 dark:bg-gray-800 dark:text-white">
    </form>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="text-xs uppercase text-gray-500 dark:text-gray-400 border-b dark:border-gray-700">
                <tr>
                    <th class="px-4 py-3">{{ __('Name') }}</th>
                    <th class="px-4 py-3">{{ __('Slug') }}</th>
                    <th class="px-4 py-3">{{ __('Products') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($brands as $brand)
                    <tr class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $brand->name }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $brand->slug }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $brand->products_count }}</td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-3">
                                <a href="{{ route('admin.brands.edit', $brand) }}"
                                    class="text-blue-600 hover:underline">{{ __('Edit') }}</a>
                                <form method="POST" action="{{ route('admin.brands.destroy', $brand) }}"
                                    onsubmit="return confirm('{{ __('Delete this brand?') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">{{ __('Delete') }}</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-gray-400">{{ __('No brands found.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $brands->links() }}
    </div>
</x-layouts.backend-layout>
