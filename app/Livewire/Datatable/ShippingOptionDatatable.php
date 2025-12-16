<?php
namespace App\Livewire\Datatable;

use App\Models\ShippingOption;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Database\Eloquent\Model;

class ShippingOptionDatatable extends Datatable
{
    public string $model = ShippingOption::class;

    // This tells the datatable to not create a 'view' action button
    public array $disabledRoutes = ['view'];

    /**
     * Defines the columns for the table header.
     */
    protected function getHeaders(): array
    {
        return [
            ['id' => 'label', 'title' => __('Label'), 'sortable' => true, 'sortBy' => 'label'],
            ['id' => 'type', 'title' => __('Type'), 'sortable' => true, 'sortBy' => 'type'],
            ['id' => 'key', 'title' => __('Key'), 'sortable' => true, 'sortBy' => 'key'],
            ['id' => 'status', 'title' => __('Status'), 'sortable' => true, 'sortBy' => 'is_active'],
            ['id' => 'actions', 'title' => __('Actions'), 'sortable' => false, 'is_action' => true],
        ];
    }

    /**
     * Builds the query to fetch shipping options.
     */
    protected function buildQuery(): QueryBuilder
    {
        return QueryBuilder::for($this->model)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('label', 'like', "%{$this->search}%")
                        ->orWhere('key', 'like', "%{$this->search}%")
                        ->orWhere('type', 'like', "%{$this->search}%");
                });
            });
    }

    /**
     * Renders the custom row structure.
     */
    public function renderRow(Model $item)
    {
        return view('backend.shipping_options._datatable_row', ['option' => $item]);
    }

    /**
     * Override delete route naming to match resource controller
     */
    public function getRoutes(): array
    {
        return [
            'create' => 'admin.shipping-options.create',
            'edit' => 'admin.shipping-options.edit',
            'delete' => 'admin.shipping-options.destroy',
        ];
    }

    protected function getItemRouteParameters($item): array
    {
        return ['shipping_option' => $item->id];
    }

    /**
     * Override permissions to match existing settings permission
     */
    protected function getPermissions(): array
    {
        return [
            'create' => 'settings.edit',
            'view' => 'settings.edit',
            'edit' => 'settings.edit',
            'delete' => 'settings.edit',
        ];
    }
}
