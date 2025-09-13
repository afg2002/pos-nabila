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
}