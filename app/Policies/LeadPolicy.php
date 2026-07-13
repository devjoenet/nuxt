<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Lead;
use App\Models\User;

class LeadPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('crm.view');
    }

    public function view(User $user, Lead $lead): bool
    {
        return $user->can('crm.view');
    }

    public function create(User $user): bool
    {
        return $user->can('crm.manage');
    }

    public function update(User $user, Lead $lead): bool
    {
        return $user->can('crm.manage');
    }

    public function archive(User $user, Lead $lead): bool
    {
        return $user->can('crm.manage');
    }

    public function convert(User $user, Lead $lead): bool
    {
        return $user->can('crm.manage');
    }
}
