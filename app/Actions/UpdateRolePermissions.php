<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\RolePermissionsData;
use App\Enums\RoleName;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UpdateRolePermissions
{
    public function handle(Role $role, RolePermissionsData $data): void
    {
        if ($role->name === RoleName::SuperAdmin->value) {
            throw ValidationException::withMessages(['permission_ids' => 'The Super Admin permissions are managed by code.']);
        }

        $permissions = Permission::query()
            ->where('guard_name', 'web')
            ->whereKey($data->permissionIds)
            ->get();

        $role->syncPermissions($permissions);

        activity()
            ->performedOn($role)
            ->withProperties(['permission_ids' => $data->permissionIds])
            ->log('Role permissions updated.');
    }
}
