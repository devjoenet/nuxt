<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Permission;
use App\Enums\RoleName;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission as PermissionModel;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = collect(Permission::cases())
            ->mapWithKeys(fn (Permission $permission): array => [
                $permission->value => PermissionModel::findOrCreate($permission->value, 'web'),
            ]);

        Role::findOrCreate(RoleName::SuperAdmin->value, 'web')->syncPermissions($permissions->values());
        Role::findOrCreate(RoleName::Admin->value, 'web')->syncPermissions($permissions->only([
            Permission::AdminDashboardView->value,
            Permission::AdminUsersManage->value,
            Permission::AdminRolesManage->value,
            Permission::AuditView->value,
            Permission::CrmView->value,
            Permission::CrmManage->value,
            Permission::JobsView->value,
            Permission::JobsManage->value,
            Permission::SettingsManage->value,
        ])->values());
        Role::findOrCreate(RoleName::SalesOps->value, 'web')->syncPermissions($permissions->only([
            Permission::AdminDashboardView->value,
            Permission::CrmView->value,
            Permission::CrmManage->value,
            Permission::JobsView->value,
        ])->values());
        Role::findOrCreate(RoleName::ClientViewer->value, 'web')->syncPermissions([
            $permissions[Permission::CrmView->value],
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
