<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\DevelopmentTaskStatus;
use App\Enums\Permission;
use App\Models\DevelopmentTask;
use App\Models\User;

class DevelopmentTaskPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canAny([
            Permission::DevelopmentRequestsCreate->value,
            Permission::DevelopmentRequestsReview->value,
            Permission::DevelopmentRoadmapManage->value,
            Permission::DevelopmentUserTestingManage->value,
        ]);
    }

    public function view(User $user, DevelopmentTask $task): bool
    {
        if ($user->canAny([
            Permission::DevelopmentRequestsReview->value,
            Permission::DevelopmentRoadmapManage->value,
            Permission::DevelopmentUserTestingManage->value,
        ])) {
            return true;
        }

        if ($task->submitter_id === $user->getKey() || $task->assignee_id === $user->getKey()) {
            return true;
        }

        return $task->testRuns()
            ->where('tester_id', $user->getKey())
            ->exists();
    }

    public function create(User $user): bool
    {
        return $user->can(Permission::DevelopmentRequestsCreate->value);
    }

    public function review(User $user, DevelopmentTask $task): bool
    {
        return $user->can(Permission::DevelopmentRequestsReview->value);
    }

    public function manageRoadmap(User $user, DevelopmentTask $task): bool
    {
        return $user->can(Permission::DevelopmentRoadmapManage->value);
    }

    public function updateProgress(User $user, DevelopmentTask $task): bool
    {
        if (! in_array($task->status, [
            DevelopmentTaskStatus::Planned,
            DevelopmentTaskStatus::InProgress,
            DevelopmentTaskStatus::Blocked,
        ], true)) {
            return false;
        }

        if ($task->assignee_id === $user->getKey()) {
            return true;
        }

        return $user->can(Permission::DevelopmentRoadmapManage->value);
    }

    public function manageTesting(User $user, DevelopmentTask $task): bool
    {
        return $user->can(Permission::DevelopmentUserTestingManage->value);
    }

    public function cancel(User $user, DevelopmentTask $task): bool
    {
        if ($user->canAny([
            Permission::DevelopmentRequestsReview->value,
            Permission::DevelopmentRoadmapManage->value,
        ])) {
            return true;
        }

        return $task->submitter_id === $user->getKey()
            && in_array($task->status, [
                DevelopmentTaskStatus::Submitted,
                DevelopmentTaskStatus::NeedsInformation,
            ], true);
    }

    public function archive(User $user, DevelopmentTask $task): bool
    {
        return $task->status->isTerminal()
            && $user->canAny([
                Permission::DevelopmentRequestsReview->value,
                Permission::DevelopmentRoadmapManage->value,
            ]);
    }
}
