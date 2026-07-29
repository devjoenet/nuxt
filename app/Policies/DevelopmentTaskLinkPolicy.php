<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permission;
use App\Models\DevelopmentTask;
use App\Models\DevelopmentTaskLink;
use App\Models\User;

class DevelopmentTaskLinkPolicy
{
    public function view(User $user, DevelopmentTaskLink $link): bool
    {
        return $user->can('view', $link->task);
    }

    public function create(User $user, DevelopmentTask $task): bool
    {
        if ($task->assignee_id === $user->getKey()) {
            return true;
        }

        return $user->can(Permission::DevelopmentRoadmapManage->value);
    }
}
