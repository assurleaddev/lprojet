<?php
namespace App\Livewire\Datatable;

use App\Models\Category;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Database\Eloquent\Model;

class CategoryDatatable extends Datatable
{
    public string $model = Category::class;

    // This tells the datatable to not create a 'view' action button
    public array $disabledRoutes = ['view'];

    /**
     * Defines the columns for the table header.
     */
    protected function getHeaders(): array
    {
        return [
            ['id' => 'name', 'title' => __('Name'), 'sortable' => true, 'sortBy' => 'name'],
            ['id' => 'icon_display', 'title' => __('Icon'), 'sortable' => false],
            ['id' => 'slug', 'title' => __('Slug'), 'sortable' => false],
            ['id' => 'actions', 'title' => __('Actions'), 'sortable' => false, 'is_action' => true],
        ];
    }

    /**
     * Builds the query to fetch only top-level categories.
     * We eager-load the children and grandchildren to prevent multiple database queries.
     */
    protected function buildQuery(): QueryBuilder
    {
        return QueryBuilder::for($this->model)
            ->with(['children.children.media', 'children.media', 'media']) // Eager load media for all levels
            ->whereNull('parent_id')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhereHas('children', function ($subQuery) {
                            $subQuery->where('name', 'like', "%{$this->search}%");
                        });
                });
            });
    }

    /**
     * Renders the Icon column.
     * Auto-discovered by datatable component based on 'icon_display' header ID.
     */
    public function renderIconDisplayColumn($item)
    {
        if ($item->getFirstMediaUrl('icon')) {
            return '<img src="' . $item->getFirstMediaUrl('icon') . '" alt="Icon" class="w-8 h-8 rounded object-cover">';
        }

        if ($item->icon) {
            return '<div class="flex items-center" wire:ignore><iconify-icon icon="' . $item->icon . '" class="text-2xl text-gray-600 dark:text-gray-400"></iconify-icon></div>';
        }

        return '<span class="text-gray-400">-</span>';
    }

    /**
     * This is the definitive method for rendering a custom row structure.
     * The parent datatable component will call this for each top-level category.
     */
    public function renderRow(Model $item)
    {
        return view('backend.marketplace.categories._datatable_row', ['category' => $item]);
    }
}