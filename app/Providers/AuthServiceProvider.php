<?php

namespace App\Providers;

use App\Policies\ProductPolicy;
use App\Policies\SalePolicy;
use App\Policies\StockMovementPolicy;
use App\Policies\CustomerPolicy;
use App\Policies\SupplierPolicy;
use App\Policies\AgendaPolicy;
use App\Product;
use App\Sale;
use App\StockMovement;
use App\Customer;
use App\Models\Supplier;
use App\IncomingGoods;
use App\PaymentSchedule;
use App\CashBalance;
use App\Receivable;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Product::class => ProductPolicy::class,
        Sale::class => SalePolicy::class,
        StockMovement::class => StockMovementPolicy::class,
        Customer::class => CustomerPolicy::class,
        Supplier::class => SupplierPolicy::class,
        IncomingGoods::class => AgendaPolicy::class,
        PaymentSchedule::class => AgendaPolicy::class,
        CashBalance::class => AgendaPolicy::class,
        Receivable::class => AgendaPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Define additional gates if needed
        Gate::define('access-dashboard', function ($user) {
            return $user->hasPermission('dashboard.view');
        });

        Gate::define('dashboard.view', function ($user) {
            return $user->hasPermission('dashboard.view');
        });

        Gate::define('dashboard.export', function ($user) {
            return $user->hasPermission('dashboard.export');
        });

        Gate::define('export-reports', function ($user) {
            return $user->hasPermission('dashboard.export');
        });

        // Agenda & Financial Gates
        Gate::define('agenda.view', function ($user) {
            return $user->hasPermission('agenda.view');
        });

        Gate::define('agenda.create', function ($user) {
            return $user->hasPermission('agenda.create');
        });

        Gate::define('agenda.update', function ($user) {
            return $user->hasPermission('agenda.update');
        });

        Gate::define('agenda.financial', function ($user) {
            return $user->hasPermission('agenda.financial');
        });

        Gate::define('agenda.payments', function ($user) {
            return $user->hasPermission('agenda.payments');
        });

        Gate::define('agenda.export', function ($user) {
            return $user->hasPermission('agenda.export');
        });

        Gate::define('financial.dashboard', function ($user) {
            return $user->hasPermission('agenda.financial') || $user->hasRole('owner');
        });

        Gate::define('financial.forms', function ($user) {
            return $user->hasPermission('agenda.create') || $user->hasRole('owner');
        });
    }
}