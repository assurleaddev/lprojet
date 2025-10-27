<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProductPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('products.manage') || $user->can('products.approve');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Product $product): bool
    {
        return $user->can('products.manage') || $user->can('products.approve');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('products.manage');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Product $product): bool
    {
        // A vendor can only update their own products, admins can update any.
        if ($user->can('product.edit')) {
            // Admins can always edit
            if ($user->can('marketplace.products.approve')) {
                return true;
            }
            // Vendors can only edit their own
            return $user->hasRole(['admin', 'Superadmin']) || $user->id === $product->vendor_id;
        }
        return false;
    }

    /**
     * Determine whether the user can approve the model.
     */
    public function approve(User $user, Product $product): bool
    {
        // A user can approve a product if they have the permission and it's currently pending
        return $user->can('product.approve') && $product->status === 'pending';
    }

    public function delete(User $user, Product $product): bool
    {
        if ($user->can('product.delete')) {
            // Admins can always delete
            if ($user->can('marketplace.products.approve')) {
                return true;
            }
            // Vendors can only delete their own
            return $user->hasRole(['admin', 'Superadmin']) || $user->id === $product->vendor_id;
        }
        return false;
    }

    public function bulkDelete(User $user): bool
    {
        return $user->can('product.bulk_delete');
    }
}