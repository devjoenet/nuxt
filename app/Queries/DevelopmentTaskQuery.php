<?php

declare(strict_types=1);

namespace App\Queries;

use App\Enums\DevelopmentTaskStatus;
use App\Models\DevelopmentTask;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final class DevelopmentTaskQuery
{
    /** @return Builder<DevelopmentTask> */
    public function forSubmitter(User $user): Builder
    {
        return DevelopmentTask::query()
            ->whereBelongsTo($user, 'submitter')
            ->with(['submitter', 'reviewer'])
            ->latest('submitted_at');
    }

    /** @return Builder<DevelopmentTask> */
    public function reviewQueue(): Builder
    {
        return DevelopmentTask::query()
            ->whereIn('status', [
                DevelopmentTaskStatus::Submitted,
                DevelopmentTaskStatus::NeedsInformation,
                DevelopmentTaskStatus::UnderReview,
            ])
            ->with(['submitter', 'reviewer'])
            ->oldest('submitted_at')
            ->orderBy('id');
    }

    /** @return Builder<DevelopmentTask> */
    public function roadmap(): Builder
    {
        return DevelopmentTask::query()
            ->whereIn('status', [
                DevelopmentTaskStatus::Planned,
                DevelopmentTaskStatus::InProgress,
                DevelopmentTaskStatus::Blocked,
            ])
            ->with(['submitter', 'reviewer', 'assignee'])
            ->orderByRaw('roadmap_position is null')
            ->orderBy('roadmap_position')
            ->oldest('planned_at')
            ->orderBy('id');
    }
}
