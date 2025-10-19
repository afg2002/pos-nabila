<?php

namespace App\Policies;

use App\Models\Supplier;
use App\Domains\User\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SupplierPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any suppliers.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('suppliers.view');
    }

    /**
     * Determine whether the user can view the supplier.
     */
    public function view(User $user, Supplier $supplier): bool
    {
        return $user->hasPermission('suppliers.view');
    }

    /**
     * Determine whether the user can create suppliers.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('suppliers.create');
    }

    /**
     * Determine whether the user can update the supplier.
     */
    public function update(User $user, Supplier $supplier): bool
    {
        return $user->hasPermission('suppliers.edit');
    }

    /**
     * Determine whether the user can delete the supplier.
     */
    public function delete(User $user, Supplier $supplier): bool
    {
        return $user->hasPermission('suppliers.delete');
    }

    /**
     * Determine whether the user can export suppliers.
     */
    public function export(User $user): bool
    {
        return $user->hasPermission('suppliers.export');
    }

    /**
     * Determine whether the user can restore the supplier.
     */
    public function restore(User $user, Supplier $supplier): bool
    {
        return $user->hasPermission('suppliers.edit');
    }

    /**
     * Determine whether the user can permanently delete the supplier.
     */
    public function forceDelete(User $user, Supplier $supplier): bool
    {
        return $user->hasPermission('suppliers.delete');
    }
}