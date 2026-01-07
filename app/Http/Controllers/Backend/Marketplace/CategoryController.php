<?php

namespace App\Http\Controllers\Backend\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Attribute;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function __construct()
    {
        // Apply permissions to all methods
        $this->middleware('can:categories.manage');
    }

    public function index(Request $request)
    {
        $searchTerm = $request->input('search');

        $query = Category::query();

        if ($searchTerm) {
            // If searching, we might still want to show the tree or just flat results?
            // Usually flat results are better for search.
            // But let's keep the user's current logic if possible, or fallback to flat list for search.
            $query->where('name', 'like', '%' . $searchTerm . '%');
        } else {
            // Only roots for initial load
            $query->whereNull('parent_id');
        }

        $categories = $query->withCount('children')
            ->orderBy('order') // Ensure order is respected
            ->latest()
            ->paginate(20);

        // If the request is an AJAX request, return a JSON response with the table partial
        if ($request->ajax()) {
            $tableView = view('backend.marketplace.categories._table_body', compact('categories'))->render();
            $paginationView = $categories->links()->toHtml();
            return response()->json(['table' => $tableView, 'pagination' => $paginationView]);
        }

        return view('backend.marketplace.categories.index', compact('categories'));
    }

    public function getChildren(Category $category)
    {
        // Load children with their children count to show/hide expand button
        $children = $category->children()->orderBy('order')->withCount('children')->get();

        // Return the HTML partial
        return view('backend.marketplace.categories._child_rows', compact('children'))->render();
    }

    public function create()
    {
        $categories = Category::whereNull('parent_id')->with('children')->get();

        $attributes = Attribute::all(); // Get all attributes
        return view('backend.marketplace.categories.create', compact('categories', 'attributes'));
    }

    public function store(Request $request)
    {
        \Log::info('Category Store Request:', $request->all());
        \Log::info('Category Store File:', [$request->file('icon_image')]);

        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                // Check for uniqueness only within the same parent_id
                Rule::unique('categories')->where(function ($query) use ($request) {
                    return $query->where('parent_id', $request->parent_id);
                }),
            ],
            'parent_id' => 'nullable|exists:categories,id',
            'attributes' => 'nullable|array',
            'icon' => 'nullable|string',
            'icon_image' => 'nullable|image|max:2048',

        ]);

        $category = Category::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'parent_id' => $request->parent_id,
        ]);

        // Sync the selected attributes with the new category
        $category->attributes()->sync($request->input('attributes', []));

        // Handle Icon
        if ($request->hasFile('icon_image')) {
            $category->addMediaFromRequest('icon_image')->toMediaCollection('icon');
            $category->update(['icon' => null]); // Clear icon class if image uploaded
        } elseif ($request->filled('icon')) {
            $category->update(['icon' => $request->icon]);
        }

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function edit(Category $category)
    {
        $categories = Category::where('id', '!=', $category->id)->get();
        $attributes = Attribute::all(); // Get all attributes
        $category->load('attributes'); // Eager load the currently selected attributes
        return view('backend.marketplace.categories.edit', compact('category', 'categories', 'attributes'));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                // Check for uniqueness within the same parent, but ignore the current category's ID
                Rule::unique('categories')->where(function ($query) use ($request) {
                    return $query->where('parent_id', $request->parent_id);
                })->ignore($category->id),
            ],
            'parent_id' => 'nullable|exists:categories,id',
            'attributes' => 'nullable|array',
            'icon' => 'nullable|string',
            'icon_image' => 'nullable|image|max:2048',
        ]);

        $category->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'parent_id' => $request->parent_id,
        ]);

        // Sync the selected attributes
        $category->attributes()->sync($request->input('attributes', []));

        // Handle Icon
        if ($request->hasFile('icon_image')) {
            $category->clearMediaCollection('icon');
            $category->addMediaFromRequest('icon_image')->toMediaCollection('icon');
            $category->update(['icon' => null]);
        } elseif ($request->filled('icon')) {
            $category->clearMediaCollection('icon'); // Remove image if class provided
            $category->update(['icon' => $request->icon]);
        }

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        // Ensure category with sub-categories cannot be deleted
        if ($category->children()->exists()) {
            return back()->with('error', 'Cannot delete a category that has sub-categories.');
        }

        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}