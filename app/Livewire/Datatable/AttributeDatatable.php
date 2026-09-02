<?php

namespace App\Livewire\Datatable;

use App\Models\Attribute;
use Illuminate\Database\Eloquent\Model;
use Spatie\QueryBuilder\QueryBuilder;

class AttributeDatatable extends Datatable
{
    public string $model = Attribute::class;

    // Attributes have no dedicated "view" page.
    public array $disabledRoutes = ['view'];

    protected function getHeaders(): array
    {
        return [
            ['id' => 'name', 'title' => __('Name'), 'sortable' => true, 'sortBy' => 'name'],
            ['id' => 'type', 'title' => __('Type'), 'sortable' => true, 'sortBy' => 'type'],
            ['id' => 'code', 'title' => __('Code'), 'sortable' => true, 'sortBy' => 'code'],
            ['id' => 'options', 'title' => __('Options'), 'sortable' => true, 'sortBy' => 'options_count'],
            ['id' => 'actions', 'title' => __('Actions'), 'sortable' => false, 'is_action' => true],
        ];
    }

    protected function buildQuery(): QueryBuilder
    {
        return QueryBuilder::for($this->model)
            ->withCount('options')
            ->with('options')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('name_fr', 'like', "%{$this->search}%")
                        ->orWhere('type', 'like', "%{$this->search}%")
                        ->orWhere('code', 'like', "%{$this->search}%");
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
        return view('backend.marketplace.attributes._datatable_row', ['attribute' => $item]);
    }

    public function getRoutes(): array
    {
        return [
            'create' => 'admin.marketplace.attributes.create',
            'edit' => 'admin.marketplace.attributes.edit',
            'delete' => 'admin.marketplace.attributes.destroy',
        ];
    }

    protected function getItemRouteParameters($item): array
    {
        return ['attribute' => $item->id];
    }

    protected function getPermissions(): array
    {
        return [
            'create' => 'attributes.manage',
            'view' => 'attributes.manage',
            'edit' => 'attributes.manage',
            'delete' => 'attributes.manage',
        ];
    }
}
