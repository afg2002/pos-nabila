<?php

namespace App\Policies;

use App\Warehouse;
use App\Domains\User\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class WarehousePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any warehouses.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('warehouses.view');
    }

    /**
     * Determine whether the user can view the warehouse.
     */
    public function view(User $user, Warehouse $warehouse): bool
    {
        return $user->hasPermission('warehouses.view');
    }

    /**
     * Determine whether the user can create warehouses.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('warehouses.create');
    }

    /**
     * Determine whether the user can update the warehouse.
     */
    public function update(User $user, Warehouse $warehouse): bool
    {
        return $user->hasPermission('warehouses.edit');
    }

    /**
     * Determine whether the user can delete the warehouse.
     */
    public function delete(User $user, Warehouse $warehouse): bool
    {
        return $user->hasPermission('warehouses.delete');
    }

    /**
     * Determine whether the user can manage warehouses.
     */
    public function manage(User $user): bool
    {
        return $user->hasPermission('warehouses.manage');
    }
}