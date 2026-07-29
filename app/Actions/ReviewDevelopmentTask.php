<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\DevelopmentTaskTransitionData;
use App\Enums\DevelopmentTaskStatus;
use App\Models\DevelopmentTask;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Facades\Activity;

class ReviewDevelopmentTask
{
    public function __construct(private TransitionDevelopmentTask $transitionDevelopmentTask) {}

    public function handle(DevelopmentTask $task, User $actor): DevelopmentTask
    {
        return DB::transaction(function () use ($task, $actor): DevelopmentTask {
            $task = DevelopmentTask::query()
                ->whereKey($task->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($task->reviewer_id === null) {
                Activity::defaultCauser(
                    $actor,
                    fn (): bool => $task->update(['reviewer_id' => $actor->getKey()]),
                );
            }

            return $this->transitionDevelopmentTask->handle(
                $task,
                new DevelopmentTaskTransitionData(DevelopmentTaskStatus::UnderReview),
                $actor,
            );
        });
    }
}
