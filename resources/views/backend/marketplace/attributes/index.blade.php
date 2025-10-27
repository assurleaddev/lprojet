<x-layouts.backend-layout>
    <x-slot name="title">Attributes</x-slot>

    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">Attribute List</h2>
        <a href="{{ route('admin.marketplace.attributes.create') }}" class="btn btn-primary">
            <i class="fas fa-plus mr-2"></i> Add New Attribute
        </a>
    </div>

    <x-card>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3">Name</th>
                        <th scope="col" class="px-6 py-3">Options</th>
                        <th scope="col" class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attributes as $attribute)
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ $attribute->name }}</td>
                            <td class="px-6 py-4">
                                {{ $attribute->options->pluck('value')->implode(', ') }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.marketplace.attributes.edit', $attribute) }}" class="font-medium text-blue-600 dark:text-blue-500 hover:underline mr-3">Edit</a>
                                <form action="{{ route('admin.marketplace.attributes.destroy', $attribute) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure? This will delete the attribute and all its options.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="font-medium text-red-600 dark:text-red-500 hover:underline">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-center">No attributes found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">
            {{ $attributes->links() }}
        </div>
    </x-card>

</x-layouts.backend-layout>