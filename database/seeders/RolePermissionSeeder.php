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
        ])->values());

        Role::findOrCreate('Accountant', 'web');
        Role::findOrCreate('CA', 'web');
        Role::findOrCreate('Viewer', 'web');
    }
}
