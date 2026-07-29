<?php

declare(strict_types=1);

namespace App\Queries;

use App\Enums\DevelopmentTestRunStatus;
use App\Enums\Permission;
use App\Models\DevelopmentTestRun;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final class DevelopmentTestRunQuery
{
    /** @return Builder<DevelopmentTestRun> */
    public function pendingFor(User $user): Builder
    {
        $query = DevelopmentTestRun::query()
            ->where('status', DevelopmentTestRunStatus::Pending)
            ->with([
                'task.submitter',
                'task.reviewer',
                'task.assignee',
                'tester',
                'requestedBy',
            ])
            ->oldest('requested_at')
            ->orderBy('id');

        if ($user->can(Permission::DevelopmentUserTestingManage->value)) {
            return $query;
        }

        return $query->whereBelongsTo($user, 'tester');
    }
}
