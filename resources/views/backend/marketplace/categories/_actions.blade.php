<x-buttons.action-buttons :show-label="false" align="right">
    <x-buttons.action-item
        :href="route('admin.categories.edit', $category)"
        icon="lucide:pencil"
        :label="__('Edit')"
    />

    <div x-data="{ deleteModalOpen: false }">
        <x-buttons.action-item
            type="modal-trigger"
            modal-target="deleteModalOpen"
            icon="lucide:trash-2"
            :label="__('Delete')"
            class="text-red-600 dark:text-red-400"
        />

        <x-modals.confirm-delete
            id="delete-category-{{ $category->id }}"
            title="{{ __('Delete Category') }}"
            content="{{ __('Are you sure you want to delete this category? Its subcategories will also be removed.') }}"
            formId="delete-category-form-{{ $category->id }}"
            formAction="{{ route('admin.categories.destroy', $category) }}"
            modalTrigger="deleteModalOpen"
            cancelButtonText="{{ __('No, cancel') }}"
            confirmButtonText="{{ __('Yes, delete') }}"
        />
    </div>
</x-buttons.action-buttons>
