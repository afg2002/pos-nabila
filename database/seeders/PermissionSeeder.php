<?php

namespace Database\Seeders;

use App\Domains\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // User permissions
            ['name' => 'users.view', 'display_name' => 'View Users', 'description' => 'Can view user list and details', 'group' => 'users'],
            ['name' => 'users.create', 'display_name' => 'Create Users', 'description' => 'Can create new users', 'group' => 'users'],
            ['name' => 'users.edit', 'display_name' => 'Edit Users', 'description' => 'Can edit existing users', 'group' => 'users'],
            ['name' => 'users.delete', 'display_name' => 'Delete Users', 'description' => 'Can delete users', 'group' => 'users'],
            
            // Role permissions
            ['name' => 'roles.view', 'display_name' => 'View Roles', 'description' => 'Can view role list and details', 'group' => 'roles'],
            ['name' => 'roles.create', 'display_name' => 'Create Roles', 'description' => 'Can create new roles', 'group' => 'roles'],
            ['name' => 'roles.edit', 'display_name' => 'Edit Roles', 'description' => 'Can edit existing roles', 'group' => 'roles'],
            ['name' => 'roles.delete', 'display_name' => 'Delete Roles', 'description' => 'Can delete roles', 'group' => 'roles'],
            
            // Permission permissions
            ['name' => 'permissions.view', 'display_name' => 'View Permissions', 'description' => 'Can view permission list', 'group' => 'permissions'],
            ['name' => 'permissions.manage', 'display_name' => 'Manage Permissions', 'description' => 'Can assign/remove permissions from roles', 'group' => 'permissions'],
            
            // Product permissions
            ['name' => 'products.view', 'display_name' => 'View Products', 'description' => 'Can view product list and details', 'group' => 'products'],
            ['name' => 'products.create', 'display_name' => 'Create Products', 'description' => 'Can create new products', 'group' => 'products'],
            ['name' => 'products.edit', 'display_name' => 'Edit Products', 'description' => 'Can edit existing products', 'group' => 'products'],
            ['name' => 'products.delete', 'display_name' => 'Delete Products', 'description' => 'Can delete products', 'group' => 'products'],
            ['name' => 'products.restore', 'display_name' => 'Restore Products', 'description' => 'Can restore soft deleted products', 'group' => 'products'],
            ['name' => 'products.force_delete', 'display_name' => 'Force Delete Products', 'description' => 'Can permanently delete products', 'group' => 'products'],
            ['name' => 'products.view_trashed', 'display_name' => 'View Trashed Products', 'description' => 'Can view soft deleted products', 'group' => 'products'],
            ['name' => 'products.manage_status', 'display_name' => 'Manage Product Status', 'description' => 'Can change product status', 'group' => 'products'],
            ['name' => 'products.import', 'display_name' => 'Import Products', 'description' => 'Can import products from Excel', 'group' => 'products'],
            ['name' => 'products.export', 'display_name' => 'Export Products', 'description' => 'Can export products to Excel/PDF', 'group' => 'products'],
            
            // Inventory permissions
            ['name' => 'inventory.view', 'display_name' => 'View Inventory', 'description' => 'Can view inventory and stock movements', 'group' => 'inventory'],
            ['name' => 'inventory.create', 'display_name' => 'Create Stock Movement', 'description' => 'Can create new stock movements', 'group' => 'inventory'],
            ['name' => 'inventory.update', 'display_name' => 'Update Stock Movement', 'description' => 'Can edit existing stock movements', 'group' => 'inventory'],
            ['name' => 'inventory.delete', 'display_name' => 'Delete Stock Movement', 'description' => 'Can delete stock movements', 'group' => 'inventory'],
            ['name' => 'inventory.manage', 'display_name' => 'Manage Inventory', 'description' => 'Can add/reduce stock and manage inventory', 'group' => 'inventory'],
            ['name' => 'inventory.history', 'display_name' => 'View Stock History', 'description' => 'Can view stock movement history', 'group' => 'inventory'],
            ['name' => 'inventory.export', 'display_name' => 'Export Inventory', 'description' => 'Can export inventory reports', 'group' => 'inventory'],

            // Customer permissions
            ['name' => 'customers.view', 'display_name' => 'View Customers', 'description' => 'Can view customer list and details', 'group' => 'customers'],
            ['name' => 'customers.create', 'display_name' => 'Create Customers', 'description' => 'Can create new customers', 'group' => 'customers'],
            ['name' => 'customers.edit', 'display_name' => 'Edit Customers', 'description' => 'Can edit existing customers', 'group' => 'customers'],
            ['name' => 'customers.delete', 'display_name' => 'Delete Customers', 'description' => 'Can delete customers', 'group' => 'customers'],
            ['name' => 'customers.export', 'display_name' => 'Export Customers', 'description' => 'Can export customer data', 'group' => 'customers'],

            // POS permissions
            ['name' => 'pos.access', 'display_name' => 'Access POS', 'description' => 'Can access POS system', 'group' => 'pos'],
            ['name' => 'pos.sell', 'display_name' => 'Process Sales', 'description' => 'Can process sales transactions', 'group' => 'pos'],
            ['name' => 'pos.reports', 'display_name' => 'View POS Reports', 'description' => 'Can view sales reports and analytics', 'group' => 'pos'],
            ['name' => 'pos.refund', 'display_name' => 'Process Refunds', 'description' => 'Can process refunds and returns', 'group' => 'pos'],
            
            // Dashboard permissions
            ['name' => 'dashboard.view', 'display_name' => 'View Dashboard', 'description' => 'Can view dashboard and analytics', 'group' => 'dashboard'],
            ['name' => 'dashboard.export', 'display_name' => 'Export Reports', 'description' => 'Can export dashboard reports', 'group' => 'dashboard'],
            
            // System permissions
            ['name' => 'system.settings', 'display_name' => 'System Settings', 'description' => 'Can access system settings', 'group' => 'system'],
            ['name' => 'system.logs', 'display_name' => 'View Logs', 'description' => 'Can view system logs', 'group' => 'system'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }
    }
}
