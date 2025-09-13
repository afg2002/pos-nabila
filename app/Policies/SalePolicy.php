<?php

namespace App\Policies;

use App\Sale;
use App\Domains\User\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SalePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any sales.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('pos.access') || $user->hasPermission('pos.reports');
    }

    /**
     * Determine whether the user can view the sale.
     */
    public function view(User $user, Sale $sale): bool
    {
        return $user->hasPermission('pos.access') || $user->hasPermission('pos.reports');
    }

    /**
     * Determine whether the user can create sales.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('pos.sell');
    }

    /**
     * Determine whether the user can access POS system.
     */
    public function accessPos(User $user): bool
    {
        return $user->hasPermission('pos.access');
    }

    /**
     * Determine whether the user can process sales.
     */
    public function processSale(User $user): bool
    {
        return $user->hasPermission('pos.sell');
    }

    /**
     * Determine whether the user can view sales reports.
     */
    public function viewReports(User $user): bool
    {
        return $user->hasPermission('pos.reports');
    }

    /**
     * Determine whether the user can process refunds.
     */
    public function refund(User $user, Sale $sale): bool
    {
        return $user->hasPermission('pos.refund');
    }
}