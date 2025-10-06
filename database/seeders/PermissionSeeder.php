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
            
            // Agenda permissions
            ['name' => 'agenda.view', 'display_name' => 'View Agenda', 'description' => 'Can view incoming goods agenda and calendar', 'group' => 'agenda'],
            ['name' => 'agenda.create', 'display_name' => 'Create Agenda', 'description' => 'Can create new incoming goods schedule', 'group' => 'agenda'],
            ['name' => 'agenda.edit', 'display_name' => 'Edit Agenda', 'description' => 'Can edit existing agenda items', 'group' => 'agenda'],
            ['name' => 'agenda.delete', 'display_name' => 'Delete Agenda', 'description' => 'Can delete agenda items', 'group' => 'agenda'],
            ['name' => 'agenda.payment', 'display_name' => 'Manage Payments', 'description' => 'Can manage payment schedules and debt', 'group' => 'agenda'],
            ['name' => 'agenda.financial', 'display_name' => 'View Financial Status', 'description' => 'Can view financial condition and cash balance', 'group' => 'agenda'],
            ['name' => 'agenda.export', 'display_name' => 'Export Agenda', 'description' => 'Can export agenda and financial reports', 'group' => 'agenda'],
            
            // Dashboard permissions
            ['name' => 'dashboard.view', 'display_name' => 'View Dashboard', 'description' => 'Can view dashboard and analytics', 'group' => 'dashboard'],
            ['name' => 'dashboard.export', 'display_name' => 'Export Reports', 'description' => 'Can export dashboard reports', 'group' => 'dashboard'],
            
            // Debt Reminder permissions
            ['name' => 'debt_reminders.view', 'display_name' => 'View Debt Reminders', 'description' => 'Can view debt reminder list and details', 'group' => 'debt_reminders'],
            ['name' => 'debt_reminders.create', 'display_name' => 'Create Debt Reminders', 'description' => 'Can create new debt reminders', 'group' => 'debt_reminders'],
            ['name' => 'debt_reminders.edit', 'display_name' => 'Edit Debt Reminders', 'description' => 'Can edit existing debt reminders', 'group' => 'debt_reminders'],
            ['name' => 'debt_reminders.delete', 'display_name' => 'Delete Debt Reminders', 'description' => 'Can delete debt reminders', 'group' => 'debt_reminders'],
            ['name' => 'debt_reminders.send', 'display_name' => 'Send Debt Reminders', 'description' => 'Can send debt reminder notifications', 'group' => 'debt_reminders'],
            
            // Purchase Order permissions
            ['name' => 'purchase_orders.view', 'display_name' => 'View Purchase Orders', 'description' => 'Can view purchase order list and details', 'group' => 'purchase_orders'],
            ['name' => 'purchase_orders.create', 'display_name' => 'Create Purchase Orders', 'description' => 'Can create new purchase orders', 'group' => 'purchase_orders'],
            ['name' => 'purchase_orders.edit', 'display_name' => 'Edit Purchase Orders', 'description' => 'Can edit existing purchase orders', 'group' => 'purchase_orders'],
            ['name' => 'purchase_orders.delete', 'display_name' => 'Delete Purchase Orders', 'description' => 'Can delete purchase orders', 'group' => 'purchase_orders'],
            ['name' => 'purchase_orders.approve', 'display_name' => 'Approve Purchase Orders', 'description' => 'Can approve purchase orders', 'group' => 'purchase_orders'],
            
            // Capital Tracking permissions
            ['name' => 'capital_tracking.view', 'display_name' => 'View Capital Tracking', 'description' => 'Can view capital tracking records', 'group' => 'capital_tracking'],
            ['name' => 'capital_tracking.create', 'display_name' => 'Create Capital Tracking', 'description' => 'Can create new capital tracking records', 'group' => 'capital_tracking'],
            ['name' => 'capital_tracking.edit', 'display_name' => 'Edit Capital Tracking', 'description' => 'Can edit existing capital tracking records', 'group' => 'capital_tracking'],
            ['name' => 'capital_tracking.delete', 'display_name' => 'Delete Capital Tracking', 'description' => 'Can delete capital tracking records', 'group' => 'capital_tracking'],
            
            // Cash Ledger permissions
            ['name' => 'cash_ledger.view', 'display_name' => 'View Cash Ledger', 'description' => 'Can view cash ledger records', 'group' => 'cash_ledger'],
            ['name' => 'cash_ledger.create', 'display_name' => 'Create Cash Ledger', 'description' => 'Can create new cash ledger records', 'group' => 'cash_ledger'],
            ['name' => 'cash_ledger.edit', 'display_name' => 'Edit Cash Ledger', 'description' => 'Can edit existing cash ledger records', 'group' => 'cash_ledger'],
            ['name' => 'cash_ledger.delete', 'display_name' => 'Delete Cash Ledger', 'description' => 'Can delete cash ledger records', 'group' => 'cash_ledger'],
            
            // Reports permissions
            ['name' => 'reports.view', 'display_name' => 'View Reports', 'description' => 'Can view all reports', 'group' => 'reports'],
            ['name' => 'reports.export', 'display_name' => 'Export Reports', 'description' => 'Can export reports to Excel/PDF', 'group' => 'reports'],
            
            // Warehouse permissions
            ['name' => 'warehouses.view', 'display_name' => 'View Warehouses', 'description' => 'Can view warehouse list and details', 'group' => 'warehouses'],
            ['name' => 'warehouses.create', 'display_name' => 'Create Warehouse', 'description' => 'Can create new warehouses', 'group' => 'warehouses'],
            ['name' => 'warehouses.edit', 'display_name' => 'Edit Warehouse', 'description' => 'Can edit existing warehouses', 'group' => 'warehouses'],
            ['name' => 'warehouses.delete', 'display_name' => 'Delete Warehouse', 'description' => 'Can delete warehouses', 'group' => 'warehouses'],
            ['name' => 'warehouses.manage', 'display_name' => 'Manage Warehouses', 'description' => 'Can fully manage warehouse operations', 'group' => 'warehouses'],

            // Incoming Goods Agenda permissions
            ['name' => 'incoming_goods_agenda.view', 'display_name' => 'View Incoming Goods Agenda', 'description' => 'Can view incoming goods agenda', 'group' => 'incoming_goods_agenda'],
            ['name' => 'incoming_goods_agenda.create', 'display_name' => 'Create Incoming Goods Agenda', 'description' => 'Can create new incoming goods agenda', 'group' => 'incoming_goods_agenda'],
            ['name' => 'incoming_goods_agenda.edit', 'display_name' => 'Edit Incoming Goods Agenda', 'description' => 'Can edit existing incoming goods agenda', 'group' => 'incoming_goods_agenda'],
            ['name' => 'incoming_goods_agenda.delete', 'display_name' => 'Delete Incoming Goods Agenda', 'description' => 'Can delete incoming goods agenda', 'group' => 'incoming_goods_agenda'],
            
            // System permissions
            ['name' => 'system.settings', 'display_name' => 'System Settings', 'description' => 'Can access system settings', 'group' => 'system'],
            ['name' => 'system.logs', 'display_name' => 'View Logs', 'description' => 'Can view system logs', 'group' => 'system']
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }
    }
}
