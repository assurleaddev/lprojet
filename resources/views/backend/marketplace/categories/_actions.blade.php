<a href="{{ route('admin.categories.edit', $category) }}"
    class="font-medium text-blue-600 dark:text-blue-500 hover:underline mr-3">Edit</a>
<form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="inline-block"
    onsubmit="return confirm('Are you sure you want to delete this category?');">
    @csrf
    @method('DELETE')
    <button type="submit" class="font-medium text-red-600 dark:text-red-500 hover:underline">Delete</button>
</form>