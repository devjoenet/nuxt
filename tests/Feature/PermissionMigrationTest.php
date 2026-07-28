<?php

declare(strict_types=1);

use App\Enums\RoleName;
use Database\Seeders\RolePermissionSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

test('client viewer CRM access migration is idempotent and preserves other grants', function (): void {
    app(RolePermissionSeeder::class)->run();
    $role = Role::findByName(RoleName::ClientViewer->value, 'web');
    $crm = Permission::findOrCreate('crm.view', 'web');
    $jobs = Permission::findOrCreate('jobs.view', 'web');
    $role->syncPermissions([$crm, $jobs]);

    $migration = require base_path('database/migrations/2026_07_28_224147_revoke_crm_view_from_client_viewer_role.php');
    $migration->up();
    $migration->up();

    app(PermissionRegistrar::class)->forgetCachedPermissions();
    $role->refresh();

    expect($role->hasPermissionTo('crm.view'))->toBeFalse()
        ->and($role->hasPermissionTo('jobs.view'))->toBeTrue();
});

test('user testing permission migration renames in place and keeps role assignments', function (): void {
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
