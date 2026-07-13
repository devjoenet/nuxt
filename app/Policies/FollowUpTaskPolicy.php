<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\FollowUpTask;
use App\Models\User;

class FollowUpTaskPolicy
{
    public function view(User $user, FollowUpTask $task): bool
    {
        return $user->can('crm.view');
    }

    public function create(User $user): bool
    {
        return $user->can('crm.manage');
    }

    public function update(User $user, FollowUpTask $task): bool
    {
        return $user->can('crm.manage');
    }

    public function delete(User $user, FollowUpTask $task): bool
    {
        return $user->can('crm.manage');
    }
}
