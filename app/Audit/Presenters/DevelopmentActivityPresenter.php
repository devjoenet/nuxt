<?php

declare(strict_types=1);

namespace App\Audit\Presenters;

use App\Models\DevelopmentTask;
use Illuminate\Database\Eloquent\Builder;
use SoutheastCode\Audit\Contracts\ActivityPresentation;
use SoutheastCode\Audit\Contracts\ActivityPresenter;
use SoutheastCode\Modules\WorkspaceUser;
use Spatie\Activitylog\Models\Activity;

final class DevelopmentActivityPresenter implements ActivityPresenter
{
    public function models(): array
    {
        return [DevelopmentTask::class];
    }

    public function constrain(Builder $query, WorkspaceUser $user): void
    {
        $tasks = DevelopmentTask::withTrashed();
        if (! collect(['development.requests.review', 'development.roadmap.manage', 'development.user_testing.manage'])->contains(fn (string $ability): bool => $user->workspaceCan($ability))) {
            $tasks->where(fn (Builder $tasks): Builder => $tasks
                ->where('submitter_id', $user->workspaceId())
                ->orWhere('assignee_id', $user->workspaceId())
                ->orWhereHas('testRuns', fn (Builder $tests): Builder => $tests->where('tester_id', $user->workspaceId())));
        }

        $query->where('subject_type', (new DevelopmentTask)->getMorphClass())->whereIn('subject_id', $tasks->select('id'));
    }

    public function eagerLoads(): array
    {
        return [];
    }

    public function present(Activity $activity, WorkspaceUser $user): ActivityPresentation
    {
        $record = $activity->subject;

        return $record instanceof DevelopmentTask
            ? new ActivityPresentation('Development task · '.$record->title, route('development.tasks.history', $record))
            : new ActivityPresentation('Development task #'.$activity->subject_id.' (unavailable)');
    }
}
