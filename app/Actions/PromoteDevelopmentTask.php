<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\DevelopmentTaskTransitionData;
use App\Enums\DevelopmentTaskStatus;
use App\Models\DevelopmentTask;
use App\Models\User;
use App\Notifications\DevelopmentTaskPromoted;
use Illuminate\Support\Facades\DB;

class PromoteDevelopmentTask
{
    public function __construct(
        private TransitionDevelopmentTask $transitionDevelopmentTask,
        private SetDevelopmentTaskRoadmapPosition $setDevelopmentTaskRoadmapPosition,
    ) {}

    public function handle(DevelopmentTask $task, User $actor): DevelopmentTask
    {
        return DB::transaction(function () use ($task, $actor): DevelopmentTask {
            $task = $this->setDevelopmentTaskRoadmapPosition->handle($task, null, $actor);
            $task = $this->transitionDevelopmentTask->handle(
                $task,
                new DevelopmentTaskTransitionData(DevelopmentTaskStatus::Planned),
                $actor,
            );
            $task = $this->setDevelopmentTaskRoadmapPosition->handle($task, PHP_INT_MAX, $actor);

            $submitter = $task->submitter;

            if ($submitter instanceof User && ! $submitter->is($actor)) {
                $submitter->notify(new DevelopmentTaskPromoted(
                    taskUuid: $task->uuid,
                    taskTitle: $task->title,
                ));
            }

            return $task;
        }, attempts: 5);
    }
}
