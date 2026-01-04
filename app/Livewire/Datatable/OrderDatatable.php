<?php

namespace App\Livewire\Datatable;

use App\Models\Order;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Facades\Auth;

class OrderDatatable extends Datatable
{
    public string $model = Order::class;
    public array $disabledRoutes = ['create', 'edit', 'destroy']; // Order usually doesn't have create/edit/delete in the same way

    // Filters
    public string $status = '';
    public string $vendor = '';
    public string $date_range = '';

    public array $queryString = [
        ...parent::QUERY_STRING_DEFAULTS,
        'status' => ['except' => ''],
        'vendor' => ['except' => ''],
        'date_range' => ['except' => ''],
    ];

    public function updatingStatus()
    {
        $this->resetPage();
    }
    public function updatingVendor()
    {
        $this->resetPage();
    }
    public function updatingDateRange()
    {
        $this->resetPage();
    }

    public function getFilters(): array
    {
        return [
            [
                'id' => 'status',
                'label' => __('Status'),
                'options' => [
                    'pending' => __('Pending'),
                    'paid' => __('Paid'),
                    'shipped' => __('Shipped'),
                    'delivered' => __('Delivered'),
                    'completed' => __('Completed'),
                    'cancelled' => __('Cancelled'),
                ],
                'filterLabel' => __('Filter by status'),
                'icon' => 'lucide:check-circle',
                'allLabel' => __('All Statuses'),
                'selected' => $this->status,
            ],
            [
                'id' => 'vendor',
                'label' => __('Seller'),
                'type' => 'searchable',
                'options' => \App\Models\User::role('vendor')->get()->pluck('full_name', 'id')->toArray(),
                'filterLabel' => __('Filter by seller'),
                'icon' => 'lucide:store',
                'allLabel' => __('All Sellers'),
                'selected' => $this->vendor,
            ],
            [
                'id' => 'date_range',
                'label' => __('Date Period'),
                'options' => [
                    'today' => __('Today'),
                    'yesterday' => __('Yesterday'),
                    'last_7_days' => __('Last 7 Days'),
                    'last_30_days' => __('Last 30 Days'),
                    'this_month' => __('This Month'),
                    'last_month' => __('Last Month'),
                ],
                'filterLabel' => __('Filter by date'),
                'icon' => 'lucide:calendar',
                'allLabel' => __('All Time'),
                'selected' => $this->date_range,
            ],
        ];
    }

    protected function getHeaders(): array
    {
        return [
            ['id' => 'id', 'title' => __('Order ID'), 'sortable' => true, 'sortBy' => 'id'],
            ['id' => 'customer', 'title' => __('Customer'), 'sortable' => false],
            ['id' => 'seller', 'title' => __('Seller'), 'sortable' => false],
            ['id' => 'product', 'title' => __('Product'), 'sortable' => false],
            ['id' => 'amount', 'title' => __('Total'), 'sortable' => true, 'sortBy' => 'amount'],
            ['id' => 'status', 'title' => __('Status'), 'sortable' => true, 'sortBy' => 'status'],
            [
                'id' => 'created_at',
                'title' => __('Date'),
                'sortable' => true,
                'sortBy' => 'created_at',
            ],
            ['id' => 'actions', 'title' => __('Actions'), 'sortable' => false, 'is_action' => true],
        ];
    }

    protected function buildQuery(): QueryBuilder
    {
        $user = Auth::user();

        $query = QueryBuilder::for($this->model)
            ->with(['user', 'vendor', 'product']);

        // Role-based visibility
        if (!$user->hasRole(['admin', 'Superadmin'])) {
            // If vendor, show orders where they are the vendor
            // Or if they are a buyer? Usually "Order Management" is for sellers/admins.
            $query->where('vendor_id', $user->id);
        }

        $query
            ->when($this->search, function ($query) {
                // Search by order ID or customer name
                $query->where(function ($q) {
                    $q->where('id', 'like', "%{$this->search}%")
                        ->orWhereHas('user', function ($u) {
                            $u->where('name', 'like', "%{$this->search}%")
                                ->orWhere('username', 'like', "%{$this->search}%");
                        });
                });
            })
            ->when($this->status, function ($query) {
                $query->where('status', $this->status);
            })
            ->when($this->vendor, function ($query) {
                $query->where('vendor_id', $this->vendor);
            })
            ->when($this->date_range, function ($query) {
                switch ($this->date_range) {
                    case 'today':
                        $query->whereDate('created_at', \Carbon\Carbon::today());
                        break;
                    case 'yesterday':
                        $query->whereDate('created_at', \Carbon\Carbon::yesterday());
                        break;
                    case 'last_7_days':
                        $query->where('created_at', '>=', \Carbon\Carbon::now()->subDays(7));
                        break;
                    case 'last_30_days':
                        $query->where('created_at', '>=', \Carbon\Carbon::now()->subDays(30));
                        break;
                    case 'this_month':
                        $query->whereMonth('created_at', \Carbon\Carbon::now()->month)
                            ->whereYear('created_at', \Carbon\Carbon::now()->year);
                        break;
                    case 'last_month':
                        $query->whereMonth('created_at', \Carbon\Carbon::now()->subMonth()->month)
                            ->whereYear('created_at', \Carbon\Carbon::now()->subMonth()->year);
                        break;
                }
            });

        return $this->sortQuery($query);
    }

    public function renderIdColumn($item): string
    {
        return '#' . $item->id;
    }

    public function renderCustomerColumn($item): string
    {
        return $item->user->fullname ?? 'N/A';
    }

    public function renderSellerColumn($item): string
    {
        return $item->vendor->fullname ?? 'N/A';
    }

    public function renderProductColumn($item): string
    {
        return $item->product->name ?? 'N/A';
    }

    public function renderAmountColumn($item): string
    {
        return '$' . number_format($item->amount, 2);
    }

    public function renderStatusColumn($item): string
    {
        $colorClasses = match ($item->status) {
            'completed' => 'bg-green-100 text-green-800',
            'delivered' => 'bg-blue-100 text-blue-800',
            'shipped' => 'bg-indigo-100 text-indigo-800',
            'paid' => 'bg-purple-100 text-purple-800',
            'pending' => 'bg-yellow-100 text-yellow-800',
            'cancelled' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };

        return "<span class='px-2 py-1 font-semibold leading-tight text-xs rounded-full {$colorClasses}'>" . ucfirst($item->status) . "</span>";
    }

    // Custom action column renderer to show "View" button
    public function renderActionsColumn($item): string|Renderable
    {
        // We can bypass the default action column logic or use a partial View
        // For simplicity, let's return a string link here or use View if complex
        $url = route('admin.orders.show', $item);
        return "<a href='{$url}' class='font-medium text-blue-600 dark:text-blue-500 hover:underline'>View</a>";
    }
}
