<?php
namespace App\Livewire\Datatable;

use App\Models\Product;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Support\Renderable;
use App\Models\Category;
use App\Models\Option;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ProductDatatable extends Datatable
{
    public string $model = Product::class;
    public array $disabledRoutes = ['view'];

    // --- Add properties for filter state ---
    public string $category = '';
    public string $option = ''; // Changed from 'attribute' to 'option
    public string $status = '';
    public string $vendor = '';



    public array $queryString = [
        ...parent::QUERY_STRING_DEFAULTS,
        'category' => ['except' => ''],
        'option' => ['except' => ''], // Changed from 'attribute'
        'status' => ['except' => ''],
        'vendor' => ['except' => ''],


    ];

    // --- Livewire hooks for filters ---
    public function updatingCategory()
    {
        $this->resetPage();
    }
    public function updatingOption()
    {
        $this->resetPage();
    }
    public function updatingStatus()
    {
        $this->resetPage();
    }
    public function updatingVendor()
    {
        $this->resetPage();
    }




    public function getFilters(): array
    {
        return [
            [
                'id' => 'category',
                'label' => __('Category'),
                'options' => Category::pluck('name', 'id')->toArray(),
                // --- Add missing keys ---
                'filterLabel' => __('Filter by category'),
                'icon' => 'lucide:sliders',
                'allLabel' => __('All Categories'),
                'selected' => $this->category,
            ],
            [
                'id' => 'option', // Changed from 'attribute'
                'label' => __('Attribute Value'), // Changed label
                // --- Fetch options (values) instead of attribute names ---
                'options' => Option::pluck('value', 'id')->toArray(),
                // --- Add missing keys ---
                'filterLabel' => __('Filter by attribute value'),
                'icon' => 'lucide:sliders',
                'allLabel' => __('All Values'),

                'selected' => $this->option,
            ],
            [
                'id' => 'status',
                'label' => __('Status'),
                'options' => [
                    'approved' => __('Approved'),
                    'pending' => __('Pending'),
                    'sold' => __('Sold'),
                ],
                'filterLabel' => __('Filter by status'),
                'icon' => 'lucide:check-circle',
                'allLabel' => __('All Statuses'),
                'selected' => $this->status,
            ],
            [
                'id' => 'vendor',
                'label' => __('Vendor'),
                'options' => User::role('vendor')->get()->pluck('full_name', 'id')->toArray(),
                'filterLabel' => __('Filter by vendor'),
                'icon' => 'lucide:user',
                'allLabel' => __('All Vendors'),
                'selected' => $this->vendor,
            ],


        ];
    }

    /**
     * Defines the columns for the table header.
     */
    protected function getHeaders(): array
    {
        return [
            ['id' => 'name', 'title' => __('Name'), 'sortable' => true, 'sortBy' => 'name'],
            ['id' => 'category', 'title' => __('Category'), 'sortable' => false],
            ['id' => 'price', 'title' => __('Price'), 'sortable' => true, 'sortBy' => 'price'],
            [
                'id' => 'created_at',
                'title' => __('Created At'),
                'width' => null,
                'sortable' => true,
                'sortBy' => 'created_at',
            ],
            ['id' => 'status', 'title' => __('Status'), 'sortable' => true, 'sortBy' => 'status'],
            ['id' => 'actions', 'title' => __('Actions'), 'sortable' => false, 'is_action' => true],
        ];
    }

    /**
     * Builds the database query with search functionality.
     */

    /**
     * This is where the filtering logic is applied to the database query.
     */
    protected function buildQuery(): QueryBuilder
    {
        $user = Auth::user();

        $query = QueryBuilder::for($this->model)
            ->with('category');

        // Role-based product visibility
        if (!$user->hasRole(['admin', 'Superadmin'])) {
            $query->where('vendor_id', $user->id);
        }

        $query
            ->when($this->search, function ($query) {
                $query->where('name', 'like', "%{$this->search}%");
            })
            ->when($this->category, function ($query) {
                $query->where('category_id', $this->category);
            })
            ->when($this->option, function ($query) {
                $query->whereHas('options', function ($q) {
                    $q->where('option_id', $this->option);
                });
            })
            ->when($this->status, function ($query) {
                $query->where('status', $this->status);
            })
            ->when($this->vendor, function ($query) {
                $query->where('vendor_id', $this->vendor);
            });


        return $this->sortQuery($query);
    }


    public function renderNameColumn(Product $product): string|Renderable
    {
        ob_start();
        ?>
        <div class="flex gap-0.5 items-center">
            <?php if ($product->hasFeaturedImage()): ?>
                <img src="<?php echo $product->getFeaturedImageUrl('preview') ?>" alt="<?php echo $product->name ?>"
                    class="w-12 h-12 object-cover rounded mr-3 min-w-10">
            <?php else: ?>
                <div class="bg-gray-100 dark:bg-gray-700 rounded flex items-center justify-center mr-2 h-10 w-10 min-w-10">
                    <iconify-icon icon="lucide:image" class=" text-center text-gray-400"></iconify-icon>
                </div>
            <?php endif; ?>
            <a href="<?php echo route('admin.products.edit', $product->id) ?>"
                class="text-gray-700 dark:text-white font-medium hover:text-primary dark:hover:text-primary">
                <?php echo $product->name; ?>
            </a>
        </div>
        <?php
        return ob_get_clean();
    }
    // public function renderAfterActionEdit($user): string|Renderable
    // {
    //     return (! Auth::user()->can('product.login_as') || $user->id === Auth::id())
    //         ? '' :
    //         view('backend.marketplace.products.partials.action-button-aprove', compact('user'));
    // }
    /**
     * Custom renderer for the 'status' column to show a styled badge.
     */
    public function renderStatusColumn(Product $product): string
    {
        $colorClasses = match ($product->status) {
            'approved' => 'bg-green-100 text-green-800',
            'pending' => 'bg-yellow-100 text-yellow-800',
            'sold' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };

        return "<span class='px-2 py-1 font-semibold leading-tight text-xs rounded-full {$colorClasses}'>" . ucfirst($product->status) . "</span>";
    }

    /**
     * Custom renderer for the 'category' column to display the category name.
     */
    public function renderCategoryColumn(Product $product): string
    {
        return $product->category->name ?? 'N/A';
    }

    /**
     * Handles the logic for deleting multiple selected products.
     */
    protected function handleBulkDelete(array $ids): int
    {
        // Add authorization check
        $this->authorize('bulkDelete', $this->model);
        return Product::whereIn('id', $ids)->delete();
    }

    /**
     * Handles the logic for deleting a single product.
     */
    public function handleRowDelete(Model|Product $product): bool
    {
        // Add authorization check
        $this->authorize('delete', $product);
        return $product->delete();
    }
}