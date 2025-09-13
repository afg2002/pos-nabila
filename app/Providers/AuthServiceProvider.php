<?php

namespace App\Providers;

use App\Policies\ProductPolicy;
use App\Policies\SalePolicy;
use App\Policies\StockMovementPolicy;
use App\Policies\CustomerPolicy;
use App\Product;
use App\Sale;
use App\StockMovement;
use App\Customer;
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
    }
}