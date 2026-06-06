<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Outlet;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Create Default Outlet if none exists
        $outlet = Outlet::first();
        if (!$outlet) {
            $outlet = Outlet::create([
                'name' => 'Wisata Provit Farm Village',
                'address' => 'Kawasan Wisata PFV',
                'phone' => '08123456789',
                'is_active' => true,
                'tax_percentage' => 11.00,
                'discount_percentage' => 0.00,
            ]);
        }

        // 3. Define and Create Permissions
        $permissions = [
            // User management
            'view_users', 'create_users', 'edit_users', 'delete_users',
            // Products
            'view_products', 'create_products', 'edit_products', 'delete_products',
            // Categories
            'view_categories', 'create_categories', 'edit_categories', 'delete_categories',
            // Outlets
            'view_outlets', 'create_outlets', 'edit_outlets', 'delete_outlets',
            // Transactions
            'view_transactions', 'create_transactions', 'edit_transactions', 'delete_transactions',
            // Shifts
            'view_shifts', 'create_shifts', 'edit_shifts', 'delete_shifts',
            // Printers
            'view_printers', 'create_printers', 'edit_printers', 'delete_printers',
            // Stock adjustments
            'view_stock_adjustments', 'create_stock_adjustments', 'edit_stock_adjustments', 'delete_stock_adjustments',
            // Stock movements
            'view_stock_movements', 'create_stock_movements',
            // Stock transfers
            'view_stock_transfers', 'create_stock_transfers',
            // Audit logs & Reports
            'view_audit_logs', 'view_reports',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
        }

        // 4. Create Roles and Assign Permissions
        // Super Admin Role
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdminRole->syncPermissions(Permission::all());

        // Kasir Role
        $kasirRole = Role::firstOrCreate(['name' => 'kasir', 'guard_name' => 'web']);
        $kasirRole->syncPermissions([
            'view_products',
            'view_transactions',
            'create_transactions',
            'view_shifts',
            'create_shifts',
        ]);

        // Admin Outlet Role
        $adminOutletRole = Role::firstOrCreate(['name' => 'admin_outlet', 'guard_name' => 'web']);
        $adminOutletRole->syncPermissions([
            'view_products', 'create_products', 'edit_products', 'delete_products',
            'view_categories', 'create_categories', 'edit_categories', 'delete_categories',
            'view_transactions',
            'view_shifts',
            'view_stock_adjustments', 'create_stock_adjustments',
            'view_stock_movements',
            'view_stock_transfers', 'create_stock_transfers',
            'view_reports',
        ]);

        // 5. Seed default admin account
        $adminUser = User::where('email', 'admin@admin.com')->first();
        if (!$adminUser) {
            $adminUser = User::create([
                'name' => 'Super Admin',
                'email' => 'admin@admin.com',
                'password' => Hash::make('password'),
                'outlet_id' => $outlet->id,
                'pin' => '123456',
                'email_verified_at' => now(),
            ]);
        }
        $adminUser->syncRoles([$superAdminRole]);

        // 6. Seed default cashier account
        $cashierUser = User::where('email', 'kasir@admin.com')->first();
        if (!$cashierUser) {
            $cashierUser = User::create([
                'name' => 'Kasir Wisata PFV',
                'email' => 'kasir@admin.com',
                'password' => Hash::make('password'),
                'outlet_id' => $outlet->id,
                'pin' => '111111',
                'email_verified_at' => now(),
            ]);
        }
        $cashierUser->syncRoles([$kasirRole]);

        // 7. Seed default outlet admin account
        $outletAdminUser = User::where('email', 'outlet@admin.com')->first();
        if (!$outletAdminUser) {
            $outletAdminUser = User::create([
                'name' => 'Admin Outlet PFV',
                'email' => 'outlet@admin.com',
                'password' => Hash::make('password'),
                'outlet_id' => $outlet->id,
                'pin' => '222222',
                'email_verified_at' => now(),
            ]);
        }
        $outletAdminUser->syncRoles([$adminOutletRole]);
    }
}
