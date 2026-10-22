<?php

declare(strict_types=1);

use SoutheastCode\UserManagement\Queries\RolePermissionCatalogQuery;
use Spatie\Permission\Models\Permission;

test('the permission catalog omits absent groups and retains ordered web permissions', function (): void {
    Permission::query()->delete();
    $manage = Permission::findOrCreate('crm.manage', 'web');
    $view = Permission::findOrCreate('crm.view', 'web');
    $custom = Permission::findOrCreate('reports.exports.create', 'web');
    Permission::findOrCreate('admin.users.manage', 'api');

    $groups = app(RolePermissionCatalogQuery::class)->grouped();

    expect($groups)->toBe([
        [
            'key' => 'crm',
            'label' => 'CRM',
            'permissions' => [
                ['id' => $view->id, 'name' => 'crm.view', 'label' => 'View'],
                ['id' => $manage->id, 'name' => 'crm.manage', 'label' => 'Manage'],
            ],
        ],
        [
            'key' => 'other',
            'label' => 'Other permissions',
            'permissions' => [
                ['id' => $custom->id, 'name' => 'reports.exports.create', 'label' => 'Reports Exports Create'],
            ],
        ],
    ]);
});

test('the permission catalog is empty when only non-web permissions exist', function (): void {
    Permission::query()->delete();
    Permission::findOrCreate('crm.view', 'api');

    expect(app(RolePermissionCatalogQuery::class)->grouped())->toBeEmpty();
});
