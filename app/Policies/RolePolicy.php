<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\Models\Role;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('admin.roles.manage');
    }

    public function update(User $user, Role $role): bool
    {
        return $user->can('admin.roles.manage');
    }
}
