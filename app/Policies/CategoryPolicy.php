<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        
        return $user->can("categories.manage");
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('category.Create');
    }
    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Category $category): bool
    {
        return $user->can('category.edite');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Category $category): bool
    {
        // Add a check to prevent deleting a category with children
        if ($category->children()->exists()) {
            return false;
        }
        return $user->can('category.delete');
    }

    /**
     * Determine whether the user can perform bulk delete.
     */
    public function bulkDelete(User $user): bool
    {
        return $user->can('categories.manage');
    }
}