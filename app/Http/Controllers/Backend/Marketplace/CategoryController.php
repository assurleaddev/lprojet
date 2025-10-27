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

        $categories = Category::with([
                'children' => function ($query) use ($searchTerm) {
                    if ($searchTerm) {
                        $query->where('name', 'like', '%' . $searchTerm . '%');
                    }
                },
                'children.children',
                'parent'

            ])
            ->when($searchTerm, function ($query) use ($searchTerm) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'like', '%' . $searchTerm . '%')
                        ->orWhereHas('children', function ($subQuery) use ($searchTerm) {
                            $subQuery->where('name', 'like', '%' . $searchTerm . '%');
                        });
                });
            })
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

    public function create()
    {
        $categories = Category::whereNull('parent_id')->with('children')->get();

        $attributes = Attribute::all(); // Get all attributes
        return view('backend.marketplace.categories.create', compact('categories', 'attributes'));
    }

    public function store(Request $request)
    {
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
            'attributes' => 'nullable|array' // Validate attributes array

        ]);

        $category = Category::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'parent_id' => $request->parent_id,
        ]);

        // Sync the selected attributes with the new category
        $category->attributes()->sync($request->input('attributes', []));

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
            'attributes' => 'nullable|array'
        ]);

        $category->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'parent_id' => $request->parent_id,
        ]);

        // Sync the selected attributes
        $category->attributes()->sync($request->input('attributes', []));

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