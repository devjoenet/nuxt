<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\UpdateRolePermissions;
use App\Data\RolePermissionsData;
use App\Enums\RoleName;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateRoleRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Role::class);

        return Inertia::render('Admin/Roles/Index', [
            'roles' => Role::query()->with('permissions')->orderBy('name')->get()->map(fn (Role $role): array => [
                'id' => $role->getKey(),
                'name' => $role->name,
                'protected' => $role->name === RoleName::SuperAdmin->value,
                'permission_ids' => $role->permissions->pluck('id')->values(),
            ]),
            'permissions' => Permission::query()->orderBy('name')->get(['id', 'name'])->map(fn (Permission $permission): array => [
                'id' => $permission->id,
                'name' => $permission->name,
                'label' => str($permission->name)->replace('.', ' ')->title()->toString(),
            ]),
        ]);
    }

    public function update(UpdateRoleRequest $request, Role $role, UpdateRolePermissions $updateRolePermissions): RedirectResponse
    {
        $this->authorize('update', $role);

        $updateRolePermissions->handle($role, RolePermissionsData::fromValidated($request->validated()));

        return back()->with('success', 'Role permissions updated.');
    }
}
