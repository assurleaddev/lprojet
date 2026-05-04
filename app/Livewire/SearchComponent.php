<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use App\Models\Product;
use App\Models\User;
use App\Models\Category;
use App\Models\Attribute;
use App\Models\Brand;
use Illuminate\Support\Str;

class SearchComponent extends Component
{
    use WithPagination;

    #[Url]
    public $query = '';

    #[Url]
    public $type = 'product';

    #[Url(as: 'categories')]
    public $categoryIds = [];

    #[Url(as: 'brands')]
    public $selectedBrands = [];

    #[Url(as: 'conditions')]
    public $selectedConditions = [];

    #[Url(as: 'attributes')]
    public $selectedAttributes = [];

    #[Url]
    public $minPrice = null;

    #[Url]
    public $maxPrice = null;

    #[Url]
    public $sort = 'newest';

    public $browsingCategoryId = null;

    public function mount()
    {
        // Ensure arrays are initialized if coming from empty URL
        $this->categoryIds = $this->categoryIds ?? [];
        $this->selectedBrands = $this->selectedBrands ?? [];
        $this->selectedConditions = $this->selectedConditions ?? [];
        $this->selectedAttributes = $this->selectedAttributes ?? [];
    }

    public function updated($propertyName)
    {
        $this->resetPage();
    }

    public function browseCategory($id)
    {
        $this->browsingCategoryId = $id;
    }

    public function selectCategory($id)
    {
        $this->categoryIds = [$id];
        $this->resetPage();
    }

    public function getBrowsingCategoryProperty()
    {
        return $this->browsingCategoryId ? Category::find($this->browsingCategoryId) : null;
    }

    public function getBrowsingCategoriesProperty()
    {
        if ($this->browsingCategoryId) {
            return Category::where('parent_id', $this->browsingCategoryId)->get();
        }
        return Category::whereNull('parent_id')->get();
    }

    /**
     * Recursively collect a category ID and all its descendant IDs.
     */
    protected function getDescendantIds(int $categoryId): array
    {
        $ids = [$categoryId];
        $children = Category::where('parent_id', $categoryId)->pluck('id')->toArray();
        foreach ($children as $childId) {
            $ids = array_merge($ids, $this->getDescendantIds($childId));
        }
        return $ids;
    }

    public function removeFilter($type, $id)
    {
        if ($type === 'category') {
            $this->categoryIds = array_diff($this->categoryIds, [$id]);
        } elseif ($type === 'brand') {
            $this->selectedBrands = array_diff($this->selectedBrands, [$id]);
        } elseif ($type === 'condition') {
            $this->selectedConditions = array_diff($this->selectedConditions, [$id]);
        } elseif ($type === 'attribute') {
            // Attribute is trickier because it's a nested array structure usually in form submission
            // But here we are flattening it for simplicity in the URL or chips?
            // Actually, for chips to work well with $selectedAttributes structure [attrId => [optId1, optId2]]
            // We need to know parent Attribute ID.
            // Let's assume $id passed here is the Option ID. We need to find it and remove it.
            foreach ($this->selectedAttributes as $attrId => $options) {
                if (isset($this->selectedAttributes[$attrId][$id])) {
                    unset($this->selectedAttributes[$attrId][$id]);
                    if (empty($this->selectedAttributes[$attrId])) {
                        unset($this->selectedAttributes[$attrId]);
                    }
                }
            }
        } elseif ($type === 'price') {
            $this->minPrice = null;
            $this->maxPrice = null;
        } elseif ($type === 'query') {
            $this->query = '';
        }
        $this->resetPage();
    }

    public function clearAllFilters()
    {
        $this->categoryIds = [];
        $this->selectedBrands = [];
        $this->selectedConditions = [];
        $this->selectedAttributes = [];
        $this->minPrice = null;
        $this->maxPrice = null;
        $this->query = '';
        $this->resetPage();
    }

