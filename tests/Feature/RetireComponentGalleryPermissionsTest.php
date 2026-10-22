<?php

declare(strict_types=1);

use App\Enums\Permission as PermissionName;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

test('fresh permission seeding omits retired gallery defaults and preserves unrelated grants', function (): void {

    $workspacePermission = Permission::findOrCreate(PermissionName::AdminDashboardView->value, 'web');
    $otherGuardPermission = Permission::findOrCreate('component.gallery.view', 'api');
    $role = Role::findOrCreate('Gallery reviewer', 'web');
    $role->givePermissionTo($workspacePermission);
    app(Database\Seeders\RolePermissionSeeder::class)->run();
    app(Database\Seeders\RolePermissionSeeder::class)->run();

    $this->assertDatabaseMissing('permissions', ['name' => 'component.gallery.view', 'guard_name' => 'web']);
    $this->assertModelExists($workspacePermission);
    $this->assertModelExists($otherGuardPermission);
    $this->assertDatabaseHas('role_has_permissions', [
        'permission_id' => $workspacePermission->getKey(),
        'role_id' => $role->getKey(),
    ]);
});
