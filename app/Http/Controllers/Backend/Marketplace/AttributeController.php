<?php

namespace App\Http\Controllers\Backend\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttributeController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:attributes.manage');
    }

    public function index()
    {
        $attributes = Attribute::with('options')->latest()->paginate(20);
        return view('backend.marketplace.attributes.index', compact('attributes'));
    }

    public function create()
    {
        return view('backend.marketplace.attributes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:attributes,name',
            'options' => 'required|string',
        ]);

        DB::transaction(function () use ($request) {
            $attribute = Attribute::create(['name' => $request->name]);

            $options = array_map('trim', explode(',', $request->options));
            foreach ($options as $optionValue) {
                if (!empty($optionValue)) {
                    $attribute->options()->create(['value' => $optionValue]);
                }
            }
        });

        return redirect()->route('admin.marketplace.attributes.index')
                         ->with('success', 'Attribute created successfully.');
    }

    public function edit(Attribute $attribute)
    {
        $attribute->load('options');
        return view('backend.marketplace.attributes.edit', compact('attribute'));
    }

    public function update(Request $request, Attribute $attribute)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:attributes,name,' . $attribute->id,
            'options' => 'required|string',
        ]);

        DB::transaction(function () use ($request, $attribute) {
            $attribute->update(['name' => $request->name]);

            // Simple approach: Delete old options and create new ones
            $attribute->options()->delete();

            $options = array_map('trim', explode(',', $request->options));
            foreach ($options as $optionValue) {
                if (!empty($optionValue)) {
                    $attribute->options()->create(['value' => $optionValue]);
                }
            }
        });

        return redirect()->route('admin.marketplace.attributes.index')
                         ->with('success', 'Attribute updated successfully.');
    }

    public function destroy(Attribute $attribute)
    {
        // The database foreign key is set to cascade, so options will be deleted automatically.
        $attribute->delete(); 
        
        return redirect()->route('admin.marketplace.attributes.index')
                         ->with('success', 'Attribute deleted successfully.');
    }
}