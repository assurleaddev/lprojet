<?php

namespace App\Livewire;

use Livewire\Component;

class HomeMenu extends Component
{
    public function render()
    {
        $categories = \App\Models\Category::whereNull('parent_id')
            ->with([
                'children' => function ($q) {
                    // Ensure we get necessary fields and order
                    $q->orderBy('name');
                },
                'children.children' => function ($q) {
                    $q->orderBy('name');
                }
            ])
            ->orderBy('name')
            ->get();

        return view('livewire.home-menu', [
            'categories' => $categories
        ]);
    }
}
