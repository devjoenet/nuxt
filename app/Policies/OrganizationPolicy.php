<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Organization;
use App\Models\User;
use App\Support\CrmAccess;

class OrganizationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('crm.view');
    }

    public function view(User $user, Organization $organization): bool
    {
        return $user->can('crm.view') && $organization->isVisibleTo($user);
    }

    public function create(User $user): bool
    {
        return $user->can('crm.manage');
    }

    public function update(User $user, Organization $organization): bool
    {
        return $user->can('crm.manage') && $organization->isVisibleTo($user);
    }

    public function delete(User $user, Organization $organization): bool
    {
        return $user->can('crm.manage') && $organization->isVisibleTo($user);
    }

    public function archive(User $user, Organization $organization): bool
    {
        return $user->can('crm.manage') && $organization->isVisibleTo($user);
    }

    public function restore(User $user, Organization $organization): bool
    {
        return $user->can('crm.manage') && $organization->isVisibleTo($user);
    }

    public function assign(User $user, Organization $organization): bool
    {
        return $user->can('crm.manage') && CrmAccess::administrator($user) && $organization->isVisibleTo($user);
    }
}
