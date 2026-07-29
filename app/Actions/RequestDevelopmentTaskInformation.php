<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\DevelopmentTaskNoteData;
use App\Data\DevelopmentTaskTransitionData;
use App\Enums\DevelopmentNoteAudience;
use App\Enums\DevelopmentTaskStatus;
use App\Models\DevelopmentTask;
use App\Models\User;
use App\Notifications\DevelopmentTaskInformationRequested;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Facades\Activity;

class RequestDevelopmentTaskInformation
{
    public function __construct(private TransitionDevelopmentTask $transitionDevelopmentTask) {}

    public function handle(
        DevelopmentTask $task,
        DevelopmentTaskNoteData $data,
        User $actor,
    ): DevelopmentTask {
        $task = DB::transaction(function () use ($task, $data, $actor): DevelopmentTask {
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

            $task = $this->transitionDevelopmentTask->handle(
                $task,
                new DevelopmentTaskTransitionData(DevelopmentTaskStatus::NeedsInformation),
                $actor,
            );

            $task->notes()->create([
                'author_id' => $actor->getKey(),
                'audience' => DevelopmentNoteAudience::Participants,
                'body' => $data->body,
            ]);

            return $task;
        });

        $submitter = $task->submitter;

        if ($submitter instanceof User && ! $submitter->is($actor)) {
            $submitter->notify(new DevelopmentTaskInformationRequested(
                taskUuid: $task->uuid,
                taskTitle: $task->title,
                reviewerName: $actor->name,
                body: $data->body,
            ));
        }

        return $task;
    }
}
