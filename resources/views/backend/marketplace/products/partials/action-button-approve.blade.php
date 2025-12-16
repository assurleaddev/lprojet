@if($product->status !== 'approved')
    <form action="{{ route('admin.products.approve', $product) }}" method="POST" class="w-full">
        @csrf
        @method('POST')
        <button type="submit"
            class="flex w-full items-center gap-2 px-4 py-2 text-sm text-green-700 hover:bg-gray-100 dark:text-green-400 dark:hover:bg-gray-700"
            role="menuitem">
            <iconify-icon icon="lucide:check-circle" class="text-base"></iconify-icon>
            {{ __('Approve') }}
        </button>
    </form>
@endif