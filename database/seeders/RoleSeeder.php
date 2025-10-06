<?php

namespace Database\Seeders;

use App\Domains\Role\Models\Role;
use App\Domains\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure permissions are seeded first so role assignments work correctly
        $this->call(PermissionSeeder::class);

        // Create Super Admin role with all permissions
        $superAdmin = Role::firstOrCreate(
            ['name' => 'super-admin'],
            [
                'display_name' => 'Super Administrator',
                'description' => 'Has access to all system functions',
                'is_active' => true,
            ]
        );
        
        // Assign super admin permissions (all permissions)
        $allPermissions = Permission::all();
        $superAdmin->permissions()->sync($allPermissions->pluck('id'));
        
        // Create Admin role
        $admin = Role::firstOrCreate(
            ['name' => 'admin'],
            [
                'display_name' => 'Administrator',
                'description' => 'Administrative access to most system functions',
                'is_active' => true,
            ]
        );
        
        // Assign admin permissions
        $adminPermissions = Permission::whereIn('name', [
            'users.view', 'users.create', 'users.edit', 'users.delete', 'users.restore', 'users.view_trashed',
            'roles.view', 'roles.create', 'roles.edit', 'roles.delete',
            'permissions.view', 'permissions.create', 'permissions.edit', 'permissions.delete',
            'products.view', 'products.create', 'products.edit', 'products.delete', 'products.restore', 'products.view_trashed', 'products.manage_status', 'products.export',
            'inventory.view', 'inventory.create', 'inventory.update', 'inventory.delete', 'inventory.manage', 'inventory.history', 'inventory.export',
            'warehouses.view', 'warehouses.create', 'warehouses.edit', 'warehouses.delete', 'warehouses.manage',
            'customers.view', 'customers.create', 'customers.edit', 'customers.delete', 'customers.export',
            'pos.access', 'pos.sell', 'pos.reports', 'pos.refund',
            'agenda.view', 'agenda.create', 'agenda.edit', 'agenda.delete', 'agenda.payment',
            'incoming_goods_agenda.view', 'incoming_goods_agenda.create', 'incoming_goods_agenda.edit', 'incoming_goods_agenda.delete',
            'dashboard.view', 'dashboard.export',
            'reports.view', 'reports.create', 'reports.export',
            'system.settings', 'system.logs'
        ])->get();
        $admin->permissions()->sync($adminPermissions->pluck('id'));
        
        // Create Manager role
        $manager = Role::firstOrCreate(
            ['name' => 'manager'],
            [
                'display_name' => 'Manager',
                'description' => 'Can manage users and view reports',
                'is_active' => true,
            ]
        );
        
        // Assign manager permissions
        $managerPermissions = Permission::whereIn('name', [
            'users.view', 'users.create', 'users.edit',
            'roles.view', 'permissions.view',
            'products.view', 'products.create', 'products.edit', 'products.delete', 'products.restore', 'products.view_trashed', 'products.manage_status', 'products.export',
            'inventory.view', 'inventory.create', 'inventory.update', 'inventory.delete', 'inventory.manage', 'inventory.history', 'inventory.export',
            'warehouses.view', 'warehouses.create', 'warehouses.edit', 'warehouses.manage',
            'customers.view', 'customers.create', 'customers.edit', 'customers.delete', 'customers.export',
            'pos.reports', 'dashboard.view', 'dashboard.export'
        ])->get();
        $manager->permissions()->sync($managerPermissions->pluck('id'));
        
        // Create Kasir role
        $kasir = Role::firstOrCreate(
            ['name' => 'kasir'],
            [
                'display_name' => 'Kasir',
                'description' => 'Can access POS system and basic inventory operations',
                'is_active' => true,
            ]
        );
        
        // Assign kasir permissions
        $kasirPermissions = Permission::whereIn('name', [
            'products.view',
            'inventory.view', 'inventory.create', 'inventory.history',
            'pos.access', 'pos.sell',
            'dashboard.view'
        ])->get();
        $kasir->permissions()->sync($kasirPermissions->pluck('id'));
        
        // Create Staff role
        $staff = Role::firstOrCreate(
            ['name' => 'staff'],
            [
                'display_name' => 'Staff',
                'description' => 'Can manage products and inventory',
                'is_active' => true,
            ]
        );
        
        // Assign staff permissions
        $staffPermissions = Permission::whereIn('name', [
            'products.view', 'products.create', 'products.edit',
            'inventory.view', 'inventory.create', 'inventory.update', 'inventory.manage', 'inventory.history',
            'warehouses.view',
            'customers.view', 'customers.create', 'customers.edit',
            'dashboard.view'
        ])->get();
        $staff->permissions()->sync($staffPermissions->pluck('id'));
        
        // Create User role
        $user = Role::firstOrCreate(
            ['name' => 'user'],
            [
                'display_name' => 'User',
                'description' => 'Basic user access',
                'is_active' => true,
            ]
        );
        
        // Assign basic user permissions
        $userPermissions = Permission::whereIn('name', [
            'dashboard.view'
        ])->get();
        $user->permissions()->sync($userPermissions->pluck('id'));
    }
}
