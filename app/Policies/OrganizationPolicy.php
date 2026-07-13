<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Organization;
use App\Models\User;

class OrganizationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('crm.view');
    }

    public function view(User $user, Organization $organization): bool
    {
        return $user->can('crm.view');
    }

    public function create(User $user): bool
    {
        return $user->can('crm.manage');
    }

    public function update(User $user, Organization $organization): bool
    {
        return $user->can('crm.manage');
    }

    public function delete(User $user, Organization $organization): bool
    {
        return $user->can('crm.manage');
    }
}
