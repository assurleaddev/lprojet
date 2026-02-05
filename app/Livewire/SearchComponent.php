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
                $results = User::where('first_name', 'like', "%{$this->query}%")
                    ->orWhere('last_name', 'like', "%{$this->query}%")
                    ->orWhere('username', 'like', "%{$this->query}%")
                    ->orWhere('email', 'like', "%{$this->query}%")
                    ->paginate(20);
            }
        } else {
            $productsQuery = Product::query()->with(['category', 'options.attribute']);

            // Approved filter
            $productsQuery->where('status', 'approved');

            // Search Query
            if ($this->query) {
                $productsQuery->where(function ($q) {
                    $q->where('name', 'like', "%{$this->query}%")
                        ->orWhere('description', 'like', "%{$this->query}%")
                        ->orWhereHas('brand', function ($q) {
                            $q->where('name', 'like', "%{$this->query}%");
                        })
                        ->orWhereHas('category', function ($q) {
                            $q->where('name', 'like', "%{$this->query}%");
                        });
                });
            }

            // Categories
            if (!empty($this->categoryIds)) {
                $productsQuery->whereIn('category_id', $this->categoryIds);
            }

            // Brands
            if (!empty($this->selectedBrands)) {
                // Assuming Brand is a relation or column. 
                // Based on "Brand" model existence, likely a relation or direct column.
                // Earlier view used $product->brand (string?). Let's check model.
                // Actually earlier code used $product->brand (property) and "Brand" Model.
                // If Brand is a model, product likely has brand_id or brand() relation.
                // SearchController query used `orWhereHas('brand'...)` implying relation.
                // So filtering by ID is best.
                $productsQuery->whereIn('brand_id', $this->selectedBrands);
            }

            // Conditions
            if (!empty($this->selectedConditions)) {
                $productsQuery->whereIn('condition', $this->selectedConditions);
            }

            // Attributes
            if (!empty($this->selectedAttributes)) {
                foreach ($this->selectedAttributes as $attributeId => $optionIds) {
                    if (!empty($optionIds) && is_array($optionIds)) {
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
                $productsQuery->latest();
            } elseif ($this->sort === 'price_asc') {
                $productsQuery->orderBy('price', 'asc');
            } elseif ($this->sort === 'price_desc') {
                $productsQuery->orderBy('price', 'desc');
            } else {
                $productsQuery->latest(); // Default
            }

            $results = $productsQuery->paginate(20);

            // Fetch Sidebar Data
            $categories = Category::with('children')->whereNull('parent_id')->get();

            if (!empty($this->categoryIds)) {
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
