<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permission;
use App\Models\DevelopmentTask;
use App\Models\DevelopmentTestRun;
use App\Models\User;

class DevelopmentTestRunPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->can(Permission::DevelopmentUserTestingManage->value)) {
            return true;
        }

        return DevelopmentTestRun::query()
            ->where('tester_id', $user->getKey())
            ->exists();
    }

    public function view(User $user, DevelopmentTestRun $testRun): bool
    {
        if ($testRun->tester_id === $user->getKey()) {
            return true;
        }

        return $user->can('view', $testRun->task);
    }

    public function create(User $user, DevelopmentTask $task): bool
    {
        return $user->canAny([
            Permission::DevelopmentRoadmapManage->value,
            Permission::DevelopmentUserTestingManage->value,
        ]);
    }

    public function update(User $user, DevelopmentTestRun $testRun): bool
    {
        if ($testRun->tester_id === $user->getKey()) {
            return true;
        }

        return $user->can(Permission::DevelopmentUserTestingManage->value);
    }
}
