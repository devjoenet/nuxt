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
                DevelopmentTaskStatus::ChangesRequested,
            ])
            ->with(['submitter', 'reviewer', 'assignee'])
            ->orderByRaw(
                'case
                    when status = ? then 1
                    when status in (?, ?) then 2
                    when status = ? then 3
                    else 4
                end',
                [
                    DevelopmentTaskStatus::Planned->value,
                    DevelopmentTaskStatus::InProgress->value,
                    DevelopmentTaskStatus::ChangesRequested->value,
                    DevelopmentTaskStatus::Blocked->value,
                ],
            )
            ->orderByRaw(
                'case when status = ? then roadmap_position end',
                [DevelopmentTaskStatus::Planned->value],
            )
            ->orderByRaw(
                'case
                    when status = ? then started_at
                    when status = ? then changes_requested_at
                    when status = ? then blocked_at
                end',
                [
                    DevelopmentTaskStatus::InProgress->value,
                    DevelopmentTaskStatus::ChangesRequested->value,
                    DevelopmentTaskStatus::Blocked->value,
                ],
            )
            ->orderBy('id');
    }
}
