<?php

declare(strict_types=1);

use App\Enums\Permission as PermissionName;
use App\Enums\RoleName;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permission = Permission::findOrCreate(PermissionName::ArgonGalleryView->value, 'web');

        Role::query()
            ->where('guard_name', 'web')
            ->whereIn('name', [RoleName::Admin->value, RoleName::SuperAdmin->value])
            ->get()
            ->each(fn (Role $role): Role => $role->givePermissionTo($permission));

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Permission::query()
            ->where('guard_name', 'web')
            ->where('name', PermissionName::ArgonGalleryView->value)
            ->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
