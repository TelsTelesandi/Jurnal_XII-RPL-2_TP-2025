<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Order $order): bool
    {
        if (($user->role ?? null) === 'Admin') return true;
        if (($user->role ?? null) === 'Customer') return (int)$order->customer_id === (int)$user->id;
        // Drivers generally shouldn't view arbitrary orders unless via assigned flow; deny by default
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Admin and Customer can create orders; Driver typically not
        return in_array(($user->role ?? null), ['Admin','Customer'], true);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Order $order): bool
    {
        if (($user->role ?? null) === 'Admin') return true;
        if (($user->role ?? null) === 'Customer') return (int)$order->customer_id === (int)$user->id;
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Order $order): bool
    {
        if (($user->role ?? null) === 'Admin') return true;
        if (($user->role ?? null) === 'Customer') return (int)$order->customer_id === (int)$user->id;
        return false;
    }
}
