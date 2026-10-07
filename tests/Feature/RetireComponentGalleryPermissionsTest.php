<?php

declare(strict_types=1);

use App\Enums\Permission as PermissionName;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

test('retiring the gallery removes its access grants while preserving other permissions', function (): void {
    $galleryPermission = Permission::findOrCreate('component.gallery.view', 'web');
    $workspacePermission = Permission::findOrCreate(PermissionName::AdminDashboardView->value, 'web');
    $otherGuardPermission = Permission::findOrCreate('component.gallery.view', 'api');
    $role = Role::findOrCreate('Gallery reviewer', 'web');
    $role->givePermissionTo([$galleryPermission, $workspacePermission]);
    $migration = require database_path('migrations/2026_10_07_171940_retire_component_gallery_permissions.php');

    $migration->up();
    $migration->up();

    $this->assertModelMissing($galleryPermission);
    $this->assertModelExists($workspacePermission);
    $this->assertModelExists($otherGuardPermission);
    $this->assertDatabaseMissing('role_has_permissions', ['permission_id' => $galleryPermission->getKey()]);
    $this->assertDatabaseHas('role_has_permissions', [
        'permission_id' => $workspacePermission->getKey(),
        'role_id' => $role->getKey(),
    ]);
});
