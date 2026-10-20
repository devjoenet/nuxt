<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Lead;
use App\Models\User;
use App\Support\CrmAccess;

class LeadPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('crm.view');
    }

    public function view(User $user, Lead $lead): bool
    {
        return $user->can('crm.view') && $lead->isVisibleTo($user);
    }

    public function create(User $user): bool
    {
        return $user->can('crm.manage');
    }

    public function update(User $user, Lead $lead): bool
    {
        return $user->can('crm.manage') && $lead->isVisibleTo($user);
    }

    public function archive(User $user, Lead $lead): bool
    {
        return $user->can('crm.manage') && $lead->isVisibleTo($user);
    }

    public function convert(User $user, Lead $lead): bool
    {
        return $user->can('crm.manage') && $lead->isVisibleTo($user);
    }

    public function assign(User $user, Lead $lead): bool
    {
        return $user->can('crm.manage') && CrmAccess::administrator($user) && $lead->isVisibleTo($user);
    }
}
