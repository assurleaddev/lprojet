<?php

namespace App\Livewire;

use App\Models\Category;
use Livewire\Component;

class CategorySelector extends Component
{
    public $name; // Form input name (e.g. category_id)
    public $value; // Selected Category ID
    public $viewCategoryId = null; // Current level being viewed

    public function mount($name = 'category_id', $value = null)
    {
        $this->name = $name;
        $this->value = $value;

        // Initialize view at the parent of the key selected category
        if ($this->value) {
            $selected = Category::find($this->value);
            if ($selected) {
                // Should view the parent of the selected category so the user sees it selected in the list
                $this->viewCategoryId = $selected->parent_id;
            }
        }
    }

    public function drillDown($id)
    {
        $this->viewCategoryId = $id;
    }

    public function goBack()
    {
        if ($this->viewCategoryId) {
            $current = Category::find($this->viewCategoryId);
            $this->viewCategoryId = $current ? $current->parent_id : null;
        }
    }

    public function select($id)
    {
        $this->value = $id;
        // Dispatch browser event for JS listeners (dynamic attributes)
        $this->dispatch('category-selected', id: $id);
    }

    // Helper to clear selection
    public function clear()
    {
        $this->value = null;
        $this->viewCategoryId = null; // Reset to root? Or keep view? Let's reset.
        $this->dispatch('category-selected', id: null);
    }

    public function render()
    {
        if ($this->viewCategoryId) {
            $currentViewCategory = Category::with(['children'])->find($this->viewCategoryId);
            $categories = $currentViewCategory ? $currentViewCategory->children : collect();
            $title = $currentViewCategory ? $currentViewCategory->name : 'Categories';
        } else {
            // Root
            $categories = Category::whereNull('parent_id')->orderBy('order')->with(['children'])->get();
            $currentViewCategory = null;
            $title = 'Categories';
        }

        // Get label for selected category
        $selectedLabel = 'Select Category';
        if ($this->value) {
            $c = Category::find($this->value);
            $selectedLabel = $c ? $c->name : 'Select Category';
        }

        return view('livewire.category-selector', [
            'categories' => $categories,
            'currentViewCategory' => $currentViewCategory,
            'title' => $title,
            'selectedLabel' => $selectedLabel
        ]);
    }
}
