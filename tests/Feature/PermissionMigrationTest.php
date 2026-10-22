<?php

declare(strict_types=1);

use App\Enums\RoleName;
use Database\Seeders\RolePermissionSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

test('fresh permission seeding excludes CRM from Client Viewer and preserves explicit unrelated grants', function (): void {
    app(RolePermissionSeeder::class)->run();
    $role = Role::findByName(RoleName::ClientViewer->value, 'web');
    $role->givePermissionTo(Permission::findOrCreate('jobs.view', 'web'));

    app(RolePermissionSeeder::class)->run();
    app(RolePermissionSeeder::class)->run();
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    expect($role->refresh()->hasPermissionTo('crm.view'))->toBeFalse()
        ->and($role->hasPermissionTo('jobs.view'))->toBeTrue();
});

test('user testing permission migration renames in place and keeps role assignments', function (): void {
    Permission::query()
        ->where('name', 'development.user_testing.manage')
        ->where('guard_name', 'web')
        ->delete();

    $role = Role::findOrCreate(RoleName::Admin->value, 'web');
    $permission = Permission::create([
        'name' => 'development.user_testing.view',
        'guard_name' => 'web',
    ]);
    $role->givePermissionTo($permission);

    $migration = require base_path('database/migrations/2026_07_28_224147_rename_development_user_testing_permission.php');
    $migration->up();
    $migration->up();

    app(PermissionRegistrar::class)->forgetCachedPermissions();
    $renamed = Permission::query()->where('name', 'development.user_testing.manage')->sole();
    $role->refresh();

    expect($renamed->getKey())->toBe($permission->getKey())
        ->and($role->hasPermissionTo('development.user_testing.manage'))->toBeTrue()
        ->and(Permission::query()->where('name', 'development.user_testing.view')->exists())->toBeFalse();

    $migration->down();
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    expect(Permission::query()->whereKey($permission->getKey())->value('name'))
        ->toBe('development.user_testing.view');
});

test('development workspace permission migration grants only admin roles and rolls back new permissions', function (): void {
    app(RolePermissionSeeder::class)->run();
    $admin = Role::findByName(RoleName::Admin->value, 'web');
    $superAdmin = Role::findByName(RoleName::SuperAdmin->value, 'web');
    $salesOps = Role::findByName(RoleName::SalesOps->value, 'web');

    Permission::query()
        ->whereIn('name', [
            'development.requests.create',
            'development.requests.review',
            'development.roadmap.manage',
        ])
        ->delete();

    $migration = require base_path('database/migrations/2026_07_29_172032_add_development_workspace_permissions.php');
    $migration->up();
    $migration->up();

    app(PermissionRegistrar::class)->forgetCachedPermissions();
    $developmentPermissions = [
        'development.requests.create',
        'development.requests.review',
        'development.roadmap.manage',
        'development.user_testing.manage',
    ];

    expect($admin->refresh()->hasAllPermissions($developmentPermissions))->toBeTrue()
        ->and($superAdmin->refresh()->hasAllPermissions($developmentPermissions))->toBeTrue()
        ->and($salesOps->refresh()->hasAnyPermission($developmentPermissions))->toBeFalse();

    $migration->down();
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    expect(Permission::query()->whereIn('name', array_slice($developmentPermissions, 0, 3))->exists())->toBeFalse()
        ->and(Permission::query()->where('name', 'development.user_testing.manage')->exists())->toBeTrue();
});
