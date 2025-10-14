<?php

namespace Database\Seeders;

use App\Domains\User\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            UpdateProductStatusSeeder::class,
        ]);
        
        // Create or update a super admin user
        $superAdmin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('password'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        
        // Assign super admin role (use RBAC role name "super_admin")
        $superAdminRole = \App\Domains\Role\Models\Role::where('name', 'super_admin')->first();
        if ($superAdminRole) {
            // Ensure role assignment without duplicates
            $superAdmin->roles()->syncWithoutDetaching([$superAdminRole->id]);
        }
        
        // Create or update a test user
        $testUser = User::updateOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        
        // Assign a default role to test user if available
        $defaultRole = \App\Domains\Role\Models\Role::whereIn('name', ['kasir', 'manager', 'admin'])->first();
        if ($defaultRole) {
            $testUser->roles()->syncWithoutDetaching([$defaultRole->id]);
        }
        
        // Run seeders that depend on users
        $this->call([
            CapitalTrackingSeeder::class,
            CashLedgerSeeder::class,
        ]);
        
        // Run product and warehouse seeders
        $this->call([
            ProductUnitSeeder::class,
            WarehouseSeeder::class,
            PharmacyProductSeeder::class,
        ]);
    }
}
