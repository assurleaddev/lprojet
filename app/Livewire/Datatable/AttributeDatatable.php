<?php

namespace App\Livewire\Datatable;

use App\Models\Attribute;
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

    /** Type as a coloured pill. */
    public function renderTypeColumn($item): string
    {
        if (! $item->type) {
            return '<span class="text-gray-400">—</span>';
        }

        return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">'.e($item->type).'</span>';
    }

    /** Code in a monospace style. */
    public function renderCodeColumn($item): string
    {
        return '<span class="font-mono text-sm text-gray-500">'.e($item->code ?: '—').'</span>';
    }

    /** A count badge + the option values inline (not the raw JSON). */
    public function renderOptionsColumn($item): string
    {
        $count = (int) ($item->options_count ?? $item->options->count());
        $badge = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200 shrink-0">'.$count.'</span>';

        $values = $item->options->pluck('value')->implode(', ');
        if ($values === '') {
            return $badge;
        }

        return '<div class="flex items-center gap-2">'.$badge
            .'<span class="text-xs text-gray-500 line-clamp-1" title="'.e($values).'">'.e($values).'</span></div>';
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
