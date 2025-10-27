<?php
namespace App\Policies;
use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('order.view.all') || $user->can('order.view.own');
    }
    
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Order $order): bool
    {
        // An admin can view any order.
        if ($user->can('order.view.all')) {
            return true;
        }

        // A vendor can only view the order if they are the vendor for that order.
        return $user->id === $order->vendor_id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Order $order): bool
    {
        if ($user->can('order.update')) {
            // An admin can update any order.
            if ($user->can('order.view.all')) {
                return true;
            }
            // A vendor can only update the order if they are the vendor.
            return $user->id === $order->vendor_id;
        }
        return false;
    }
}