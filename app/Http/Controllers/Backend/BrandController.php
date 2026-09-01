<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BrandController extends Controller
{
    /** Paginated, searchable list of brands. */
    public function index(Request $request)
    {
        $search = $request->query('q');

        $brands = Brand::query()
            ->when($search, fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->withCount('products')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('backend.brands.index', compact('brands', 'search'));
    }

    public function create()
    {
        return view('backend.brands.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:brands,name',
        ]);

        Brand::create([
            'name' => $validated['name'],
            'slug' => $this->uniqueSlug($validated['name']),
        ]);

        return redirect()->route('admin.brands.index')
            ->with('success', __('Brand created successfully.'));
    }

    public function edit(Brand $brand)
    {
        return view('backend.brands.edit', compact('brand'));
    }

    public function update(Request $request, Brand $brand)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:brands,name,'.$brand->id,
        ]);

        $brand->update([
            'name' => $validated['name'],
            'slug' => $this->uniqueSlug($validated['name'], $brand->id),
        ]);

        return redirect()->route('admin.brands.index')
            ->with('success', __('Brand updated successfully.'));
    }

    public function destroy(Brand $brand)
    {
        $brand->delete();

        return back()->with('success', __('Brand deleted successfully.'));
    }

    /** Build a slug that stays unique across brands (ignoring the given id). */
    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'brand';
        $slug = $base;
        $i = 1;

        while (Brand::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
