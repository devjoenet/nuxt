<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\CreateRoleData;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CreateRole
{
    public function handle(CreateRoleData $data, User $actor): Role
    {
        return DB::transaction(function () use ($data, $actor): Role {
            if (Role::query()->where('guard_name', 'web')->where('name', $data->name)->exists()) {
                throw ValidationException::withMessages(['name' => 'A role with this name already exists.']);
            }

            $permissions = Permission::query()
                ->where('guard_name', 'web')
                ->whereKey($data->permissionIds)
                ->get();

            if ($permissions->count() !== count($data->permissionIds)) {
                throw ValidationException::withMessages(['permission_ids' => 'One or more selected permissions are invalid.']);
            }

            $role = Role::query()->create([
                'name' => $data->name,
                'guard_name' => 'web',
            ]);
            $role->syncPermissions($permissions);

            activity()
                ->causedBy($actor)
                ->performedOn($role)
                ->withProperties(['permission_ids' => $data->permissionIds])
                ->log('Role created.');

            return $role->load('permissions');
        });
    }
}
