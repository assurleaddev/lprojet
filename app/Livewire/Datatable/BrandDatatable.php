<?php

namespace App\Livewire\Datatable;

use App\Models\Brand;
use Illuminate\Database\Eloquent\Model;
use Spatie\QueryBuilder\QueryBuilder;

class BrandDatatable extends Datatable
{
    public string $model = Brand::class;

    // Brands have no dedicated "view" page.
    public array $disabledRoutes = ['view'];

    protected function getHeaders(): array
    {
        return [
            ['id' => 'name', 'title' => __('Name'), 'sortable' => true, 'sortBy' => 'name'],
            ['id' => 'slug', 'title' => __('Slug'), 'sortable' => true, 'sortBy' => 'slug'],
            ['id' => 'products', 'title' => __('Products'), 'sortable' => true, 'sortBy' => 'products_count'],
            ['id' => 'created_at', 'title' => __('Created'), 'sortable' => true, 'sortBy' => 'created_at'],
            ['id' => 'actions', 'title' => __('Actions'), 'sortable' => false, 'is_action' => true],
        ];
    }

    protected function buildQuery(): QueryBuilder
    {
        return QueryBuilder::for($this->model)
            ->withCount('products')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('slug', 'like', "%{$this->search}%");
                });
            })
            ->when(
                $this->sort,
                fn ($q) => $q->orderBy($this->sort, $this->direction),
                fn ($q) => $q->orderBy('name'),
            );
    }

    public function renderRow(Model $item)
    {
        return view('backend.brands._datatable_row', ['brand' => $item]);
    }

    public function getRoutes(): array
    {
        return [
            'create' => 'admin.brands.create',
            'edit' => 'admin.brands.edit',
            'delete' => 'admin.brands.destroy',
        ];
    }

    protected function getItemRouteParameters($item): array
    {
        return ['brand' => $item->id];
    }

    protected function getPermissions(): array
    {
        return [
            'create' => 'categories.manage',
            'view' => 'categories.manage',
            'edit' => 'categories.manage',
            'delete' => 'categories.manage',
        ];
    }
}
