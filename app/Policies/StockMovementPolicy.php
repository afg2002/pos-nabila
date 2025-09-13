<?php

namespace App\Policies;

use App\StockMovement;
use App\Domains\User\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StockMovementPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any stock movements.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('inventory.view');
    }

    /**
     * Determine whether the user can view the stock movement.
     */
    public function view(User $user, StockMovement $stockMovement): bool
    {
        return $user->hasPermission('inventory.view');
    }

    /**
     * Determine whether the user can create stock movements.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('inventory.create') || $user->hasPermission('inventory.manage');
    }

    /**
     * Determine whether the user can update the stock movement.
     */
    public function update(User $user, StockMovement $stockMovement): bool
    {
        return $user->hasPermission('inventory.update') || $user->hasPermission('inventory.manage');
    }

    /**
     * Determine whether the user can delete the stock movement.
     */
    public function delete(User $user, StockMovement $stockMovement): bool
    {
        return $user->hasPermission('inventory.delete') || $user->hasPermission('inventory.manage');
    }

    /**
     * Determine whether the user can view stock history.
     */
    public function viewHistory(User $user): bool
    {
        return $user->hasPermission('inventory.history');
    }

    /**
     * Determine whether the user can export inventory reports.
     */
    public function export(User $user): bool
    {
        return $user->hasPermission('inventory.export');
    }

    /**
     * Determine whether the user can manage inventory (add/reduce stock).
     */
    public function manage(User $user): bool
    {
        return $user->hasPermission('inventory.manage') || $user->hasPermission('inventory.create');
    }
}