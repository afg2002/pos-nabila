<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Domains\Role\Models\Role;
use App\Domains\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing roles and permissions (custom RBAC schema)
        DB::table('role_permissions')->delete();
        DB::table('user_roles')->delete();
        DB::table('roles')->delete();
        DB::table('permissions')->delete();
        
        // Create permissions
        $permissions = [
            // User Management
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            
            // Role Management
            'roles.view',
            'roles.create',
            'roles.edit',
            'roles.delete',
            
            // Product Management
            'products.view',
            'products.create',
            'products.edit',
            'products.delete',
            'products.import',
            
            // Product Unit Management
            'product_units.view',
            'product_units.create',
            'product_units.edit',
            'product_units.delete',
            
            // Inventory Management
            'inventory.view',
            'inventory.create',
            'inventory.edit',
            'inventory.delete',
            'inventory.adjust',
            
            // Supplier Management
            'suppliers.view',
            'suppliers.create',
            'suppliers.edit',
            'suppliers.delete',
            
            // POS Management
            'pos.access',
            'pos.view_sales',
            'pos.create_sales',
            'pos.edit_sales',
            'pos.delete_sales',
            'pos.print_receipt',
            'pos.manage_invoices',
            'pos.view_reports',
            
            // Capital Tracking Management
            'capital_tracking.view',
            'capital_tracking.create',
            'capital_tracking.edit',
            'capital_tracking.delete',
            'capital_tracking.view_reports',
            
            // Cash Ledger Management
            'cash_ledger.view',
            'cash_ledger.create',
            'cash_ledger.edit',
            'cash_ledger.delete',
            'cash_ledger.export',
            'cash_ledger.print',
            'cash_ledger.view_reports',
            
            // Agenda Management (Enhanced)
            'agenda.view',
            'agenda.create',
            'agenda.edit',
            'agenda.delete',
            'agenda.manage_cashflow',
            'agenda.manage_purchase_order',
            'agenda.view_reports',
            'agenda.export',
            
            // Cashflow Agenda Management
            'cashflow_agenda.view',
            'cashflow_agenda.create',
            'cashflow_agenda.edit',
            'cashflow_agenda.delete',
            'cashflow_agenda.view_reports',
            
            // Incoming Goods Agenda Management
            'incoming_goods_agenda.view',
            'incoming_goods_agenda.create',
            'incoming_goods_agenda.edit',
            'incoming_goods_agenda.delete',
            'incoming_goods_agenda.receive',
            'incoming_goods_agenda.manage_payments',
            'incoming_goods_agenda.view_reports',
            
            // Purchase Order Management
            'purchase_orders.view',
            'purchase_orders.create',
            'purchase_orders.edit',
            'purchase_orders.delete',
            'purchase_orders.approve',
            'purchase_orders.receive',
            'purchase_orders.view_reports',
            
            // Sales Invoice Management
            'sales_invoices.view',
            'sales_invoices.create',
            'sales_invoices.edit',
            'sales_invoices.delete',
            'sales_invoices.manage_payments',
            'sales_invoices.print',
            'sales_invoices.view_reports',
            
            // Batch Expiration Management
            'batch_expirations.view',
            'batch_expirations.create',
            'batch_expirations.edit',
            'batch_expirations.delete',
            'batch_expirations.manage_alerts',
            'batch_expirations.view_reports',
            
            // Warehouse Management
            'warehouses.view',
            'warehouses.create',
            'warehouses.edit',
            'warehouses.delete',
            'warehouses.manage_stock',
            'warehouses.view_reports',
            
            // Financial Management
            'financial.view',
            'financial.create',
            'financial.edit',
            'financial.delete',
            'financial.view_reports',
            'financial.export',
            
            // Report Management
            'reports.view',
            'reports.create',
            'reports.export',
            'reports.print',
            
            // Settings Management
            'settings.view',
            'settings.edit',
            'settings.manage_system',
            
            // Dashboard Access
            'dashboard.view',
            'dashboard.admin',
            'dashboard.financial',
            'dashboard.inventory',
            'dashboard.sales',
        ];
        
        // Create permissions using custom Domain model and fill required columns
        foreach ($permissions as $permissionName) {
            $group = explode('.', $permissionName)[0] ?? null;
            $displayName = ucwords(str_replace(['_', '.'], [' ', ' '], $permissionName));

            Permission::create([
                'name' => $permissionName,
                'display_name' => $displayName,
                'description' => null,
                'group' => $group,
                'is_active' => true,
            ]);
        }
        
        // Create roles and assign permissions
        
        // Super Admin Role - Has all permissions
        $superAdminRole = Role::create([
            'name' => 'super_admin',
            'display_name' => 'Super Admin',
            'description' => null,
            'is_active' => true,
        ]);
        $superAdminRole->permissions()->sync(Permission::pluck('id')->all());
        
        // Admin Role - Has most permissions except system settings
        $adminRole = Role::create([
            'name' => 'admin',
            'display_name' => 'Admin',
            'description' => null,
            'is_active' => true,
        ]);
        $adminPermissions = Permission::whereNotIn('name', [
            'settings.manage_system'
        ])->get();
        $adminRole->permissions()->sync($adminPermissions->pluck('id')->all());
        
        // Manager Role - Can manage most business operations
        $managerRole = Role::create([
            'name' => 'manager',
            'display_name' => 'Manager',
            'description' => null,
            'is_active' => true,
        ]);
        $managerPermissions = Permission::whereIn('name', [
            'dashboard.view',
            'dashboard.admin',
            'products.view',
            'products.edit',
            'inventory.view',
            'inventory.adjust',
            'suppliers.view',
            'suppliers.edit',
            'pos.access',
            'pos.view_sales',
            'pos.create_sales',
            'pos.edit_sales',
            'pos.print_receipt',
            'pos.manage_invoices',
            'pos.view_reports',
            'capital_tracking.view',
            'capital_tracking.create',
            'capital_tracking.edit',
            'capital_tracking.view_reports',
            'cash_ledger.view',
            'cash_ledger.create',
            'cash_ledger.edit',
            'cash_ledger.export',
            'cash_ledger.print',
            'cash_ledger.view_reports',
            'agenda.view',
            'agenda.create',
            'agenda.edit',
            'agenda.manage_cashflow',
            'agenda.manage_purchase_order',
            'agenda.view_reports',
            'agenda.export',
            'cashflow_agenda.view',
            'cashflow_agenda.create',
            'cashflow_agenda.edit',
            'cashflow_agenda.view_reports',
            'incoming_goods_agenda.view',
            'incoming_goods_agenda.create',
            'incoming_goods_agenda.edit',
            'incoming_goods_agenda.receive',
            'incoming_goods_agenda.manage_payments',
            'incoming_goods_agenda.view_reports',
            'purchase_orders.view',
            'purchase_orders.create',
            'purchase_orders.edit',
            'purchase_orders.approve',
            'purchase_orders.receive',
            'purchase_orders.view_reports',
            'sales_invoices.view',
            'sales_invoices.create',
            'sales_invoices.edit',
            'sales_invoices.manage_payments',
            'sales_invoices.print',
            'sales_invoices.view_reports',
            'batch_expirations.view',
            'batch_expirations.create',
            'batch_expirations.edit',
            'batch_expirations.manage_alerts',
            'batch_expirations.view_reports',
            'warehouses.view',
            'warehouses.edit',
            'warehouses.manage_stock',
            'warehouses.view_reports',
            'financial.view',
            'financial.view_reports',
            'financial.export',
            'reports.view',
            'reports.create',
            'reports.export',
            'reports.print',
            'settings.view',
            'settings.edit',
        ])->get();
        $managerRole->permissions()->sync($managerPermissions->pluck('id')->all());
        
        // Kasir Role - Can access POS and basic sales functions
        $kasirRole = Role::create([
            'name' => 'kasir',
            'display_name' => 'Kasir',
            'description' => null,
            'is_active' => true,
        ]);
        $kasirPermissions = Permission::whereIn('name', [
            'dashboard.view',
            'dashboard.sales',
            'products.view',
            'inventory.view',
            'pos.access',
            'pos.view_sales',
            'pos.create_sales',
            'pos.edit_sales',
            'pos.print_receipt',
            'pos.manage_invoices',
            'sales_invoices.view',
            'sales_invoices.create',
            'sales_invoices.edit',
            'sales_invoices.print',
            'reports.view',
        ])->get();
        $kasirRole->permissions()->sync($kasirPermissions->pluck('id')->all());
        
        // Gudang Role - Can manage inventory and warehouse
        $gudangRole = Role::create([
            'name' => 'gudang',
            'display_name' => 'Gudang',
            'description' => null,
            'is_active' => true,
        ]);
        $gudangPermissions = Permission::whereIn('name', [
            'dashboard.view',
            'dashboard.inventory',
            'products.view',
            'inventory.view',
            'inventory.create',
            'inventory.edit',
            'inventory.adjust',
            'suppliers.view',
            'incoming_goods_agenda.view',
            'incoming_goods_agenda.create',
            'incoming_goods_agenda.edit',
            'incoming_goods_agenda.receive',
            'incoming_goods_agenda.view_reports',
            'purchase_orders.view',
            'purchase_orders.create',
            'purchase_orders.edit',
            'purchase_orders.receive',
            'purchase_orders.view_reports',
            'batch_expirations.view',
            'batch_expirations.create',
            'batch_expirations.edit',
            'batch_expirations.manage_alerts',
            'batch_expirations.view_reports',
            'warehouses.view',
            'warehouses.edit',
            'warehouses.manage_stock',
            'warehouses.view_reports',
            'reports.view',
        ])->get();
        $gudangRole->permissions()->sync($gudangPermissions->pluck('id')->all());
        
        // Keuangan Role - Can manage financial reports
        $keuanganRole = Role::create([
            'name' => 'keuangan',
            'display_name' => 'Keuangan',
            'description' => null,
            'is_active' => true,
        ]);
        $keuanganPermissions = Permission::whereIn('name', [
            'dashboard.view',
            'dashboard.financial',
            'pos.view_sales',
            'pos.view_reports',
            'capital_tracking.view',
            'capital_tracking.create',
            'capital_tracking.edit',
            'capital_tracking.view_reports',
            'cash_ledger.view',
            'cash_ledger.create',
            'cash_ledger.edit',
            'cash_ledger.export',
            'cash_ledger.print',
            'cash_ledger.view_reports',
            'agenda.view',
            'agenda.view_reports',
            'agenda.export',
            'cashflow_agenda.view',
            'cashflow_agenda.view_reports',
            'incoming_goods_agenda.view',
            'incoming_goods_agenda.manage_payments',
            'incoming_goods_agenda.view_reports',
            'purchase_orders.view',
            'purchase_orders.view_reports',
            'sales_invoices.view',
            'sales_invoices.manage_payments',
            'sales_invoices.view_reports',
            'batch_expirations.view',
            'batch_expirations.view_reports',
            'financial.view',
            'financial.view_reports',
            'financial.export',
            'reports.view',
            'reports.create',
            'reports.export',
            'reports.print',
            'settings.view',
        ])->get();
        $keuanganRole->permissions()->sync($keuanganPermissions->pluck('id')->all());
        
        $this->command->info('Roles and permissions created successfully!');
        
        // Display role information
        $this->command->line('');
        $this->command->info('Created roles:');
        $this->command->line('- super_admin: All permissions');
        $this->command->line('- admin: Most permissions (except system settings)');
        $this->command->line('- manager: Business operations permissions');
        $this->command->line('- kasir: POS and sales permissions');
        $this->command->line('- gudang: Inventory and warehouse permissions');
        $this->command->line('- keuangan: Financial reports permissions');
    }
}
