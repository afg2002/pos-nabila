<?php

namespace App\Policies;

use App\Product;
use App\Domains\User\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any products.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('products.view');
    }

    /**
     * Determine whether the user can view the product.
     */
    public function view(User $user, Product $product): bool
    {
        return $user->hasPermission('products.view');
    }

    /**
     * Determine whether the user can create products.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('products.create');
    }

    /**
     * Determine whether the user can update the product.
     */
    public function update(User $user, Product $product): bool
    {
        return $user->hasPermission('products.edit');
    }

    /**
     * Determine whether the user can delete the product.
     */
    public function delete(User $user, Product $product): bool
    {
        return $user->hasPermission('products.delete');
    }

    /**
     * Determine whether the user can bulk delete products.
     */
    public function bulkDelete(User $user): bool
    {
        return $user->hasPermission('products.delete');
    }

    /**
     * Determine whether the user can import products.
     */
    public function import(User $user): bool
    {
        return $user->hasPermission('products.import');
    }

    /**
     * Determine whether the user can export products.
     */
    public function export(User $user): bool
    {
        return $user->hasPermission('products.export');
    }
    
    /**
     * Determine whether the user can force delete the product (permanent).
     */
    public function forceDelete(User $user, Product $product): bool
    {
        return $user->hasPermission('products.force_delete');
    }
    
    /**
     * Determine whether the user can restore the product.
     */
    public function restore(User $user, Product $product): bool
    {
        return $user->hasPermission('products.restore');
    }
    
    /**
     * Determine whether the user can view trashed products.
     */
    public function viewTrashed(User $user): bool
    {
        return $user->hasPermission('products.view_trashed');
    }
    
    /**
     * Determine whether the user can manage product status.
     */
    public function manageStatus(User $user, Product $product): bool
    {
        return $user->hasPermission('products.manage_status');
    }
}