<?php

declare(strict_types=1);

use App\Enums\RoleName;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /** @var array<int, string> */
    private const NEW_PERMISSIONS = [
        'development.requests.create',
        'development.requests.review',
        'development.roadmap.manage',
    ];

    /** @var array<int, string> */
    private const DEVELOPMENT_PERMISSIONS = [
        ...self::NEW_PERMISSIONS,
        'development.user_testing.manage',
    ];

    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = collect(self::DEVELOPMENT_PERMISSIONS)
            ->map(fn (string $permission): Permission => Permission::findOrCreate($permission, 'web'));

        Role::query()
            ->where('guard_name', 'web')
            ->whereIn('name', [RoleName::Admin->value, RoleName::SuperAdmin->value])
            ->get()
            ->each(fn (Role $role): Role => $role->givePermissionTo($permissions));

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Permission::query()
            ->where('guard_name', 'web')
            ->whereIn('name', self::NEW_PERMISSIONS)
            ->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
