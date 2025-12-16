<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ShippingOptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('backend.shipping_options.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('backend.shipping_options.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(\Illuminate\Http\Request $request)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'type' => 'required|in:home_pickup,drop_off',
            'description' => 'nullable|string',
            'key' => 'required|string|unique:shipping_options,key',
            'icon_class' => 'nullable|string',
            'is_active' => 'boolean',
            'logo' => 'nullable|image|max:1024',
        ]);

        $validated['is_active'] = $request->has('is_active');

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('shipping_logos', 'public');
            $validated['logo_path'] = $path;
        }

        \App\Models\ShippingOption::create($validated);

        return redirect()->route('admin.shipping-options.index')
            ->with('success', 'Shipping option created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(\App\Models\ShippingOption $shippingOption)
    {
        return view('backend.shipping_options.edit', compact('shippingOption'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(\Illuminate\Http\Request $request, \App\Models\ShippingOption $shippingOption)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'type' => 'required|in:home_pickup,drop_off',
            'description' => 'nullable|string',
            'key' => 'required|string|unique:shipping_options,key,' . $shippingOption->id,
            'icon_class' => 'nullable|string',
            'is_active' => 'boolean',
            'logo' => 'nullable|image|max:1024',
        ]);

        $validated['is_active'] = $request->has('is_active');

        if ($request->hasFile('logo')) {
            if ($shippingOption->logo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($shippingOption->logo_path)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($shippingOption->logo_path);
            }
            $path = $request->file('logo')->store('shipping_logos', 'public');
            $validated['logo_path'] = $path;
        }

        $shippingOption->update($validated);

        return redirect()->route('admin.shipping-options.index')
            ->with('success', 'Shipping option updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(\App\Models\ShippingOption $shippingOption)
    {
        $shippingOption->delete();
        return back()->with('success', 'Shipping option deleted successfully.');
    }
}