    public function render()
    {
        $results = collect();
        $categories = [];
        $allAttributes = collect();
        $brands = collect();
        $conditions = collect();

        if ($this->type === 'user') {
            if ($this->query) {
                $results = User::where('username', 'like', "%{$this->query}%")
                    ->paginate(20);
            }
        } else {
            $productsQuery = Product::query()->with(['category', 'options.attribute']);

            // Approved filter
            $productsQuery->where('status', 'approved');

            // Search Query — with weighted text relevance when a query is present
            if ($this->query) {
                $q = $this->query;

                $productsQuery->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%")
                        ->orWhereHas('brand', function ($b) use ($q) {
                            $b->where('name', 'like', "%{$q}%");
                        })
                        ->orWhereHas('category', function ($c) use ($q) {
                            $c->where('name', 'like', "%{$q}%");
                        });
                });

                // Weighted relevance score: exact title > starts-with > contains > brand/category > description
                $productsQuery->selectRaw("
                    products.*,
                    (
                        CASE WHEN LOWER(products.name) = LOWER(?) THEN 100 ELSE 0 END
                        + CASE WHEN LOWER(products.name) LIKE LOWER(?) THEN 70 ELSE 0 END
                        + CASE WHEN LOWER(products.name) LIKE LOWER(?) THEN 50 ELSE 0 END
                        + CASE WHEN EXISTS (
                            SELECT 1 FROM brands b
                            WHERE b.id = products.brand_id
                              AND LOWER(b.name) LIKE LOWER(?)
                          ) THEN 30 ELSE 0 END
                        + CASE WHEN EXISTS (
                            SELECT 1 FROM categories c
                            WHERE c.id = products.category_id
                              AND LOWER(c.name) LIKE LOWER(?)
                          ) THEN 20 ELSE 0 END
                        + CASE WHEN LOWER(products.description) LIKE LOWER(?) THEN 10 ELSE 0 END
                    ) AS relevance_score
                ", [
                    $q,
                    $q . '%',
                    '%' . $q . '%',
                    '%' . $q . '%',
                    '%' . $q . '%',
                    '%' . $q . '%',
                ]);
            }

            // Categories — include selected categories and all their descendants
            if (! empty($this->categoryIds)) {
                $expandedIds = [];
                foreach ($this->categoryIds as $catId) {
                    $expandedIds = array_merge($expandedIds, $this->getDescendantIds((int) $catId));
                }
                $productsQuery->whereIn('category_id', array_unique($expandedIds));
            }

            // Brands
            if (! empty($this->selectedBrands)) {
                $productsQuery->whereIn('brand_id', $this->selectedBrands);
            }

            // Conditions
            if (! empty($this->selectedConditions)) {
                $productsQuery->whereIn('condition', $this->selectedConditions);
            }

            // Attributes
            if (! empty($this->selectedAttributes)) {
                foreach ($this->selectedAttributes as $attributeId => $optionIds) {
                    if (! empty($optionIds) && is_array($optionIds)) {
                        $productsQuery->whereHas('options', function ($q) use ($attributeId, $optionIds) {
                            $q->where('attribute_id', $attributeId)
                                ->whereIn('id', $optionIds);
                        });
                    }
                }
            }

            // Price
            if ($this->minPrice) {
                $productsQuery->where('price', '>=', $this->minPrice);
            }
            if ($this->maxPrice) {
                $productsQuery->where('price', '<=', $this->maxPrice);
            }

            // Sorting
            if ($this->sort === 'newest') {
                $productsQuery->orderBy('created_at', 'desc');
            } elseif ($this->sort === 'price_asc') {
                $productsQuery->orderBy('price', 'asc');
            } elseif ($this->sort === 'price_desc') {
                $productsQuery->orderBy('price', 'desc');
            } elseif ($this->query) {
                // Text search: relevance first, then global score as tiebreaker
                $productsQuery->orderByRaw('relevance_score DESC, score DESC, created_at DESC');
            } else {
                // Browse without query: rank by global score
                $productsQuery->orderBy('score', 'desc')->orderBy('created_at', 'desc');
            }

            $results = $productsQuery->paginate(20);

            // Fetch Sidebar Data
            $categories = Category::with('children')->whereNull('parent_id')->get();

            if (! empty($this->categoryIds)) {
                $allAttributes = Attribute::whereHas('categories', function ($q) {
                    $q->whereIn('categories.id', $this->categoryIds);
                })->with('options')->get();
            } else {
                $allAttributes = Attribute::with('options')->get();
            }

            $conditions = Product::whereNotNull('condition')->distinct()->pluck('condition');
            $brands = Brand::orderBy('name')->get();
        }

        // Partition Attributes
        $sizeAttributes = $allAttributes->filter(function ($attr) {
            return Str::startsWith($attr->code, 'size_group_') || $attr->code === 'sizes';
        });

        $colorAttribute = $allAttributes->firstWhere('type', 'color') ?? $allAttributes->firstWhere('code', 'colors');

        $otherAttributes = $allAttributes->reject(function ($attr) use ($sizeAttributes, $colorAttribute) {
            return $sizeAttributes->contains('id', $attr->id)
                || ($colorAttribute && $attr->id === $colorAttribute->id);
        });

        return view('livewire.search-component', compact(
            'results',
            'categories',
            'sizeAttributes',
            'colorAttribute',
            'brands',
            'conditions',
            'otherAttributes'
        ))->layout('layouts.app');
    }
}
