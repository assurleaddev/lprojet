<?php

namespace App\Livewire\Search;

use App\Models\Category;
use Livewire\Component;

class CategoryFilter extends Component
{
    public $categoryIds = [];
    public $viewCategoryId = null;

    public function mount($categoryIds = [])
    {
        $this->categoryIds = $categoryIds;

        // If a category is selected, start view at its parent (or itself if it has children)
        // ideally we want to see the list containing the selected item
        if (!empty($categoryIds)) {
            $firstId = $categoryIds[0];
            $selected = Category::find($firstId);
            if ($selected) {
                // If selected has children, viewed category is itself? 
                // No, usually filters show siblings. So view parent.
                // But if we want Vinted style:
                // "Clothes" -> "Men" -> "Jeans".
                // If "Jeans" is selected, we want to see "Jeans" checked in the "Men" list.
                // So view category should be the PARENT of the selected category.
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

    public function render()
    {
        if ($this->viewCategoryId) {
            $currentViewCategory = Category::with(['children'])->find($this->viewCategoryId);
            $categories = $currentViewCategory ? $currentViewCategory->children : collect();
            $title = $currentViewCategory ? $currentViewCategory->name : 'Categories';
        } else {
            // Root
            $categories = Category::whereNull('parent_id')->with(['children'])->get();
            $currentViewCategory = null;
            $title = 'Categories';
        }

        return view('livewire.search.category-filter', [
            'categories' => $categories,
            'currentViewCategory' => $currentViewCategory,
            'title' => $title
        ]);
    }
}
