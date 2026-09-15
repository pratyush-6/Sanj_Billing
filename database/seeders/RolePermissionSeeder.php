<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $names = [
            'companies.manage',
            'financial-years.manage',
            'users.manage',
            'roles.manage',
            'settings.manage',
            'audit-logs.view',
            'expense-categories.manage',
            'vendors.manage',
            'bank-accounts.manage',
            'masters.manage',
            'expenses.manage',
            'expenses.view',
            'reports.view',
            'accounting.view',
            'daily-notes.view',
            'daily-notes.manage',
            'product-categories.manage',
            'products.manage',
            'inventory.view',
            'vendor-quotations.manage',
            'vendor-quotations.approve',
            'purchase-orders.manage',
            'goods-receipts.manage',
            'stock-adjustments.manage',
            'stock-adjustments.approve',
        ];

        $permissions = collect($names)->mapWithKeys(
            fn (string $name) => [$name => Permission::findOrCreate($name, 'web')]
        );

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $superAdmin = Role::findOrCreate('Super Admin', 'web');
        $superAdmin->syncPermissions($permissions->values());

        $admin = Role::findOrCreate('Admin', 'web');
        $admin->syncPermissions($permissions->only([
            'financial-years.manage',
            'users.manage',
            'settings.manage',
            'audit-logs.view',
            'expense-categories.manage',
            'vendors.manage',
            'bank-accounts.manage',
            'masters.manage',
            'expenses.manage',
            'expenses.view',
            'reports.view',
            'accounting.view',
            'daily-notes.view',
            'daily-notes.manage',
            'product-categories.manage',
            'products.manage',
            'inventory.view',
            'vendor-quotations.manage',
            'vendor-quotations.approve',
            'purchase-orders.manage',
            'goods-receipts.manage',
            'stock-adjustments.manage',
            'stock-adjustments.approve',
        ])->values());

        $accountant = Role::findOrCreate('Accountant', 'web');
        $accountant->syncPermissions($permissions->only([
            'vendors.manage',
            'expenses.manage',
            'expenses.view',
            'reports.view',
            'accounting.view',
            'daily-notes.view',
            'daily-notes.manage',
            'product-categories.manage',
            'products.manage',
            'inventory.view',
            'vendor-quotations.manage',
            'purchase-orders.manage',
            'goods-receipts.manage',
            'stock-adjustments.manage',
        ])->values());

        $ca = Role::findOrCreate('CA', 'web');
        $ca->syncPermissions($permissions->only(['expenses.view', 'reports.view', 'accounting.view', 'daily-notes.view', 'inventory.view'])->values());

        $viewer = Role::findOrCreate('Viewer', 'web');
        $viewer->syncPermissions($permissions->only(['expenses.view', 'reports.view', 'daily-notes.view', 'inventory.view'])->values());
    }
}
