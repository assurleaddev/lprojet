<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('Superadmin');
    }

    public function view(User $user, Product $product): bool
    {
        return $user->hasRole('Superadmin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('Superadmin');
    }

    public function update(User $user, Product $product): bool
    {
        // Superadmin can edit any product; a user can edit only their own
        return $user->hasRole('Superadmin') || $user->id === $product->vendor_id;
    }

    public function delete(User $user, Product $product): bool
    {
        // Superadmin can delete any product; a user can delete only their own
        return $user->hasRole('Superadmin') || $user->id === $product->vendor_id;
    }

    public function approve(User $user, Product $product): bool
    {
        return $user->hasRole('Superadmin') && $product->status === 'pending';
    }

    public function bulkDelete(User $user): bool
    {
        return $user->hasRole('Superadmin');
    }
}
